<?php
namespace App\Models;

class Analytics {
    public $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }
    public function getMonthlyIncomeExpense($userId, $year = null) {
        if (!$year) $year = date('Y');
        
        $stmt = $this->db->prepare("
            SELECT 
                EXTRACT(MONTH FROM transaction_date) as month,
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
            FROM transactions
            WHERE user_id = ? AND EXTRACT(YEAR FROM transaction_date) = ?
            GROUP BY EXTRACT(MONTH FROM transaction_date)
            ORDER BY month ASC
        ");
        $stmt->execute([$userId, $year]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Initialize all 12 months with 0
        $data = array_fill(1, 12, ['income' => 0, 'expense' => 0]);
        foreach ($results as $row) {
            $data[(int)$row['month']] = [
                'income' => (float)$row['total_income'],
                'expense' => (float)$row['total_expense']
            ];
        }
        return $data;
    }

    // 2. Category-wise expense distribution (for pie chart & insights)
    public function getCategoryDistribution($userId, $startDate = null, $endDate = null) {
        $query = "SELECT category as name, SUM(amount) as total FROM transactions WHERE user_id = ? AND type = 'expense'";
        $params = [$userId];

        if ($startDate && $endDate) {
            $query .= " AND transaction_date BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }

        $query .= " GROUP BY category ORDER BY total DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // 3. Daily Balance Trend (for line chart)
    public function getDailyBalanceTrend($userId, $month, $year) {
        // First get starting balance before this month
        $stmtStart = $this->db->prepare("
            SELECT 
                SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as start_balance
            FROM transactions
            WHERE user_id = ? AND transaction_date < ?
        ");
        $startDateStr = "$year-$month-01";
        $stmtStart->execute([$userId, $startDateStr]);
        $startBalance = (float)$stmtStart->fetchColumn() ?: 0.0;

        // Get daily net flow for the requested month
        $stmtDaily = $this->db->prepare("
            SELECT 
                EXTRACT(DAY FROM transaction_date) as day,
                SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as net_flow
            FROM transactions
            WHERE user_id = ? AND EXTRACT(MONTH FROM transaction_date) = ? AND EXTRACT(YEAR FROM transaction_date) = ?
            GROUP BY EXTRACT(DAY FROM transaction_date)
            ORDER BY day ASC
        ");
        $stmtDaily->execute([$userId, $month, $year]);
        $dailyFlows = $stmtDaily->fetchAll(\PDO::FETCH_ASSOC);
        
        $flowMap = [];
        foreach ($dailyFlows as $flow) {
            $flowMap[(int)$flow['day']] = (float)$flow['net_flow'];
        }

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $dailyBalances = [];
        $currentBalance = $startBalance;

        for ($i = 1; $i <= $daysInMonth; $i++) {
            if (isset($flowMap[$i])) {
                $currentBalance += $flowMap[$i];
            }
            $dailyBalances[$i] = $currentBalance;
        }

        return $dailyBalances;
    }

    // Spending Insights Logic
    public function getSpendingInsights($userId) {
        $currentMonth = date('m');
        $currentYear = date('Y');
        $lastMonth = date('m', strtotime('-1 month'));
        $lastMonthYear = date('Y', strtotime('-1 month'));

        $currentCategories = $this->getCategoryDistribution($userId, "$currentYear-$currentMonth-01", date('Y-m-t'));
        $lastCategories = $this->getCategoryDistribution($userId, "$lastMonthYear-$lastMonth-01", date('Y-m-t', strtotime('-1 month')));

        $insights = [];
        
        // Find highest spending category this month
        if (!empty($currentCategories)) {
            $topCategory = $currentCategories[0];
            $insights[] = [
                'type' => 'warning',
                'message' => "Your highest expense this month is on {$topCategory['name']} (₹" . number_format($topCategory['total'], 2) . "). Consider setting a budget constraint."
            ];

            // Compare with last month
            $lastTotal = 0;
            foreach ($lastCategories as $lc) {
                if ($lc['name'] === $topCategory['name']) {
                    $lastTotal = $lc['total'];
                    break;
                }
            }

            if ($lastTotal > 0 && $topCategory['total'] > $lastTotal) {
                $percentIncrease = (($topCategory['total'] - $lastTotal) / $lastTotal) * 100;
                $insights[] = [
                    'type' => 'alert',
                    'message' => "You spent " . round($percentIncrease) . "% more on {$topCategory['name']} this month compared to last month."
                ];
            }
        }

        return $insights;
    }
}
