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
    <title>Wallet - Expensio</title>
    <?php include __DIR__ . '/includes/theme-head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-expensio min-h-screen flex font-sans overflow-hidden">

    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 h-screen overflow-y-auto custom-scrollbar relative pb-20 md:pb-0">
        
        <?php
        $page_title = 'Wallet';
        $page_subtitle = 'Manage your balance and cash flow';
        include __DIR__ . '/includes/header.php'; ?>

        <!-- Layout Controls (Month picker & Manage) -->
        <div class="px-6 flex justify-between items-center mb-6 animate-fade">
            <button onclick="alert('Date picker coming soon!');" class="flex items-center gap-2 border border-gray-200 rounded-full px-4 py-2 bg-white text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-50 transition-all">
                <i class='bx bx-calendar text-gray-500 text-lg'></i>
                This month
            </button>
            <div class="flex items-center gap-3 w-full justify-end sm:w-auto">
                <button onclick="alert('Widget builder coming soon!');" class="hidden lg:flex items-center gap-2 border border-gray-200 rounded-full px-5 py-2.5 bg-white text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-50 transition-all">
                    <i class='bx bx-grid-alt text-gray-500 text-xl'></i>
                    Manage widgets
                </button>
                <button onclick="toggleModal('addIncomeModal')" class="hidden md:flex flex-row items-center gap-2 bg-green-500 text-white px-6 py-2.5 rounded-full font-semibold hover:opacity-90 transition-all shadow-md text-[15px]">
                    <i class='bx bx-plus text-xl'></i>
                    <span>Add Income</span>
                </button>
                <button onclick="toggleModal('addExpenseModal')" class="hidden md:flex flex-row items-center gap-2 bg-expensio-purple text-white px-6 py-2.5 rounded-full font-semibold hover:opacity-90 transition-all shadow-[0_4px_12px_rgba(124,58,237,0.3)] text-[15px]">
                    <i class='bx bx-minus text-xl'></i>
                    <span>Add Expense</span>
                </button>
            </div>
        </div>

        <!-- Metrics Section -->
        <section class="px-6 grid grid-cols-1 lg:grid-cols-3 gap-6 animate-fade" style="animation-delay: 0.1s;">
            <!-- Total Balance -->
            <div class="glass-panel p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] transition-all flex flex-col justify-between">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-gray-900 text-[15px]">Total balance</h3>
                    <div class="flex items-center cursor-pointer gap-1 border border-gray-200 rounded-full px-2 py-0.5 text-[11px] font-bold text-gray-500 hover:bg-gray-50 transition-colors">
                        INR <i class='bx bx-chevron-down text-sm'></i>
                    </div>
                </div>
                <h2 class="text-[32px] font-black text-gray-900 mb-6">₹<?php echo number_format($total_balance, 0); ?><span class="text-gray-300 font-medium tracking-tight">.00</span></h2>
                <div class="flex justify-between items-end">
                    <div class="space-y-4">
                        <div class="inline-flex items-center gap-0.5 bg-green-50 text-green-600 px-2.5 py-1 rounded-lg text-xs font-bold">
                            <i class='bx bx-up-arrow-alt'></i> 12.1%
                        </div>
                        <p class="text-[11px] text-gray-400">You have extra <span class="font-bold text-gray-700">+₹1,700</span><br>compared to last month</p>
                    </div>
                    <div class="space-y-3 text-[11px] text-gray-500 font-semibold mb-1">
                        <div class="flex items-center gap-3"><div class="w-6 h-6 rounded-full bg-[#F3E8FF] flex items-center justify-center text-[#8B5CF6]"><i class='bx bx-transfer-alt'></i></div> 50 transactions</div>
                        <div class="flex items-center gap-3"><div class="w-6 h-6 rounded-full bg-[#E0E7FF] flex items-center justify-center text-[#6366F1]"><i class='bx bx-category-alt'></i></div> 15 categories</div>
                    </div>
                </div>
            </div>

            <!-- Income -->
            <div class="glass-panel p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] transition-all flex flex-col justify-between">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-gray-900 text-[15px]">Income</h3>
                    <div class="flex items-center cursor-pointer gap-1 border border-gray-200 rounded-full px-2 py-0.5 text-[11px] font-bold text-gray-500 hover:bg-gray-50 transition-colors">
                        INR <i class='bx bx-chevron-down text-sm'></i>
                    </div>
                </div>
                <h2 class="text-[32px] font-black text-gray-900 mb-6">₹<?php echo number_format($total_income, 0); ?><span class="text-gray-300 font-medium tracking-tight">.00</span></h2>
                <div class="flex justify-between items-end">
                    <div class="space-y-4">
                        <div class="inline-flex items-center gap-0.5 bg-green-50 text-green-600 px-2.5 py-1 rounded-lg text-xs font-bold">
                            <i class='bx bx-up-arrow-alt'></i> 6.3%
                        </div>
                        <p class="text-[11px] text-gray-400">You earn extra <span class="font-bold text-gray-700">+₹500</span><br>compared to last month</p>
                    </div>
                    <div class="space-y-3 text-[11px] text-gray-500 font-semibold mb-1">
                        <div class="flex items-center gap-3"><div class="w-6 h-6 rounded-full bg-[#F3E8FF] flex items-center justify-center text-[#8B5CF6]"><i class='bx bx-transfer-alt'></i></div> 27 transactions</div>
                        <div class="flex items-center gap-3"><div class="w-6 h-6 rounded-full bg-[#E0E7FF] flex items-center justify-center text-[#6366F1]"><i class='bx bx-category-alt'></i></div> 6 categories</div>
                    </div>
                </div>
            </div>

            <!-- Expense -->
            <div class="glass-panel p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] transition-all flex flex-col justify-between">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-gray-900 text-[15px]">Expense</h3>
                    <div class="flex items-center cursor-pointer gap-1 border border-gray-200 rounded-full px-2 py-0.5 text-[11px] font-bold text-gray-500 hover:bg-gray-50 transition-colors">
                        INR <i class='bx bx-chevron-down text-sm'></i>
                    </div>
                </div>
                <h2 class="text-[32px] font-black text-gray-900 mb-6">₹<?php echo number_format($total_expense, 0); ?><span class="text-gray-300 font-medium tracking-tight">.00</span></h2>
                <div class="flex justify-between items-end">
                    <div class="space-y-4">
                        <div class="inline-flex items-center gap-0.5 bg-red-50 text-red-500 px-2.5 py-1 rounded-lg text-xs font-bold">
                            <i class='bx bx-down-arrow-alt'></i> 2.4%
                        </div>
                        <p class="text-[11px] text-gray-400">You spent extra <span class="font-bold text-gray-700">+₹1,222</span><br>compared to last month</p>
                    </div>
                    <div class="space-y-3 text-[11px] text-gray-500 font-semibold mb-1">
                        <div class="flex items-center gap-3"><div class="w-6 h-6 rounded-full bg-[#F3E8FF] flex items-center justify-center text-[#8B5CF6]"><i class='bx bx-transfer-alt'></i></div> 23 transactions</div>
                        <div class="flex items-center gap-3"><div class="w-6 h-6 rounded-full bg-[#E0E7FF] flex items-center justify-center text-[#6366F1]"><i class='bx bx-category-alt'></i></div> 9 categories</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Grid -->
        <section class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10 animate-fade" style="animation-delay: 0.2s;">
            
            <!-- Left Chart Column - Recent Transactions -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Transaction List Section -->
                <div class="glass-panel p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] w-full overflow-hidden flex flex-col h-full">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
                        <h3 class="text-lg font-bold text-gray-900">Recent Transactions</h3>
                        <div class="flex items-center gap-2">
                            <button onclick="loadTransactions(1)" class="border border-gray-200 rounded-lg px-3 py-2 bg-white text-sm text-gray-600 hover:bg-gray-50 transition-all">
                                <i class='bx bx-refresh'></i> Refresh
                            </button>
                        </div>
                    </div>

                    <div class="w-full overflow-x-auto flex-1 custom-scrollbar">
                        <table class="w-full text-left border-collapse" id="transactions-table">
                            <thead>
                                <tr class="text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                                    <th class="pb-3 pl-2 w-28">Date</th>
                                    <th class="pb-3 w-40">Category</th>
                                    <th class="pb-3">Description</th>
                                    <th class="pb-3 text-right pr-2 w-32">Total</th>
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
            </div>

            <!-- Budget Analytics -->
            <div class="glass-panel p-6 flex flex-col justify-between overflow-hidden relative shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)]">
                <div class="relative z-10 w-full">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-[15px] font-bold text-gray-900">Statistics</h3>
                        <div class="flex items-center gap-2 border border-gray-200 rounded-full px-2 py-0.5 text-[11px] font-bold text-gray-500 hover:bg-gray-50 cursor-pointer">
                            Expense <i class='bx bx-chevron-down'></i>
                        </div>
                    </div>
                    <p class="text-[11px] text-gray-500 leading-relaxed mb-6">You have an increase of expenses in several categories this month</p>
                    
                    <div class="aspect-square w-full max-w-[220px] mx-auto relative mt-2 mb-6">
                        <canvas id="budgetChart"></canvas>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-[11px] text-gray-500 font-semibold mb-0.5">This month expense</span>
                            <span class="text-[26px] font-black text-gray-900">₹6,222<span class="text-gray-300 font-medium">.00</span></span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-y-3 gap-x-2 relative z-10 text-[11px] font-medium text-gray-600">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-[#8B5CF6]"></div> Money transfer
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-[#E5E7EB]"></div> Cafe & Restaurants
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-[#C4B5FD]"></div> Rent
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-[#4B5563]"></div> Education
                    </div>
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
                <p class="text-xs text-gray-500 mt-1">Track your spending.</p>
            </div>
            <form action="../app/Controllers/ExpenseController.php?action=create" method="POST" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Amount (₹)</label>
                        <input type="number" step="0.01" name="amount" placeholder="0.00" required class="w-full px-4 py-3 bg-white/50 border border-white rounded-2xl outline-none focus:bg-white transition-all text-sm font-bold">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Date</label>
                        <input type="date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-4 py-3 bg-white/50 border border-white rounded-2xl outline-none focus:bg-white transition-all text-sm font-medium">
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Category</label>
                    <select name="category_id" required class="w-full px-4 py-3 bg-white/50 border border-white rounded-2xl outline-none focus:bg-white transition-all text-sm font-medium appearance-none cursor-pointer">
                        <option value="" disabled selected>Choose Category</option>
                        <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Description</label>
                    <textarea name="description" placeholder="What was this for?" rows="3" class="w-full px-4 py-3 bg-white/50 border border-white rounded-2xl outline-none focus:bg-white transition-all text-sm"></textarea>
                </div>
                <button type="submit" class="w-full bg-expensio-purple text-white py-3.5 rounded-2xl font-bold hover:opacity-90 transition-all shadow-lg active:scale-[0.98] mt-4">Track Expense</button>
            </form>
        </div>
    </div>

    <!-- Add Income Modal -->
    <div id="addIncomeModal" class="fixed inset-0 bg-expensio-dark/60 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-6 transition-all">
        <div class="glass-card w-full max-w-lg p-8 rounded-[32px] shadow-2xl animate-[fadeIn_0.5s_ease-out] relative bg-white/80">
            <button onclick="toggleModal('addIncomeModal')" class="absolute top-6 right-6 text-gray-400 hover:text-gray-900 transition-colors">
                <i class='bx bx-x text-2xl'></i>
            </button>
            <div class="mb-6">
                <h3 class="text-xl font-bold text-gray-900 font-display">Add Income</h3>
                <p class="text-xs text-gray-500 mt-1">Record money added to wallet.</p>
            </div>
            <form action="../app/Controllers/ExpenseController.php?action=create" method="POST" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Amount (₹)</label>
                        <input type="number" step="0.01" name="amount" placeholder="0.00" required class="w-full px-4 py-3 bg-white/50 border border-white rounded-2xl outline-none focus:bg-white transition-all text-sm font-bold">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Date</label>
                        <input type="date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full px-4 py-3 bg-white/50 border border-white rounded-2xl outline-none focus:bg-white transition-all text-sm font-medium">
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Category</label>
                    <select name="category_id" required class="w-full px-4 py-3 bg-white/50 border border-white rounded-2xl outline-none focus:bg-white transition-all text-sm font-medium appearance-none cursor-pointer">
                        <option value="" disabled selected>Choose Category</option>
                        <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Description</label>
                    <textarea name="description" placeholder="Source of income" rows="3" class="w-full px-4 py-3 bg-white/50 border border-white rounded-2xl outline-none focus:bg-white transition-all text-sm"></textarea>
                </div>
                <button type="submit" class="w-full bg-green-500 text-white py-3.5 rounded-2xl font-bold hover:opacity-90 transition-all shadow-lg active:scale-[0.98] mt-4">Add Income</button>
            </form>
        </div>
    </div>

    <script>
        function toggleModal(id) {
            const modal = document.getElementById(id);
            modal.classList.toggle('hidden');
    </script>
    <script>
        // Chart.js Default Typography
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#9CA3AF';

        // 1. Doughnut Chart (Statistics)
        const ctxDoughnut = document.getElementById('budgetChart').getContext('2d');
        new Chart(ctxDoughnut, {
            type: 'doughnut',
            data: {
                labels: ['Money transfer', 'Cafe & Restaurants', 'Rent', 'Education'],
                datasets: [{
                    data: [40, 25, 15, 20],
                    backgroundColor: ['#8B5CF6', '#E5E7EB', '#C4B5FD', '#4B5563'],
                    borderWidth: 4,
                    borderColor: '#ffffff',
                    hoverOffset: 0
                }]
            },
            options: {
                cutout: '80%',
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            }
        });

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
                        <td class="py-4">
                            <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-[#F3E8FF] text-[#8B5CF6]">
                                Category ${t.category_id || 'Misc'}
                            </span>
                        </td>
                        <td class="py-4 text-gray-600">${t.description || 'No description'}</td>
                        <td class="py-4 text-right pr-2 font-bold text-gray-900">₹${parseFloat(t.amount).toLocaleString()}</td>
                    </tr>`;
                });
                tbody.innerHTML = html;
            } catch (err) {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center py-10 text-red-500">Failed to load transactions.</td></tr>`;
            }
        }
        
        // Load on start
        loadTransactions(1);
    </script>
</body>
</html>
