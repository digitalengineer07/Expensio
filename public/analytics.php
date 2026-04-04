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
    <title>Analytics - Expensio</title>
    <?php include __DIR__ . '/includes/theme-head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 dark:bg-black h-[100dvh] w-full flex items-center justify-center font-sans overflow-hidden md:py-3 md:px-4 lg:py-4 lg:px-6">

    <!-- Outermost Rounded Web App Shell -->
    <div class="w-full h-full bg-expensio dark:bg-[#121317] md:rounded-[36px] overflow-hidden shadow-2xl shadow-gray-200/50 dark:shadow-none border border-gray-200 dark:border-white/5 flex flex-row relative">

        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <main class="flex-1 h-full overflow-y-auto custom-scrollbar relative pb-20 md:pb-0">
        
        <?php
        $page_title = 'Analytics';
        $page_subtitle = 'Detailed overview of your financial situation';
        include __DIR__ . '/includes/header.php'; ?>

        <!-- Layout Controls (Month picker & Manage) -->
        <div class="px-8 lg:px-10 pt-8 flex justify-between items-center mb-8 animate-fade">
            <button onclick="alert('Date picker coming soon!');" class="flex items-center gap-2 border border-gray-200/60 rounded-full px-5 py-2.5 bg-white text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-50 transition-all hover:border-expensio-purple/30">
                <i class='bx bx-calendar text-gray-500 text-lg'></i>
                This month
            </button>
            <div class="flex items-center gap-3 w-full justify-end sm:w-auto">
                <button onclick="alert('Widget builder coming soon!');" class="hidden lg:flex items-center gap-2 border border-gray-200 rounded-full px-5 py-2.5 bg-white text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-50 transition-all">
                    <i class='bx bx-grid-alt text-gray-500 text-xl'></i>
                    Manage widgets
                </button>
                <button onclick="toggleModal('addExpenseModal')" class="hidden md:flex flex-row items-center gap-2 bg-expensio-purple text-white px-6 py-2.5 rounded-full font-semibold hover:opacity-90 transition-all shadow-[0_4px_12px_rgba(124,58,237,0.3)] text-[15px]">
                    <i class='bx bx-plus text-xl'></i>
                    <span>Add Expense</span>
                </button>
            </div>
        </div>

        <!-- Metrics Section -->
        <section class="px-8 lg:px-10 grid grid-cols-1 lg:grid-cols-3 gap-6 animate-fade" style="animation-delay: 0.1s;">
            <!-- Total Balance -->
            <div class="glass-panel p-7 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_12px_30px_-6px_rgba(0,0,0,0.1)] flex flex-col justify-between border-t-2 border-t-[#8B5CF6]/20">
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
        <section class="px-8 lg:px-10 grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10 animate-fade" style="animation-delay: 0.2s;">
            
            <!-- Left Chart Column -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Total Balance Overview (Line Chart) -->
                <div class="glass-panel p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)]">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
                        <h3 class="text-[15px] font-bold text-gray-900">Total balance overview</h3>
                        <div class="flex flex-wrap items-center gap-5 text-[11px] font-semibold">
                            <div class="flex items-center gap-2 text-[#8B5CF6]"><div class="w-2 h-2 rounded-full bg-[#8B5CF6]"></div> This month</div>
                            <div class="flex items-center gap-2 text-gray-400"><div class="w-1.5 h-1.5 rotate-45 border-2 border-gray-300"></div> Same period last month</div>
                            <div class="flex items-center gap-1 border border-gray-200 rounded-full px-3 py-1 text-gray-600 hover:bg-gray-50 cursor-pointer ml-auto sm:ml-0">
                                Total balance <i class='bx bx-chevron-down text-sm'></i>
                            </div>
                        </div>
                    </div>
                    <div class="w-full h-[220px] relative">
                        <canvas id="balanceLineChart"></canvas>
                    </div>
                </div>

                <!-- Comparing of budget and expence (Bar Chart) -->
                <div class="glass-panel p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)]">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
                        <h3 class="text-[15px] font-bold text-gray-900">Comparing of budget and expence</h3>
                        <div class="flex flex-wrap items-center gap-5 text-[11px] font-semibold">
                            <div class="flex items-center gap-2 text-[#8B5CF6]"><div class="w-2 h-2 rounded-full bg-[#8B5CF6]"></div> Expense</div>
                            <div class="flex items-center gap-2 text-[#C4B5FD]"><div class="w-2 h-2 rounded-full bg-[#C4B5FD]"></div> Budget</div>
                            <div class="flex items-center gap-1 border border-gray-200 rounded-full px-3 py-1 text-gray-600 hover:bg-gray-50 cursor-pointer ml-auto sm:ml-0">
                                This year <i class='bx bx-chevron-down text-sm'></i>
                            </div>
                        </div>
                    </div>
                    <div class="w-full h-[200px] relative">
                        <canvas id="budgetBarChart"></canvas>
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

        <!-- Deep Analytics Grid -->
        <section class="px-8 lg:px-10 grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10 animate-fade" style="animation-delay: 0.3s;">
            <!-- Cash Flow Waterfall -->
            <div class="glass-panel p-7 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] transition-all duration-300 hover:shadow-[0_12px_30px_-6px_rgba(0,0,0,0.08)] relative">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-[15px] font-bold text-gray-900">Cash Flow Tracking</h3>
                    <div class="flex items-center gap-2 border border-gray-200 rounded-full px-3 py-1 text-[11px] font-bold text-gray-500 hover:bg-gray-50 cursor-pointer transition-colors shadow-sm">
                        Quarterly <i class='bx bx-chevron-down text-sm'></i>
                    </div>
                </div>
                <div class="h-[260px] w-full relative">
                    <canvas id="cashflowChart"></canvas>
                </div>
            </div>

            <!-- Spending by Project/Category Radar -->
            <div class="glass-panel p-7 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] transition-all duration-300 hover:shadow-[0_12px_30px_-6px_rgba(0,0,0,0.08)] relative">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-[15px] font-bold text-gray-900">Spending Radar</h3>
                    <div class="flex items-center gap-2 border border-gray-200 rounded-full px-3 py-1 text-[11px] font-bold text-gray-500 hover:bg-gray-50 cursor-pointer transition-colors shadow-sm">
                        This Month <i class='bx bx-chevron-down text-sm'></i>
                    </div>
                </div>
                <div class="h-[260px] w-full relative flex justify-center mt-2">
                    <canvas id="patternsChart"></canvas>
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

            <form action="../app/Controllers/ExpenseController.php?action=create" method="POST" class="space-y-4">
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

        // 2. Line Chart (Total Balance Overview)
        const ctxLine = document.getElementById('balanceLineChart').getContext('2d');
        const gradientLine = ctxLine.createLinearGradient(0, 0, 0, 200);
        gradientLine.addColorStop(0, 'rgba(139, 92, 246, 0.2)');
        gradientLine.addColorStop(1, 'rgba(139, 92, 246, 0)');

        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: ['1 Jul', '3 Jul', '5 Jul', '7 Jul', '9 Jul', '11 Jul', '13 Jul', '15 Jul', '17 Jul', '19 Jul'],
                datasets: [
                    {
                        label: 'This month',
                        data: [13000, 12000, 15000, 8000, 12000, 9000, 14000, 10000, 12000, 13000],
                        borderColor: '#8B5CF6',
                        backgroundColor: gradientLine,
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#8B5CF6',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Same period last month',
                        data: [12000, 15000, 13000, 10000, 14000, 6000, 12000, 8000, 10000, 12000],
                        borderColor: '#E5E7EB',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        tension: 0.4,
                        fill: false,
                        pointRadius: 0,
                        pointHoverRadius: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: '#ffffff',
                        titleColor: '#1F2024',
                        bodyColor: '#6B7280',
                        borderColor: '#F3F4F6',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false,
                        callbacks: {
                            label: function(context) { return '₹' + context.parsed.y.toLocaleString(); }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 20000,
                        ticks: { stepSize: 5000, callback: function(value) { return '₹' + value.toLocaleString(); }, font: {size: 10} },
                        border: { display: false },
                        grid: { color: '#F3F4F6', drawBorder: false }
                    },
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: { font: {size: 10} }
                    }
                }
            }
        });

        // 3. Grouped Bar Chart (Comparing budget and expence)
        const ctxBar = document.getElementById('budgetBarChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                datasets: [
                    {
                        label: 'Budget',
                        data: [5000, 2500, 4000, 5000, 3000, 7000, 3500],
                        backgroundColor: '#C4B5FD',
                        borderRadius: {topLeft: 20, topRight: 20, bottomLeft: 0, bottomRight: 0},
                        barPercentage: 0.6,
                        categoryPercentage: 0.5,
                        grouped: false
                    },
                    {
                        label: 'Expense',
                        data: [4500, 2000, 3500, 4800, 2500, 6500, 3000],
                        backgroundColor: '#8B5CF6',
                        borderRadius: {topLeft: 20, topRight: 20, bottomLeft: 0, bottomRight: 0},
                        barPercentage: 0.4,
                        categoryPercentage: 0.5,
                        grouped: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: '#ffffff',
                        titleColor: '#1F2024',
                        bodyColor: '#6B7280',
                        borderColor: '#F3F4F6',
                        borderWidth: 1,
                        padding: 10,
                        callbacks: {
                            label: function(context) { return context.dataset.label + ': ₹' + context.parsed.y.toLocaleString(); }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 7500,
                        ticks: { stepSize: 2500, callback: function(value) { return '₹' + value.toLocaleString(); }, font: {size: 10} },
                        border: { display: false },
                        grid: { color: '#F3F4F6', drawBorder: false }
                    },
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: { font: {size: 10} }
                    }
                }
            }
        });

        // 4. Cash Flow Analysis (Bar)
        const ctxCashflow = document.getElementById('cashflowChart').getContext('2d');
        new Chart(ctxCashflow, {
            type: 'bar',
            data: {
                labels: ['Q1', 'Q2', 'Q3', 'Q4'],
                datasets: [
                    {
                        label: 'Gross Income',
                        data: [45000, 52000, 48000, 61000],
                        backgroundColor: '#10B981', // green
                        borderRadius: {topLeft: 6, topRight: 6, bottomLeft: 0, bottomRight: 0},
                        barPercentage: 0.5,
                        categoryPercentage: 0.6
                    },
                    {
                        label: 'Total Expenses',
                        data: [32000, 41000, 39000, 45000],
                        backgroundColor: '#EF4444', // red
                        borderRadius: {topLeft: 6, topRight: 6, bottomLeft: 0, bottomRight: 0},
                        barPercentage: 0.5,
                        categoryPercentage: 0.6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: '#ffffff',
                        titleColor: '#1F2024',
                        bodyColor: '#6B7280',
                        borderColor: '#F3F4F6',
                        borderWidth: 1,
                        padding: 12,
                        callbacks: {
                            label: function(context) { return context.dataset.label + ': ₹' + context.parsed.y.toLocaleString(); }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#F3F4F6', drawBorder: false, borderDash: [5, 5] },
                        border: { display: false },
                        ticks: { callback: function(value) { return '₹' + (value/1000) + 'k'; }, font: {size: 10} }
                    },
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: { font: {size: 11, weight: 'bold'} }
                    }
                }
            }
        });

        // 5. Spending Patterns (Radar Chart)
        const ctxPatterns = document.getElementById('patternsChart').getContext('2d');
        new Chart(ctxPatterns, {
            type: 'radar',
            data: {
                labels: ['Dining', 'Transit', 'Housing', 'Media', 'Retail', 'Bills'],
                datasets: [{
                    label: 'Current Month',
                    data: [85, 45, 100, 60, 40, 75],
                    backgroundColor: 'rgba(124, 58, 237, 0.15)', // purple transparent
                    borderColor: '#7C3AED',
                    borderWidth: 2,
                    pointBackgroundColor: '#7C3AED',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#7C3AED',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1F2024',
                        titleColor: '#ffffff',
                        bodyColor: '#D1D5DB',
                        padding: 10,
                        displayColors: false
                    }
                },
                scales: {
                    r: {
                        angleLines: { color: 'rgba(243, 244, 246, 0.8)' },
                        grid: { color: 'rgba(243, 244, 246, 0.8)', circular: true },
                        pointLabels: { font: { size: 10, family: "'Inter', sans-serif", weight: 'bold' }, color: '#6B7280' },
                        ticks: { display: false, beginAtZero: true } // Hide numbers on the radar rings
                    }
                }
            }
        });
    </script>
    </div>
</body>
</html>
