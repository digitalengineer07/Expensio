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
    <title>Settings - Expensio</title>
    <?php include __DIR__ . '/includes/theme-head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 dark:bg-black h-[100dvh] w-full flex items-center justify-center font-sans overflow-hidden md:py-3 md:px-4 lg:py-4 lg:px-6">

    <!-- Outermost Rounded Web App Shell -->
    <div class="w-full h-full bg-expensio dark:bg-[#121317] md:rounded-[36px] overflow-hidden shadow-2xl shadow-gray-200/50 dark:shadow-none border border-gray-200 dark:border-white/5 flex flex-row relative">

        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <main class="flex-1 h-full overflow-y-auto custom-scrollbar relative">
        
        <?php
        $page_title = 'Settings';
        $page_subtitle = 'Manage app preferences and security';
        include __DIR__ . '/includes/header.php'; ?>

        <!-- Settings Content -->
        <div class="px-6 pt-8 pb-20 grid grid-cols-1 lg:grid-cols-4 gap-8 animate-fade w-full max-w-7xl mx-auto">
            <!-- Sidebar Navigation (Responsive: tabs on mobile, sidebar on desktop) -->
            <div class="lg:col-span-1">
                <div class="glass-panel p-3 shadow-sm sticky top-24">
                    <nav class="flex overflow-x-auto lg:flex-col gap-1.5 custom-scrollbar pb-2 lg:pb-0">
                        <button class="shrink-0 w-full text-left px-4 py-3 bg-expensio-purple text-white shadow-md shadow-expensio-purple/20 rounded-[14px] text-[14px] font-semibold tracking-wide flex items-center gap-3.5 transition-all">
                            <i class='bx bx-user-circle text-[22px]'></i> Profile & Security
                        </button>
                        <button class="shrink-0 w-full text-left px-4 py-3 rounded-[14px] text-[14px] font-medium text-gray-500 hover:bg-gray-100/80 hover:text-gray-900 tracking-wide transition-all flex items-center gap-3.5">
                            <i class='bx bx-palette text-[22px]'></i> Appearance
                        </button>
                        <button class="shrink-0 w-full text-left px-4 py-3 rounded-[14px] text-[14px] font-medium text-gray-500 hover:bg-gray-100/80 hover:text-gray-900 tracking-wide transition-all flex items-center gap-3.5">
                            <i class='bx bx-bell text-[22px]'></i> Notifications
                        </button>
                        <button class="shrink-0 w-full text-left px-4 py-3 rounded-[14px] text-[14px] font-medium text-gray-500 hover:bg-gray-100/80 hover:text-gray-900 tracking-wide transition-all flex items-center gap-3.5">
                            <i class='bx bx-globe text-[22px]'></i> Regional
                        </button>
                        <div class="hidden lg:block border-t border-gray-100 my-2"></div>
                        <button class="shrink-0 w-full text-left px-4 py-3 rounded-[14px] text-[14px] font-medium text-gray-500 hover:bg-red-50 hover:text-red-500 tracking-wide transition-all flex items-center gap-3.5 mt-2 lg:mt-0">
                            <i class='bx bx-error-circle text-[22px]'></i> Danger Zone
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Settings Sections -->
            <div class="lg:col-span-3 space-y-8">
                
                <!-- Profile & Security -->
                <section class="glass-panel p-6 sm:p-8 shadow-[0_4px_24px_-4px_rgba(0,0,0,0.03)] border border-gray-50/50">
                    <div class="mb-6 flex items-center justify-between border-b border-gray-100 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-expensio-purple shadow-inner">
                                <i class='bx bx-lock-alt text-xl'></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 leading-tight">Profile & Security</h3>
                                <p class="text-[12px] text-gray-500 mt-0.5 font-medium">Manage your personal information and account security.</p>
                            </div>
                        </div>
                    </div>
                    
                    <form class="space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="space-y-1.5 focus-within:text-expensio-purple transition-colors">
                                <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wide">Full Name</label>
                                <input type="text" value="<?php echo htmlspecialchars($username); ?>" class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl outline-none focus:border-expensio-purple focus:bg-white focus:ring-4 focus:ring-purple-100/50 transition-all text-sm font-medium text-gray-900 shadow-sm">
                            </div>
                            <div class="space-y-1.5 focus-within:text-expensio-purple transition-colors">
                                <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wide">Email Address</label>
                                <input type="email" value="user@example.com" class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl outline-none focus:border-expensio-purple focus:bg-white focus:ring-4 focus:ring-purple-100/50 transition-all text-sm font-medium text-gray-900 shadow-sm">
                            </div>
                        </div>
                        
                        <!-- Change Password -->
                        <div class="pt-4 border-t border-gray-50 space-y-4">
                            <h4 class="text-[13px] font-bold text-gray-900">Change Password</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div class="space-y-1.5 focus-within:text-expensio-purple transition-colors">
                                    <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wide">Current Password</label>
                                    <input type="password" placeholder="••••••••" class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl outline-none focus:border-expensio-purple focus:bg-white focus:ring-4 focus:ring-purple-100/50 transition-all text-sm font-medium text-gray-900 shadow-sm">
                                </div>
                                <div class="space-y-1.5 focus-within:text-expensio-purple transition-colors">
                                    <label class="text-[11px] font-bold text-gray-500 uppercase tracking-wide">New Password</label>
                                    <input type="password" placeholder="Leave blank to keep current" class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl outline-none focus:border-expensio-purple focus:bg-white focus:ring-4 focus:ring-purple-100/50 transition-all text-sm font-medium text-gray-900 shadow-sm">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Active Sessions -->
                        <div class="pt-4 border-t border-gray-50">
                            <h4 class="text-[13px] font-bold text-gray-900 mb-3">Active Sessions</h4>
                            <div class="flex items-center justify-between p-4 bg-gray-50/50 rounded-xl border border-gray-100 hover:border-gray-200 transition-colors shadow-sm">
                                <div class="flex items-center gap-4">
                                    <i class='bx bx-laptop text-2xl text-gray-400'></i>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 leading-tight">Windows • Chrome</p>
                                        <p class="text-[11px] text-green-500 font-medium mt-0.5">Active now</p>
                                    </div>
                                </div>
                                <button type="button" class="text-xs font-semibold text-gray-500 hover:text-red-500 transition-colors bg-white border border-gray-200 hover:border-red-100 hover:bg-red-50 px-3 py-1.5 rounded-lg shadow-sm">Log out device</button>
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="button" onclick="showToast('Profile settings saved successfully')" class="px-6 py-2.5 bg-expensio-dark text-white text-sm font-bold rounded-xl hover:bg-expensio-purple transition-colors shadow-lg shadow-gray-200/50 relative overflow-hidden group">
                                <span class="relative z-10 flex items-center gap-2"><i class='bx bx-check text-lg'></i> Save Changes</span>
                            </button>
                        </div>
                    </form>
                </section>

                <!-- Appearance -->
                <section class="glass-panel p-6 sm:p-8 shadow-[0_4px_24px_-4px_rgba(0,0,0,0.03)] border border-gray-50/50">
                    <div class="mb-6 flex items-center justify-between border-b border-gray-100 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-500 shadow-inner">
                                <i class='bx bx-palette text-xl'></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 leading-tight">Appearance</h3>
                                <p class="text-[12px] text-gray-500 mt-0.5 font-medium">Customize the look and feel of your app.</p>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl border border-gray-100 bg-gray-50/30 hover:bg-gray-50/80 transition-colors shadow-sm">
                            <div class="flex gap-4 items-start">
                                <div class="w-8 h-8 rounded-full bg-white shadow-sm flex items-center justify-center border border-gray-100 shrink-0 mt-0.5 transition-colors">
                                    <i id="theme-setting-icon" class='bx bx-moon text-gray-600'></i>
                                </div>
                                <div>
                                    <h4 class="text-[15px] font-bold text-gray-900">Dark Mode</h4>
                                    <p class="text-[12px] text-gray-500 mt-1 leading-relaxed max-w-sm">Switch to dark mode for an elegant, low-light viewing experience.</p>
                                    <span id="theme-status" class="inline-flex items-center mt-2 text-[10px] font-bold text-gray-500 bg-white border border-gray-200 px-2 py-0.5 rounded-md uppercase tracking-wider shadow-sm transition-colors">Light Mode Active</span>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0 ml-12 sm:ml-0">
                                <input type="checkbox" id="darkModeToggle" class="sr-only peer" onchange="expensioToggleTheme(); updateThemeUI();">
                                <div class="w-12 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-[24px] peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-expensio-dark shadow-inner"></div>
                            </label>
                        </div>
                        <script>
                            function updateThemeUI() {
                                const isDark = document.documentElement.classList.contains('dark');
                                const icon = document.getElementById('theme-setting-icon');
                                const status = document.getElementById('theme-status');
                                if (icon && status) {
                                    icon.className = isDark ? 'bx bx-sun text-yellow-500' : 'bx bx-moon text-gray-600';
                                    icon.parentElement.className = isDark ? 'w-8 h-8 rounded-full bg-yellow-50/10 shadow-sm flex items-center justify-center border border-yellow-500/20 shrink-0 mt-0.5 transition-colors' : 'w-8 h-8 rounded-full bg-white shadow-sm flex items-center justify-center border border-gray-100 shrink-0 mt-0.5 transition-colors';
                                    status.textContent = isDark ? 'Dark Mode Active' : 'Light Mode Active';
                                    status.className = isDark 
                                        ? 'inline-flex items-center mt-2 text-[10px] font-bold text-yellow-500 bg-yellow-500/10 border border-yellow-500/20 px-2 py-0.5 rounded-md uppercase tracking-wider transition-colors'
                                        : 'inline-flex items-center mt-2 text-[10px] font-bold text-gray-500 bg-white border border-gray-200 px-2 py-0.5 rounded-md uppercase tracking-wider shadow-sm transition-colors';
                                }
                            }
                            if (localStorage.getItem('theme') === 'dark') {
                                document.getElementById('darkModeToggle').checked = true;
                                setTimeout(updateThemeUI, 100);
                            }
                        </script>
                    </div>
                </section>

                <!-- Notifications -->
                <section class="glass-panel p-6 sm:p-8 shadow-[0_4px_24px_-4px_rgba(0,0,0,0.03)] border border-gray-50/50">
                    <div class="mb-6 flex items-center justify-between border-b border-gray-100 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center text-orange-500 shadow-inner">
                                <i class='bx bx-bell text-xl'></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 leading-tight">Notifications</h3>
                                <p class="text-[12px] text-gray-500 mt-0.5 font-medium">Control how and when you receive alerts.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <!-- Email Notifications -->
                        <div class="flex items-center justify-between p-4 rounded-xl hover:bg-gray-50/80 transition-colors border border-transparent hover:border-gray-100">
                            <div class="pr-4">
                                <h4 class="text-[14px] font-bold text-gray-900">Email Notifications</h4>
                                <p class="text-[12px] text-gray-500 mt-1">Receive summarized activity reports and critical alerts via email.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" onchange="showToast('Settings saved')" class="sr-only peer" checked>
                                <div class="w-10 h-[22px] bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-[18px] after:w-[18px] after:transition-all peer-checked:bg-green-500 shadow-inner"></div>
                            </label>
                        </div>
                        
                        <!-- In-app Notifications -->
                        <div class="flex items-center justify-between p-4 rounded-xl hover:bg-gray-50/80 transition-colors border border-transparent hover:border-gray-100">
                            <div class="pr-4">
                                <h4 class="text-[14px] font-bold text-gray-900">In-app Notifications</h4>
                                <p class="text-[12px] text-gray-500 mt-1">Show push notifications within the application dashboard.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" onchange="showToast('Settings saved')" class="sr-only peer" checked>
                                <div class="w-10 h-[22px] bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-[18px] after:w-[18px] after:transition-all peer-checked:bg-green-500 shadow-inner"></div>
                            </label>
                        </div>

                        <!-- Expense Reminders -->
                        <div class="flex items-center justify-between p-4 rounded-xl hover:bg-gray-50/80 transition-colors border border-transparent hover:border-gray-100">
                            <div class="pr-4">
                                <h4 class="text-[14px] font-bold text-gray-900">Expense Reminders</h4>
                                <p class="text-[12px] text-gray-500 mt-1">Get reminded to log daily spendings and upcoming recurring bills.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" onchange="showToast('Settings saved')" class="sr-only peer">
                                <div class="w-10 h-[22px] bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-[18px] after:w-[18px] after:transition-all peer-checked:bg-green-500 shadow-inner"></div>
                            </label>
                        </div>

                        <!-- Group Settlement Reminders -->
                        <div class="flex items-center justify-between p-4 rounded-xl hover:bg-gray-50/80 transition-colors border border-transparent hover:border-gray-100">
                            <div class="pr-4">
                                <h4 class="text-[14px] font-bold text-gray-900">Group Settlement Reminders</h4>
                                <p class="text-[12px] text-gray-500 mt-1">Notify when someone requests a payment or settles a split bill.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" onchange="showToast('Settings saved')" class="sr-only peer" checked>
                                <div class="w-10 h-[22px] bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-[18px] after:w-[18px] after:transition-all peer-checked:bg-green-500 shadow-inner"></div>
                            </label>
                        </div>

                        <!-- Subscription Alerts -->
                        <div class="flex items-center justify-between p-4 rounded-xl hover:bg-gray-50/80 transition-colors border border-transparent hover:border-gray-100">
                            <div class="pr-4">
                                <h4 class="text-[14px] font-bold text-gray-900">Subscription Alerts</h4>
                                <p class="text-[12px] text-gray-500 mt-1">Warnings for upcoming subscription charges before they renew.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" onchange="showToast('Settings saved')" class="sr-only peer" checked>
                                <div class="w-10 h-[22px] bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-[18px] after:w-[18px] after:transition-all peer-checked:bg-green-500 shadow-inner"></div>
                            </label>
                        </div>
                    </div>
                </section>

                <!-- Regional & Currency -->
                <section class="glass-panel p-6 sm:p-8 shadow-[0_4px_24px_-4px_rgba(0,0,0,0.03)] border border-gray-50/50">
                    <div class="mb-6 flex items-center justify-between border-b border-gray-100 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600 shadow-inner">
                                <i class='bx bx-globe text-xl'></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 leading-tight">Regional & Currency</h3>
                                <p class="text-[12px] text-gray-500 mt-0.5 font-medium">Set how data and numbers are displayed globally.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-[12px] font-bold text-gray-900 flex items-center gap-2">
                                <i class='bx bx-money text-gray-400'></i> Default Currency
                            </label>
                            <div class="relative">
                                <select onchange="showToast('Currency updated')" class="w-full px-4 py-3.5 bg-gray-50/50 hover:bg-gray-50 focus:bg-white border border-gray-200 rounded-xl outline-none focus:border-expensio-purple focus:ring-4 focus:ring-purple-100/50 transition-all text-sm font-bold text-gray-900 cursor-pointer appearance-none shadow-sm">
                                    <option value="INR" selected>INR (₹) - Indian Rupee</option>
                                    <option value="USD">USD ($) - US Dollar</option>
                                    <option value="EUR">EUR (€) - Euro</option>
                                    <option value="GBP">GBP (£) - British Pound</option>
                                </select>
                                <i class='bx bx-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none text-lg'></i>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-2 leading-relaxed bg-blue-50/50 p-3 rounded-lg border border-blue-50 text-blue-800 font-medium">
                                <i class='bx bx-info-circle mr-1 text-blue-500'></i> Changing the currency will strictly apply immediately to all reports, budgets, and dashboards.
                            </p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[12px] font-bold text-gray-900 flex items-center gap-2">
                                <i class='bx bx-text text-gray-400'></i> Display Language
                            </label>
                            <div class="relative">
                                <select onchange="showToast('Language updated')" class="w-full px-4 py-3.5 bg-gray-50/50 hover:bg-gray-50 focus:bg-white border border-gray-200 rounded-xl outline-none focus:border-expensio-purple focus:ring-4 focus:ring-purple-100/50 transition-all text-sm font-bold text-gray-900 cursor-pointer appearance-none shadow-sm">
                                    <option value="en" selected>English (US)</option>
                                    <option value="es">Español</option>
                                    <option value="fr">Français</option>
                                    <option value="hi">हिन्दी (Hindi)</option>
                                </select>
                                <i class='bx bx-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none text-lg'></i>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Danger Zone -->
                <section class="glass-panel p-6 sm:p-8 shadow-[0_4px_24px_-4px_rgba(0,0,0,0.03)] border border-red-100 mt-12 overflow-hidden bg-gradient-to-br from-white to-red-50/10">
                    <div class="relative z-10">
                        <div class="mb-6 flex items-center justify-between border-b border-red-100 pb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center text-red-600 shadow-inner">
                                    <i class='bx bx-error text-xl'></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-red-600 leading-tight">Danger Zone</h3>
                                    <p class="text-[12px] text-red-400 mt-0.5 font-medium">Irreversible and destructive account actions.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            <!-- Deactivate -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-xl border border-red-100 bg-white hover:border-red-200 hover:bg-red-50/30 transition-colors shadow-sm">
                                <div>
                                    <h4 class="text-[14px] font-bold text-gray-900">Deactivate Account</h4>
                                    <p class="text-[12px] text-gray-500 mt-1.5 leading-relaxed max-w-md font-medium">Temporarily disable your account. Your data will be kept intact and you can reactivate anytime by logging back in.</p>
                                </div>
                                <button type="button" onclick="showConfirmModal('deactivateModal')" class="px-5 py-2.5 bg-white border border-gray-200 hover:border-gray-300 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 transition-colors whitespace-nowrap shadow-sm">Deactivate Account</button>
                            </div>
                            
                            <!-- Delete -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-xl border border-red-200 bg-red-50/50 hover:bg-red-50 transition-colors shadow-sm">
                                <div>
                                    <h4 class="text-[14px] font-bold text-red-700">Delete Account & Data</h4>
                                    <p class="text-[12px] text-red-500/80 mt-1.5 leading-relaxed max-w-md font-medium">Permanently remove your account, transactions, budgets, and all associated financial data. This cannot be undone.</p>
                                </div>
                                <button type="button" onclick="showConfirmModal('deleteModal')" class="px-5 py-2.5 bg-red-600 text-white border border-red-700 rounded-xl text-sm font-bold hover:bg-red-700 transition-colors shadow-lg shadow-red-600/30 whitespace-nowrap active:scale-[0.98]">Delete Account</button>
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </div>
        
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

    </main>
    
    <!-- Toast Notification -->
    <div id="toastNotification" class="fixed bottom-8 left-1/2 -translate-x-1/2 bg-expensio-dark text-white px-6 py-3 rounded-full shadow-2xl shadow-expensio-dark/30 font-bold text-[13px] flex items-center gap-3 transform translate-y-20 opacity-0 transition-all duration-300 ease-out z-[200]">
        <i class='bx bx-check-circle text-green-400 text-xl'></i>
        <span id="toastMessage">Settings saved successfully</span>
    </div>

    <!-- Deactivate Modal -->
    <div id="deactivateModal" class="fixed inset-0 bg-expensio-dark/60 backdrop-blur-sm z-[150] hidden flex items-center justify-center p-6 opacity-0 transition-opacity duration-300">
        <div class="bg-white max-w-md w-full rounded-[24px] shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300">
            <div class="p-6">
                <div class="w-12 h-12 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center mb-4">
                    <i class='bx bx-pause-circle text-2xl'></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 font-display mb-2">Deactivate Account?</h3>
                <p class="text-[13px] text-gray-500 leading-relaxed font-medium">Your profile and data will be hidden until you log back in. Are you sure you want to proceed?</p>
            </div>
            <div class="p-4 bg-gray-50 flex gap-3 justify-end border-t border-gray-100">
                <button type="button" onclick="hideConfirmModal('deactivateModal')" class="px-5 py-2.5 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-200 transition-colors">Cancel</button>
                <button type="button" onclick="hideConfirmModal('deactivateModal'); showToast('Account deactivated successfully');" class="px-5 py-2.5 bg-orange-500 text-white rounded-xl text-sm font-bold hover:bg-orange-600 shadow-lg shadow-orange-500/30 transition-all">Deactivate</button>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-expensio-dark/80 backdrop-blur-md z-[150] hidden flex items-center justify-center p-6 opacity-0 transition-opacity duration-300">
        <div class="bg-white max-w-sm w-full rounded-[24px] shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300 border border-red-100">
            <div class="p-6 text-center">
                <div class="w-16 h-16 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-5 border-4 border-red-50">
                    <i class='bx bx-trash text-3xl'></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 font-display mb-2">Delete Account</h3>
                <p class="text-[13px] text-gray-500 leading-relaxed font-medium">This action is permanent and cannot be undone. All your financial data will be erased.</p>
            </div>
            <div class="p-4 bg-gray-50 flex flex-col gap-2 border-t border-gray-100">
                <button type="button" onclick="hideConfirmModal('deleteModal'); showToast('Account deleted', true);" class="w-full px-5 py-3 bg-red-600 text-white rounded-xl text-sm font-bold hover:bg-red-700 shadow-lg shadow-red-600/30 transition-all">Yes, permanently delete</button>
                <button type="button" onclick="hideConfirmModal('deleteModal')" class="w-full px-5 py-3 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-200 transition-colors">No, cancel</button>
            </div>
        </div>
    </div>

    <script>
        function showToast(message, isError = false) {
            const toast = document.getElementById('toastNotification');
            document.getElementById('toastMessage').textContent = message;
            
            if(isError) {
                toast.classList.replace('bg-expensio-dark', 'bg-red-600');
                if (toast.classList.contains('bg-green-600')) toast.classList.replace('bg-green-600', 'bg-red-600');
                toast.querySelector('i').className = 'bx bx-error-circle text-white text-xl';
            } else {
                toast.classList.replace('bg-red-600', 'bg-expensio-dark');
                toast.querySelector('i').className = 'bx bx-check-circle text-green-400 text-xl';
            }

            toast.classList.remove('translate-y-20', 'opacity-0');
            
            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 3000);
        }

        function showConfirmModal(id) {
            const modal = document.getElementById(id);
            modal.classList.remove('hidden');
            // trigger reflow
            void modal.offsetWidth;
            modal.classList.remove('opacity-0');
            modal.firstElementChild.classList.remove('scale-95');
            modal.firstElementChild.classList.add('scale-100');
        }

        function hideConfirmModal(id) {
            const modal = document.getElementById(id);
            modal.classList.add('opacity-0');
            modal.firstElementChild.classList.remove('scale-100');
            modal.firstElementChild.classList.add('scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        function toggleModal(id) {
            const modal = document.getElementById(id);
            modal.classList.toggle('hidden');
        }
    </script>
    </div>
</body>
</html>
