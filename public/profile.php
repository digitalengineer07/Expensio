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

$stmt = App\Models\Database::getInstance()->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$currentUser = $stmt->fetch();

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
    <title>Profile - Expensio</title>
    <?php include __DIR__ . '/includes/theme-head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-expensio min-h-screen flex font-sans overflow-hidden">

    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="flex-1 h-screen overflow-y-auto custom-scrollbar relative pb-20 md:pb-0">
        
        <?php
        $page_title = 'Profile';
        $page_subtitle = 'View and update your personal details';
        include __DIR__ . '/includes/header.php'; ?>

        <!-- Profile Content -->
        <div class="px-6 pb-10 grid grid-cols-1 lg:grid-cols-3 gap-6 animate-fade">
            <!-- Left Column: Avatar & Quick Info -->
            <div class="lg:col-span-1 space-y-6">
                <div class="glass-panel p-8 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] flex flex-col items-center text-center">
                    <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-xl relative group mb-4">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($currentUser['username']); ?>&background=random&color=fff&size=128" alt="Profile" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                            <i class='bx bx-camera text-white text-3xl'></i>
                        </div>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($currentUser['username']); ?></h2>
                    <p class="text-gray-500 text-sm"><?php echo htmlspecialchars($currentUser['email'] ?? ''); ?></p>
                    
                    <div class="mt-6 pt-6 border-t border-gray-100 w-full text-left space-y-4">
                        <div>
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Role</p>
                            <span class="inline-flex items-center gap-1.5 bg-purple-50 text-expensio-purple px-3 py-1 rounded-full text-xs font-bold">
                                <i class='bx bxs-badge-check'></i>
                                <?php echo htmlspecialchars($user_role); ?>
                            </span>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Status</p>
                            <span class="inline-flex items-center gap-1.5 bg-green-50 text-green-600 px-3 py-1 rounded-full text-xs font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-600"></span> Active
                            </span>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Verified</p>
                            <p class="text-sm font-medium text-gray-900"><?php echo $currentUser['is_verified'] ? 'Yes' : 'No'; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Settings Forms -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Personal Info -->
                <div class="glass-panel p-8 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)]">
                    <h3 class="text-lg font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">Personal Details</h3>
                    <form action="javascript:alert('Profile updated');" class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-bold text-gray-500 uppercase">Username</label>
                                <input type="text" name="username" value="<?php echo htmlspecialchars($currentUser['username']); ?>" class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:border-expensio-purple focus:ring-1 focus:ring-expensio-purple outline-none transition-all text-sm font-medium text-gray-900">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-bold text-gray-500 uppercase">Email Address</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($currentUser['email'] ?? ''); ?>" class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:border-expensio-purple focus:ring-1 focus:ring-expensio-purple outline-none transition-all text-sm font-medium text-gray-900">
                            </div>
                            <div class="space-y-1.5 md:col-span-2">
                                <label class="text-[11px] font-bold text-gray-500 uppercase">Phone Number</label>
                                <input type="tel" name="phone" placeholder="Not provided" class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:border-expensio-purple focus:ring-1 focus:ring-expensio-purple outline-none transition-all text-sm font-medium text-gray-900">
                            </div>
                        </div>
                        <div class="pt-4 flex justify-end">
                            <button type="submit" class="bg-expensio-purple text-white px-6 py-2.5 rounded-xl font-semibold hover:opacity-90 transition-all shadow-md text-sm">Save Changes</button>
                        </div>
                    </form>
                </div>

                <!-- Security -->
                <div class="glass-panel p-8 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)]">
                    <h3 class="text-lg font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">Change Password</h3>
                    <form action="javascript:alert('Password changed');" class="space-y-5">
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-bold text-gray-500 uppercase">Current Password</label>
                            <input type="password" required class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:border-expensio-purple focus:ring-1 focus:ring-expensio-purple outline-none transition-all text-sm font-medium text-gray-900">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-bold text-gray-500 uppercase">New Password</label>
                                <input type="password" required class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:border-expensio-purple focus:ring-1 focus:ring-expensio-purple outline-none transition-all text-sm font-medium text-gray-900">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-bold text-gray-500 uppercase">Confirm Password</label>
                                <input type="password" required class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:border-expensio-purple focus:ring-1 focus:ring-expensio-purple outline-none transition-all text-sm font-medium text-gray-900">
                            </div>
                        </div>
                        <div class="pt-4 flex justify-end">
                            <button type="submit" class="bg-gray-900 text-white px-6 py-2.5 rounded-xl font-semibold hover:bg-gray-800 transition-all shadow-md text-sm">Update Password</button>
                        </div>
                    </form>
                </div>
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

    <script>
        function toggleModal(id) {
            const modal = document.getElementById(id);
            modal.classList.toggle('hidden');
        }
    </script>
</body>
</html>
