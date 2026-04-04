<?php
namespace App\Controllers;

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Models\Database;
use App\Middleware\Session;

class TransactionController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function handleRequest() {
        header("Content-Type: application/json");
        if (!Session::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(["error" => "Unauthenticated"]);
            exit;
        }

        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            $this->index();
        } elseif ($method === 'POST') {
            $this->create();
        } elseif ($method === 'PUT' || $method === 'PATCH') {
            $this->update();
        } elseif ($method === 'DELETE') {
            $this->delete();
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
        }
    }

    private function index() {
        $userId = Session::get('user_id');
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 20;
        $offset = ($page - 1) * $limit;

        $query = "SELECT * FROM expenses WHERE (user_id = :userId1 OR id IN (SELECT expense_id FROM expense_splits WHERE user_id = :userId2))";
        
        // Add filters (simplified)
        if (!empty($_GET['group_id'])) {
            $query .= " AND group_id = :groupId";
        }
        $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':userId1', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':userId2', $userId, \PDO::PARAM_INT);
        if (!empty($_GET['group_id'])) {
            $stmt->bindValue(':groupId', intval($_GET['group_id']), \PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $transactions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        echo json_encode(['data' => $transactions, 'page' => $page, 'limit' => $limit]);
    }

    private function create() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // In existing table, paid_by maps to user_id 
        $paidBy = Session::get('user_id'); 
        if (!empty($input['paid_by'])) {
            $paidBy = intval($input['paid_by']);
        }

        $totalAmount = floatval($input['total_amount'] ?? 0);
        $splitType = $input['split_type'] ?? 'equal';
        $splits = $input['splits'] ?? [];
        $date = $input['date'] ?? date('Y-m-d');
        $note = $input['note'] ?? '';
        $categoryId = !empty($input['category']) ? intval($input['category']) : null;

        try {
            $computedSplits = $this->calculateSplits($totalAmount, $splitType, $splits, $paidBy);

            $this->db->beginTransaction();

            $stmt = $this->db->prepare("INSERT INTO expenses (group_id, user_id, amount, expense_date, description, currency, ocr_status, category_id) VALUES (?, ?, ?, ?, ?, 'INR', 'none', ?)");
            $stmt->execute([
                $input['group_id'] ?? null,
                $paidBy,
                $totalAmount,
                $date,
                $note,
                $categoryId
            ]);
            $expenseId = $this->db->lastInsertId();

            if (!empty($computedSplits)) {
                $splitStmt = $this->db->prepare("INSERT INTO expense_splits (expense_id, user_id, share_amount) VALUES (?, ?, ?)");
                foreach ($computedSplits as $split) {
                    $splitStmt->execute([$expenseId, $split['user_id'], $split['share_amount']]);
                    if ($split['user_id'] != $paidBy) {
                        $this->updateNormalizedBalance($split['user_id'], $paidBy, $split['share_amount']);
                    }
                }
            }

            $this->db->commit();
            http_response_code(201);
            echo json_encode([
                "status" => "success", 
                "expense_id" => $expenseId, 
                "split_type" => $splitType,
                "splits" => $computedSplits
            ]);

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Transaction DB Error: " . $e->getMessage());
            http_response_code(400);
            echo json_encode(["error" => "Transaction failed", "message" => $e->getMessage()]);
        }
    }

    private function update() {
        http_response_code(501);
        echo json_encode(["error" => "Not Implemented yet"]);
    }

    private function delete() {
        http_response_code(501);
        echo json_encode(["error" => "Not Implemented yet"]);
    }

    private function calculateSplits($totalAmount, $splitType, $splits, $paidBy) {
        $computed = [];
        $totalMembers = count($splits);
        if ($totalMembers === 0) return [];

        if ($splitType === 'equal') {
            $baseShare = floor(($totalAmount / $totalMembers) * 100) / 100;
            $sum = 0;

            foreach ($splits as $index => $split) {
                $share = $baseShare;
                if ($index === $totalMembers - 1) {
                    $share = $totalAmount - $sum;
                }
                $sum += $share;
                $computed[] = ['user_id' => $split['user_id'], 'share_amount' => round($share, 2)];
            }
            return $computed;
        }

        if ($splitType === 'custom') {
            $sum = 0;
            foreach ($splits as $split) {
                $sum += floatval($split['share_amount']);
                $computed[] = ['user_id' => $split['user_id'], 'share_amount' => round($split['share_amount'], 2)];
            }
            if (abs($sum - $totalAmount) > 0.50) {
                throw new \Exception("Splits sum (".round($sum, 2).") does not match total amount ($totalAmount).");
            }
            return $computed;
        }

        return $splits;
    }

    private function updateNormalizedBalance($debtor, $creditor, $amount) {
        $userFrom = min($debtor, $creditor);
        $userTo = max($debtor, $creditor);
        
        $computedAmount = ($debtor == $userFrom) ? $amount : -$amount;

        $stmt = $this->db->prepare("SELECT id, amount FROM balances WHERE user_from = ? AND user_to = ?");
        $stmt->execute([$userFrom, $userTo]);
        $row = $stmt->fetch();

        if ($row) {
            $newAmount = $row['amount'] + $computedAmount;
            $update = $this->db->prepare("UPDATE balances SET amount = ?, last_updated = NOW() WHERE id = ?");
            $update->execute([$newAmount, $row['id']]);
        } else {
            $insert = $this->db->prepare("INSERT INTO balances (user_from, user_to, amount) VALUES (?, ?, ?)");
            $insert->execute([$userFrom, $userTo, $computedAmount]);
        }
    }
}
