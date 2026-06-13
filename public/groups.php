<?php
require_once __DIR__ . '/../config/bootstrap.php';

use App\Middleware\Session;

if (!Session::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = Session::get('user_id');
$username = Session::get('username');

// Subscription check
$stmt = App\Models\Database::getInstance()->prepare("SELECT subscription_status, trial_ends_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$subUser = $stmt->fetch();
$trialEnds = new DateTime($subUser['trial_ends_at'] ?? 'now');
$now = new DateTime();
if ($subUser['subscription_status'] !== 'premium' && $trialEnds < $now) {
    echo "<script>alert('Your free trial has expired! Please subscribe to access Groups.'); window.location.href='profile.php';</script>";
    exit;
}

$groupModel = new \App\Models\Group();
$groups = $groupModel->getGroupsByUser($user_id);

$selectedGroupId = $_GET['id'] ?? null;
if (!$selectedGroupId && !empty($groups)) {
    $selectedGroupId = $groups[0]['id'];
}

$selectedGroup = null;
$groupMembers = [];
$groupBalances = [];
$groupExpenses = [];

if ($selectedGroupId) {
    foreach ($groups as $g) {
        if ($g['id'] == $selectedGroupId) {
            $selectedGroup = $g;
            break;
        }
    }
    
    if ($selectedGroup) {
        $groupMembers = $groupModel->getGroupMembers($selectedGroupId);
        $groupBalances = $groupModel->getGroupBalances($selectedGroupId);
        
        // Fetch group expenses directly via PDO for now to display them
        $stmt = $groupModel->db->prepare("
            SELECT ge.*, u.username as paid_by_name, c.name as category_name
            FROM group_expenses ge 
            JOIN users u ON ge.paid_by = u.id 
            LEFT JOIN categories c ON ge.category_id = c.id
            WHERE ge.group_id = ? ORDER BY ge.created_at DESC
        ");
        $stmt->execute([$selectedGroupId]);
        $groupExpenses = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $categoryStmt = $groupModel->db->query("SELECT id, name FROM categories WHERE type='expense' OR type IS NULL");
        $categories = $categoryStmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Groups - Expensio</title>
    <?php include __DIR__ . '/includes/theme-head.php'; ?>
</head>
<body class="bg-expensio h-[100dvh] w-full flex font-sans overflow-hidden m-0 p-0">

    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="flex-1 h-[100dvh] overflow-y-auto custom-scrollbar relative pb-24 md:pb-0">
        
        <?php
        $page_title = 'Groups';
        $page_subtitle = 'Manage shared expenses and settlements';
        include __DIR__ . '/includes/header.php'; ?>

        <div class="px-8 lg:px-10 pt-8 flex justify-between items-center mb-8 animate-fade">
            <button onclick="toggleModal('createGroupModal')" class="flex flex-row items-center gap-2 bg-expensio-purple text-white px-6 py-2.5 rounded-full font-semibold hover:opacity-90 transition-all shadow-[0_4px_12px_rgba(124,58,237,0.3)] text-[15px]">
                <i class='bx bx-plus text-xl'></i>
                <span>Create Group</span>
            </button>
            <?php if ($selectedGroup): ?>
            <div class="flex items-center gap-3">
                <button onclick="toggleModal('addGroupExpenseModal')" class="flex flex-row items-center gap-2 bg-green-500 text-white px-6 py-2.5 rounded-full font-semibold hover:opacity-90 transition-all shadow-md text-[15px]">
                    <i class='bx bx-receipt text-xl'></i>
                    <span>Add Expense</span>
                </button>
            </div>
            <?php endif; ?>
        </div>

        <section class="px-8 lg:px-10 grid grid-cols-1 lg:grid-cols-3 gap-6 animate-fade mb-10" style="animation-delay: 0.1s;">
            <!-- Group List -->
            <div class="lg:col-span-1 space-y-4">
                <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-4">My Groups</h3>
                <?php if (empty($groups)): ?>
                    <div class="glass-panel p-6 text-center shadow-sm">
                        <i class='bx bx-group text-4xl text-gray-300 mb-2'></i>
                        <p class="text-sm font-medium text-gray-500">You are not in any groups yet.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach($groups as $g): ?>
                        <a href="groups.php?id=<?php echo $g['id']; ?>" class="block glass-panel p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-md <?php echo ($selectedGroupId == $g['id']) ? 'border-2 border-expensio-purple' : ''; ?>">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h4 class="font-bold text-gray-900 text-[15px]"><?php echo htmlspecialchars($g['name']); ?></h4>
                                    <p class="text-[12px] text-gray-500 mt-1"><?php echo $g['member_count']; ?> members</p>
                                </div>
                                <i class='bx bx-chevron-right text-xl text-gray-400'></i>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Group Details -->
            <div class="lg:col-span-2 space-y-6">
                <?php if ($selectedGroup): ?>
                    <!-- Group Balances -->
                    <div class="glass-panel p-7 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border-t-2 border-t-[#8B5CF6]/20">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-gray-900 text-[16px]">Who owes whom?</h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php foreach ($groupBalances as $bal): ?>
                            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100 flex justify-between items-center">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center font-bold text-gray-600 shadow-sm">
                                        <?php echo strtoupper(substr($bal['username'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="text-[13px] font-bold text-gray-900"><?php echo htmlspecialchars($bal['username']); ?> <?php echo ($bal['id'] == $user_id) ? '(You)' : ''; ?></p>
                                        <?php if ($bal['net_balance'] > 0): ?>
                                            <p class="text-[11px] font-semibold text-green-500">Gets back ₹<?php echo number_format($bal['net_balance'], 2); ?></p>
                                        <?php elseif ($bal['net_balance'] < 0): ?>
                                            <p class="text-[11px] font-semibold text-red-500">Owes ₹<?php echo number_format(abs($bal['net_balance']), 2); ?></p>
                                        <?php else: ?>
                                            <p class="text-[11px] font-semibold text-gray-500">Settled up</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if ($bal['id'] != $user_id && $bal['net_balance'] > 0): ?>
                                    <button onclick="openSettleModal(<?php echo $bal['id']; ?>, '<?php echo htmlspecialchars($bal['username']); ?>', <?php echo $bal['net_balance']; ?>)" class="bg-expensio-purple text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:opacity-90">Pay</button>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Group Expenses -->
                    <div class="glass-panel p-7 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)]">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                            <h3 class="font-bold text-gray-900 text-[16px]">History</h3>
                            <div class="flex flex-wrap items-center gap-2">
                                <input type="date" id="filterDate" class="text-xs px-2 py-1.5 border border-gray-200 rounded-lg outline-none" onchange="filterExpenses()">
                                <select id="filterMember" class="text-xs px-2 py-1.5 border border-gray-200 rounded-lg outline-none" onchange="filterExpenses()">
                                    <option value="">All Members</option>
                                    <?php foreach($groupMembers as $member): ?>
                                    <option value="<?php echo htmlspecialchars($member['username']); ?>"><?php echo htmlspecialchars($member['username']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select id="filterCategory" class="text-xs px-2 py-1.5 border border-gray-200 rounded-lg outline-none" onchange="filterExpenses()">
                                    <option value="">All Categories</option>
                                    <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['name']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php if (empty($groupExpenses)): ?>
                            <div class="text-center py-6 text-gray-500 text-sm">No expenses yet.</div>
                        <?php else: ?>
                            <div class="space-y-4" id="expensesList">
                                <?php foreach($groupExpenses as $exp): ?>
                                <div class="expense-item flex items-center justify-between p-4 rounded-2xl hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-100"
                                     data-date="<?php echo date('Y-m-d', strtotime($exp['expense_date'])); ?>"
                                     data-member="<?php echo htmlspecialchars($exp['paid_by_name']); ?>"
                                     data-category="<?php echo htmlspecialchars($exp['category_name'] ?? ''); ?>">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center text-expensio-purple shrink-0">
                                            <i class='bx bx-receipt text-xl'></i>
                                        </div>
                                        <div>
                                            <p class="text-[14px] font-bold text-gray-900"><?php echo htmlspecialchars($exp['description']); ?></p>
                                            <p class="text-[11px] font-semibold text-gray-500 mt-0.5">
                                                Paid by <?php echo htmlspecialchars($exp['paid_by_name']); ?> 
                                                <?php echo $exp['category_name'] ? '• ' . htmlspecialchars($exp['category_name']) : ''; ?> 
                                                • <?php echo date('M d, Y', strtotime($exp['expense_date'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[15px] font-black text-gray-900">₹<?php echo number_format($exp['amount'], 2); ?></p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="glass-panel p-10 text-center shadow-sm flex flex-col items-center justify-center h-full">
                        <i class='bx bx-group text-6xl text-gray-200 mb-4'></i>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">No Group Selected</h3>
                        <p class="text-sm font-medium text-gray-500">Select a group from the list or create a new one to start tracking shared expenses.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        </main>
    </div>

    <!-- Modals -->
    <!-- Create Group Modal -->
    <div id="createGroupModal" class="fixed inset-0 bg-expensio-dark/60 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-6 transition-all">
        <div class="glass-card w-full max-w-lg p-8 rounded-[32px] shadow-2xl relative bg-white/80">
            <button onclick="toggleModal('createGroupModal')" class="absolute top-6 right-6 text-gray-400 hover:text-gray-900 transition-colors">
                <i class='bx bx-x text-2xl'></i>
            </button>
            <div class="mb-6">
                <h3 class="text-xl font-bold text-gray-900 font-display">Create New Group</h3>
                <p class="text-xs text-gray-500 mt-1">Start tracking shared expenses with friends.</p>
            </div>
            <form action="../app/Controllers/GroupController.php?action=create" method="POST" class="space-y-4">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Group Name</label>
                    <input type="text" name="name" placeholder="e.g. Goa Trip" required
                           class="w-full px-4 py-3 bg-white/50 border border-white rounded-2xl outline-none focus:bg-white transition-all text-sm font-bold">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Description (Optional)</label>
                    <input type="text" name="description" placeholder="A short description"
                           class="w-full px-4 py-3 bg-white/50 border border-white rounded-2xl outline-none focus:bg-white transition-all text-sm">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Invite Members (Email or Username)</label>
                    <input type="text" name="invite_users" placeholder="e.g. alice, bob@example.com"
                           class="w-full px-4 py-3 bg-white/50 border border-white rounded-2xl outline-none focus:bg-white transition-all text-sm">
                    <p class="text-[10px] text-gray-400 ml-1">Separate multiple users with commas.</p>
                </div>
                <button type="submit" class="w-full bg-expensio-purple text-white py-3.5 rounded-2xl font-bold hover:opacity-90 mt-4">Create Group</button>
            </form>
        </div>
    </div>

    <!-- Add Expense Modal -->
    <?php if ($selectedGroup): ?>
    <div id="addGroupExpenseModal" class="fixed inset-0 bg-expensio-dark/60 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-6 transition-all">
        <div class="glass-card w-full max-w-lg p-8 rounded-[32px] shadow-2xl relative bg-white/80">
            <button onclick="toggleModal('addGroupExpenseModal')" class="absolute top-6 right-6 text-gray-400 hover:text-gray-900 transition-colors">
                <i class='bx bx-x text-2xl'></i>
            </button>
            <div class="mb-6">
                <h3 class="text-xl font-bold text-gray-900 font-display">Add Group Expense</h3>
                <p class="text-xs text-gray-500 mt-1">Split expenses easily among all members.</p>
            </div>
            <form action="../app/Controllers/GroupController.php?action=add_expense" method="POST" class="space-y-4" onsubmit="return validateSplits()">
                <input type="hidden" name="group_id" value="<?php echo $selectedGroupId; ?>">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Amount (₹)</label>
                        <input type="number" step="0.01" name="amount" id="expenseAmount" placeholder="0.00" required oninput="updateSplits()"
                               class="w-full px-4 py-3 bg-white/50 border border-white rounded-2xl outline-none focus:bg-white transition-all text-sm font-bold">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Date</label>
                        <input type="date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required
                               class="w-full px-4 py-3 bg-white/50 border border-white rounded-2xl outline-none focus:bg-white transition-all text-sm font-medium">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Category</label>
                        <select name="category_id" required class="w-full px-4 py-3 bg-white/50 border border-white rounded-2xl outline-none focus:bg-white transition-all text-sm font-medium">
                            <option value="" disabled selected>Select</option>
                            <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Paid By</label>
                        <select name="paid_by" required class="w-full px-4 py-3 bg-white/50 border border-white rounded-2xl outline-none focus:bg-white transition-all text-sm font-medium">
                            <?php foreach($groupMembers as $member): ?>
                            <option value="<?php echo $member['id']; ?>" <?php echo $member['id'] == $user_id ? 'selected' : ''; ?>><?php echo htmlspecialchars($member['username']); ?> <?php echo $member['id'] == $user_id ? '(You)' : ''; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Description</label>
                    <input type="text" name="description" placeholder="What was this for?" required
                           class="w-full px-4 py-3 bg-white/50 border border-white rounded-2xl outline-none focus:bg-white transition-all text-sm">
                </div>
                <div class="space-y-1.5 border-t border-gray-100 pt-3">
                    <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Split Options</label>
                    <select name="split_type" id="splitType" onchange="updateSplits()" class="w-full px-4 py-3 bg-white/50 border border-white rounded-2xl outline-none focus:bg-white transition-all text-sm font-medium">
                        <option value="equal">Equally</option>
                        <option value="percentage">By Percentage</option>
                        <option value="custom">By Custom Amounts</option>
                    </select>
                </div>
                <div id="splitContainer" class="space-y-2 mt-2 hidden">
                    <?php foreach($groupMembers as $member): ?>
                    <div class="flex items-center justify-between gap-2 bg-gray-50 p-2 rounded-xl border border-gray-100">
                        <label class="text-xs font-bold text-gray-700 flex-1 ml-2"><?php echo htmlspecialchars($member['username']); ?></label>
                        <input type="number" step="0.01" name="split_pct_<?php echo $member['id']; ?>" placeholder="%" class="split-input-pct w-20 px-3 py-1.5 border border-gray-200 rounded-lg text-sm hidden font-bold text-center">
                        <input type="number" step="0.01" name="split_amt_<?php echo $member['id']; ?>" placeholder="₹0" class="split-input-amt w-24 px-3 py-1.5 border border-gray-200 rounded-lg text-sm hidden font-bold text-center">
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="w-full bg-green-500 text-white py-3.5 rounded-2xl font-bold hover:opacity-90 mt-4">Add Expense</button>
            </form>
        </div>
    </div>

    <!-- Settle Up Modal -->
    <div id="settleModal" class="fixed inset-0 bg-expensio-dark/60 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-6 transition-all">
        <div class="glass-card w-full max-w-sm p-8 rounded-[32px] shadow-2xl relative bg-white/80">
            <button onclick="toggleModal('settleModal')" class="absolute top-6 right-6 text-gray-400 hover:text-gray-900 transition-colors">
                <i class='bx bx-x text-2xl'></i>
            </button>
            <div class="mb-6 text-center">
                <h3 class="text-xl font-bold text-gray-900 font-display">Settle Up</h3>
                <p class="text-xs text-gray-500 mt-1">Record a payment to <span id="settlePayeeName" class="font-bold text-gray-800"></span></p>
            </div>
            <form action="../app/Controllers/GroupController.php?action=settle" method="POST" class="space-y-4">
                <input type="hidden" name="group_id" value="<?php echo $selectedGroupId; ?>">
                <input type="hidden" name="payee_id" id="settlePayeeId">
                <div class="space-y-1.5 text-center">
                    <label class="text-[10px] font-black uppercase text-gray-400 ml-1">Amount to pay (₹)</label>
                    <input type="number" step="0.01" name="amount" id="settleAmount" required readonly
                           class="w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded-2xl outline-none text-center text-lg font-black text-gray-900">
                </div>
                <button type="submit" class="w-full bg-expensio-purple text-white py-3.5 rounded-2xl font-bold hover:opacity-90 mt-4">Confirm Payment</button>
            </form>
        </div>
    </div>
    <?php endif; ?>
    </main>

    <script>
        function toggleModal(id) {
            const modal = document.getElementById(id);
            if (modal) modal.classList.toggle('hidden');
        }

        function openSettleModal(payeeId, payeeName, amount) {
            document.getElementById('settlePayeeId').value = payeeId;
            document.getElementById('settlePayeeName').innerText = payeeName;
            document.getElementById('settleAmount').value = amount;
            toggleModal('settleModal');
        }

        function updateSplits() {
            const splitType = document.getElementById('splitType').value;
            const container = document.getElementById('splitContainer');
            const pctInputs = document.querySelectorAll('.split-input-pct');
            const amtInputs = document.querySelectorAll('.split-input-amt');
            
            if (splitType === 'equal') {
                container.classList.add('hidden');
                pctInputs.forEach(el => { el.classList.add('hidden'); el.required = false; });
                amtInputs.forEach(el => { el.classList.add('hidden'); el.required = false; });
            } else if (splitType === 'percentage') {
                container.classList.remove('hidden');
                pctInputs.forEach(el => { el.classList.remove('hidden'); el.required = true; });
                amtInputs.forEach(el => { el.classList.add('hidden'); el.required = false; });
            } else if (splitType === 'custom') {
                container.classList.remove('hidden');
                pctInputs.forEach(el => { el.classList.add('hidden'); el.required = false; });
                amtInputs.forEach(el => { el.classList.remove('hidden'); el.required = true; });
            }
        }

        function validateSplits() {
            const amount = parseFloat(document.getElementById('expenseAmount').value) || 0;
            const splitType = document.getElementById('splitType').value;

            if (splitType === 'percentage') {
                let totalPct = 0;
                document.querySelectorAll('.split-input-pct').forEach(el => totalPct += (parseFloat(el.value) || 0));
                if (Math.abs(totalPct - 100) > 0.01) {
                    alert('Percentages must add up to exactly 100%. Currently: ' + totalPct + '%');
                    return false;
                }
            } else if (splitType === 'custom') {
                let totalAmt = 0;
                document.querySelectorAll('.split-input-amt').forEach(el => totalAmt += (parseFloat(el.value) || 0));
                if (Math.abs(totalAmt - amount) > 0.01) {
                    alert('Custom amounts must add up to the total amount (₹' + amount + '). Currently: ₹' + totalAmt);
                    return false;
                }
            }
            return true;
        }

        function filterExpenses() {
            const date = document.getElementById('filterDate').value;
            const member = document.getElementById('filterMember').value;
            const category = document.getElementById('filterCategory').value;

            document.querySelectorAll('.expense-item').forEach(el => {
                let show = true;
                if (date && el.getAttribute('data-date') !== date) show = false;
                if (member && el.getAttribute('data-member') !== member) show = false;
                if (category && el.getAttribute('data-category') !== category) show = false;
                
                el.style.display = show ? 'flex' : 'none';
            });
        }
    </script>
</body>
</html>
