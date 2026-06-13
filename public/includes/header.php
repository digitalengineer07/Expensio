<?php
// Get page title from caller or default
$page_title = $page_title ?? 'Expensio';
$page_subtitle = $page_subtitle ?? '';
?>
<!-- Universal Page Header - Included by all main pages -->
<header class="sticky top-0 z-40 px-6 py-4 md:py-0 md:h-[88px] flex flex-col md:flex-row md:items-center justify-between gap-3 bg-[#F9FAFB] dark:bg-[#121317] border-b border-gray-200/60 transition-all">
    <div>
        <h1 class="text-[26px] font-bold text-gray-900 font-display tracking-tight"><?php echo htmlspecialchars($page_title); ?></h1>
        <?php if ($page_subtitle): ?>
        <p class="text-gray-500 text-[14px] mt-0.5 font-medium"><?php echo $page_subtitle; ?></p>
        <?php endif; ?>
    </div>

    <div class="flex items-center gap-4 flex-wrap w-full md:w-auto">
        <!-- Search bar -->
        <div class="relative hidden lg:flex items-center bg-white border border-gray-200 shadow-sm rounded-full px-4 py-2.5 gap-2 transition-all">
            <i class='bx bx-search text-gray-400 text-[20px]'></i>
            <input type="text" placeholder="Search task" class="bg-transparent border-none outline-none text-[14px] font-medium w-40 placeholder-gray-400 text-gray-700">
            <div class="bg-gray-100 rounded md flex items-center justify-center px-1.5 py-0.5 ml-2">
                <span class="text-[10px] text-gray-500 font-bold">⌘F</span>
            </div>
        </div>

        <!-- Theme Toggle -->
        <button onclick="expensioToggleTheme()" class="w-[42px] h-[42px] bg-white border border-gray-200 rounded-full flex items-center justify-center hover:bg-gray-50 transition-all shadow-sm group" title="Toggle dark mode">
            <i class='bx bx-moon text-[22px] text-gray-500 group-hover:text-gray-800 transition-colors' id="global-theme-icon"></i>
        </button>

        <!-- Notification Bell -->
        <div class="relative" id="notiWrapper">
            <button id="notiBtn" onclick="toggleNotifDropdown()" class="w-[42px] h-[42px] bg-white border border-gray-200 rounded-full flex items-center justify-center hover:bg-gray-50 transition-all shadow-sm group relative">
                <i class='bx bx-bell text-[22px] text-gray-500 group-hover:text-gray-800 transition-colors'></i>
                <span class="notif-dot absolute top-0 right-0 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white" style="display:none"></span>
            </button>
            <!-- Notification Dropdown -->
            <div id="notiDropdown" class="hidden absolute right-0 top-full mt-2 w-80 bg-white rounded-2xl shadow-2xl border border-gray-100 z-[60] overflow-hidden">
                <div id="notiDropdownContent"></div>
            </div>
        </div>

        <!-- User Avatar + Dropdown -->
        <div class="relative group cursor-pointer ml-2">
            <div class="flex items-center gap-3 transition-all z-10 relative">
                <div class="w-10 h-10 rounded-full overflow-hidden shrink-0 shadow-sm border border-gray-100">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($username ?? 'User'); ?>&background=145d3b&color=fff&size=80" alt="Avatar" class="w-full h-full object-cover">
                </div>
                <!-- Text stacked -->
                <div class="hidden sm:flex flex-col">
                    <span class="text-[14px] font-bold text-gray-900 leading-tight"><?php echo htmlspecialchars($username ?? 'User'); ?></span>
                    <span class="text-[12px] text-gray-500 font-medium">user@example.com</span>
                </div>
            </div>
            <!-- User dropdown -->
            <div class="absolute right-0 top-full mt-2 w-52 bg-white rounded-2xl shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 overflow-hidden">
                <div class="p-2">
                    <div class="px-3 py-2 mb-1">
                        <p class="text-[13px] font-bold text-gray-900"><?php echo htmlspecialchars($username ?? 'User'); ?></p>
                    </div>
                    <div class="border-t border-gray-100 mb-1"></div>
                    <a href="profile.php" class="flex items-center gap-3 px-3 py-2 text-[14px] font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-xl transition-colors"><i class='bx bx-user text-[18px]'></i> My Profile</a>
                    <a href="settings.php" class="flex items-center gap-3 px-3 py-2 text-[14px] font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-xl transition-colors"><i class='bx bx-cog text-[18px]'></i> Settings</a>
                    <div class="border-t border-gray-100 my-1"></div>
                    <a href="../app/Controllers/AuthController.php?action=logout" class="flex items-center gap-3 px-3 py-2 text-[14px] font-medium text-red-500 hover:bg-red-50 rounded-xl transition-colors"><i class='bx bx-log-out text-[18px]'></i> Log out</a>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
    // Sync global theme icon
    (function() {
        const icon = document.getElementById('global-theme-icon');
        if (icon && document.documentElement.classList.contains('dark')) {
            icon.classList.replace('bx-moon', 'bx-sun');
        }
        window.addEventListener('themeChanged', function(e) {
            if (!icon) return;
            if (e.detail.theme === 'dark') {
                icon.classList.replace('bx-moon', 'bx-sun');
            } else {
                icon.classList.replace('bx-sun', 'bx-moon');
            }
        });
    })();

    function toggleNotifDropdown() {
        const dropdown = document.getElementById('notiDropdown');
        const isHidden = dropdown.classList.contains('hidden');
        if (isHidden) {
            dropdown.classList.remove('hidden');
            if (typeof ExpensioNotifications !== 'undefined') {
                ExpensioNotifications.renderDropdown('notiDropdownContent');
            }
        } else {
            dropdown.classList.add('hidden');
        }
    }

    // Close notification dropdown on outside click
    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('notiWrapper');
        const dropdown = document.getElementById('notiDropdown');
        if (wrapper && dropdown && !wrapper.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
</script>
