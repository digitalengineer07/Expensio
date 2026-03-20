<?php
require_once __DIR__ . '/../config/bootstrap.php';

use App\Middleware\Session;
use App\Models\Expense;
use App\Models\Category;
use App\Models\Project;

if (!Session::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = Session::get('user_id');
$username = Session::get('username');
$user_role = Session::get('user_role');

$expenseModel = new Expense();
$categoryModel = new Category();
$projectModel = new Project();

$total_balance = $expenseModel->getNetBalance($user_id);
$total_income = $expenseModel->getTotalIncome($user_id);
$total_expense = $expenseModel->getTotalExpense($user_id);
$recent_expenses = $expenseModel->getAllByUser($user_id, 5);
$categories = $categoryModel->getByUser($user_id);
$projects = ($user_role === 'Engineer' || $user_role === 'Admin') ? $projectModel->getByUser($user_id) : [];

// Chart Data
$chart_stats = $expenseModel->getCategoryStats($user_id);
$chart_labels = json_encode(array_column($chart_stats, 'name'));
$chart_data = json_encode(array_column($chart_stats, 'total'));

// Premium Metrics
$metrics = [
    ['label' => 'Total Expenses', 'value' => '₹'.number_format($total_balance, 0), 'icon' => 'wallet', 'color' => 'text-blue-500', 'bg' => 'bg-blue-50'],
    ['label' => 'Monthly Savings', 'value' => '₹2,450', 'icon' => 'trending-up', 'color' => 'text-emerald-500', 'bg' => 'bg-emerald-50'],
    ['label' => 'Active Budgets', 'value' => '4', 'icon' => 'pie-chart-alt-2', 'color' => 'text-purple-500', 'bg' => 'bg-purple-50'],
    ['label' => 'Trust Score', 'value' => '748', 'icon' => 'shield-quarter', 'color' => 'text-amber-500', 'bg' => 'bg-amber-50'],
];
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Expensio</title>
    <?php include __DIR__ . '/includes/theme-head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-expensio min-h-screen flex font-sans overflow-hidden">

    <!-- Sidebar -->
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 h-screen overflow-y-auto custom-scrollbar relative pb-20 md:pb-0">
        
        <?php
        $page_title = 'Transactions';
        $page_subtitle = 'View your past expenses and income';
        include __DIR__ . '/includes/header.php'; ?>

        <!-- Layout Controls (Month picker & Manage) -->
        <div class="px-6 flex justify-between items-center mb-6 animate-fade">
            <button class="flex items-center gap-2 border border-gray-200 rounded-full px-4 py-2 bg-white text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-50 transition-all">
                <i class='bx bx-calendar text-gray-500 text-lg'></i>
                Filter By Date
            </button>
            <div class="flex items-center gap-3 w-full justify-end sm:w-auto">
                <button onclick="toggleModal('addExpenseModal')" class="hidden md:flex flex-row items-center gap-2 bg-expensio-purple text-white px-6 py-2.5 rounded-full font-semibold hover:opacity-90 transition-all shadow-[0_4px_12px_rgba(124,58,237,0.3)] text-[15px]">
                    <i class='bx bx-plus text-xl'></i>
                    <span>Add Expense</span>
                </button>
            </div>
        </div>

        <!-- Transaction List Section -->
        <section class="px-6 mb-10 animate-fade" id="transactions-container">
            <div class="glass-panel p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] w-full overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
                    <h3 class="text-lg font-bold text-gray-900">Recent Transactions</h3>
                    <div class="flex items-center gap-2">
                        <select id="filterType" class="border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-600 bg-white outline-none">
                            <option value="all">All Types</option>
                            <option value="expense">Expense</option>
                            <option value="settlement">Settlements</option>
                        </select>
                        <button onclick="loadTransactions(1)" class="border border-gray-200 rounded-lg px-3 py-2 bg-white text-sm text-gray-600 hover:bg-gray-50 transition-all">
                            <i class='bx bx-refresh'></i> Refresh
                        </button>
                    </div>
                </div>

                <div class="w-full overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="transactions-table">
                        <thead>
                            <tr class="text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                                <th class="pb-3 pl-2">Date</th>
                                <th class="pb-3">Description</th>
                                <th class="pb-3">Category</th>
                                <th class="pb-3 text-right pr-2">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody id="transactions-tbody" class="text-sm">
                            <!-- JS will populate -->
                            <tr>
                                <td colspan="4" class="text-center py-10 text-gray-400">Loading transactions...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        
        <!-- Floating Action Button -->
        <button onclick="toggleModal('addExpenseModal')" class="fixed bottom-8 right-8 w-14 h-14 bg-expensio-dark text-white rounded-[20px] shadow-2xl flex items-center justify-center hover:bg-expensio-purple transition-all transform hover:scale-110 active:scale-95 group z-40">
            <i class='bx bx-plus text-3xl group-hover:rotate-90 transition-all duration-300'></i>
            <span class="absolute right-full mr-4 px-4 py-2 bg-expensio-dark text-white text-xs font-bold rounded-xl opacity-0 group-hover:opacity-100 transition-all pointer-events-none whitespace-nowrap shadow-xl">Add New Expense</span>
        </button>

    <!-- Add Expense Modal -->
    <div id="addExpenseModal" class="fixed inset-0 bg-expensio-dark/60 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-6 transition-all">
        <div class="glass-card w-full max-w-lg p-8 rounded-[32px] shadow-2xl animate-[fadeIn_0.5s_ease-out] relative bg-white/80">
            <button onclick="toggleModal('addExpenseModal')" class="absolute top-6 right-6 text-gray-400 hover:text-gray-900 transition-colors">
                <i class='bx bx-x text-2xl'></i>
            </button>
            
            <div class="mb-6">
                <h3 class="text-xl font-bold text-gray-900 font-display">Add New Expense</h3>
                <p class="text-xs text-gray-500 mt-1">Fill in the details to track your spending.</p>
            </div>

            <form id="addExpenseForm" onsubmit="event.preventDefault(); submitTransaction();" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Amount (₹)</label>
                        <input type="number" step="0.01" name="amount" placeholder="0.00" required
                               class="w-full px-4 py-3 bg-white/50 border border-white rounded-2xl outline-none focus:bg-white transition-all text-sm font-bold">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Date</label>
                        <input type="date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required
                               class="w-full px-4 py-3 bg-white/50 border border-white rounded-2xl outline-none focus:bg-white transition-all text-sm font-medium">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Category</label>
                    <select name="category_id" required
                            class="w-full px-4 py-3 bg-white/50 border border-white rounded-2xl outline-none focus:bg-white transition-all text-sm font-medium appearance-none cursor-pointer">
                        <option value="" disabled selected>Choose Category</option>
                        <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Description</label>
                    <textarea name="description" placeholder="What was this for?" rows="3"
                              class="w-full px-4 py-3 bg-white/50 border border-white rounded-2xl outline-none focus:bg-white transition-all text-sm"></textarea>
                </div>

                <button type="submit" 
                        class="w-full bg-expensio-purple text-white py-3.5 rounded-2xl font-bold hover:opacity-90 transition-all shadow-lg shadow-purple-500/20 active:scale-[0.98] mt-4">
                    Track Expense
                </button>
            </form>
        </div>
    </div>

    <script>
        function toggleModal(id) {
            const modal = document.getElementById(id);
            modal.classList.toggle('hidden');
        }
    </script>
    <script>
        // Transaction Page Logic
        async function loadTransactions(page = 1) {
            const tbody = document.getElementById('transactions-tbody');
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-10 text-gray-400">Loading transactions...</td></tr>';
            try {
                const res = await fetch(`../api/transactions/index.php?page=${page}&limit=20`);
                if (!res.ok) throw new Error('API Error');
                const data = await res.json();
                
                if (!data.data || data.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center py-10 text-gray-400">No transactions found.</td></tr>';
                    return;
                }
                
                let html = '';
                data.data.forEach(t => {
                    const dt = new Date(t.expense_date);
                    const formattedDate = dt.toLocaleDateString();
                    html += `<tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                        <td class="py-4 pl-2 font-medium text-gray-900 whitespace-nowrap">${formattedDate}</td>
                        <td class="py-4 text-gray-600">${t.description || 'No description'}</td>
                        <td class="py-4">
                            <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-[#F3E8FF] text-[#8B5CF6]">
                                Category ${t.category_id || 'Misc'}
                            </span>
                        </td>
                        <td class="py-4 text-right pr-2 font-bold text-gray-900">₹${parseFloat(t.amount).toLocaleString()}</td>
                    </tr>`;
                });
                tbody.innerHTML = html;
            } catch (err) {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center py-10 text-red-500">Failed to load transactions.</td></tr>`;
            }
        }
        
        // Create New Transaction Logic
        async function submitTransaction() {
            const form = document.getElementById('addExpenseForm');
            const formData = new FormData(form);
            
            const payload = {
                total_amount: formData.get('amount'),
                date: formData.get('expense_date'),
                category: formData.get('category_id'), // map to category in logic
                note: formData.get('description'),
                split_type: 'equal',
                splits: [] // Handled as personal for now in UI
            };

            try {
                const res = await fetch(`../api/transactions/index.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                if (!res.ok) {
                    const errData = await res.json().catch(() => ({}));
                    throw new Error(errData.message || 'Failed to create transaction');
                }
                
                // Hide modal & reset form
                toggleModal('addExpenseModal');
                form.reset();

                // Reload transactions to match new data
                loadTransactions(1);
            } catch (err) {
                alert('Error processing transaction: ' + err.message);
                console.error(err);
            }
        }
        
        // Load on start
        loadTransactions(1);
    </script>
</body>
</html>
