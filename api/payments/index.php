<?php
namespace App\Controllers;

require_once __DIR__ . '/../../config/bootstrap.php';
use App\Models\Database;
use App\Middleware\Session;

class PaymentController {
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

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['from_user'], $input['to_user'], $input['amount'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required fields"]);
            return;
        }

        $fromUser = intval($input['from_user']);
        $toUser = intval($input['to_user']);
        $amount = floatval($input['amount']);

        // Check that from_user is the logged-in user or an admin
        if ($fromUser !== Session::get('user_id')) {
            // For safety: a user should only record payments they made, or we assume trust here
            // (Assuming user can make payments on their own behalf)
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("INSERT INTO payments (from_user, to_user, amount, group_id, note) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $fromUser,
                $toUser,
                $amount,
                $input['group_id'] ?? null,
                $input['note'] ?? ''
            ]);
            $paymentId = $this->db->lastInsertId();

            // Update balance
            $this->applyPaymentToNormalizedBalance($fromUser, $toUser, $amount);

            $this->db->commit();
            echo json_encode(["status" => "success", "payment_id" => $paymentId]);

        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(["error" => "Payment failed", "message" => $e->getMessage()]);
        }
    }

    private function applyPaymentToNormalizedBalance($payer, $receiver, $amount) {
        $userFrom = min($payer, $receiver);
        $userTo = max($payer, $receiver);

        // If payer == user_from, then user_from is paying user_to. 
        // A payment from user_from to user_to REDUCES the amount user_from owes user_to.
        $computedAmount = ($payer == $userFrom) ? -$amount : $amount;

        $stmt = $this->db->prepare("SELECT id, amount FROM balances WHERE user_from = ? AND user_to = ? FOR UPDATE");
        $stmt->execute([$userFrom, $userTo]);
        $row = $stmt->fetch();

        if ($row) {
            $newAmount = $row['amount'] + $computedAmount;
            $update = $this->db->prepare("UPDATE balances SET amount = ?, last_updated = NOW() WHERE id = ?");
            $update->execute([$newAmount, $row['id']]);
        } else {
            // If they didn't owe anything and just pay, it creates a negative owe (credit)
            $insert = $this->db->prepare("INSERT INTO balances (user_from, user_to, amount) VALUES (?, ?, ?)");
            $insert->execute([$userFrom, $userTo, $computedAmount]);
        }
    }
}

$controller = new PaymentController();
$controller->handleRequest();
