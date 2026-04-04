<?php
// Get page title from caller or default
$page_title = $page_title ?? 'Expensio';
$page_subtitle = $page_subtitle ?? '';
?>
<!-- Premium Glass Header -->
<header class="sticky top-0 z-50 px-8 lg:px-10 py-5 lg:py-0 lg:min-h-[96px] flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white/70 dark:bg-[#121317]/70 backdrop-blur-xl border-b border-gray-100/50 dark:border-white/5 transition-all w-full shadow-[0_8px_30px_-12px_rgba(0,0,0,0.04)]">
    
    <!-- Title Area (Left) -->
    <div class="flex flex-col justify-center flex-1 min-w-0 mr-4">
        <h1 class="text-[28px] font-black text-gray-900 leading-tight tracking-tight drop-shadow-sm font-display truncate"><?php echo htmlspecialchars($page_title); ?></h1>
        <?php if ($page_subtitle): ?>
        <p class="text-gray-500 text-[13px] font-semibold mt-1 tracking-wide truncate"><?php echo $page_subtitle; ?></p>
        <?php endif; ?>
    </div>

    <!-- Actions Area (Right) -->
    <div class="flex items-center gap-4 lg:gap-5 flex-shrink-0 w-full lg:w-auto mt-1 lg:mt-0">
        
        <!-- Premium Search Bar -->
        <div class="relative hidden lg:flex items-center bg-gray-50/50 hover:bg-white dark:bg-white/5 dark:hover:bg-white/10 border border-gray-200/60 hover:border-expensio-purple/30 rounded-full px-5 py-2.5 transition-all shadow-sm focus-within:ring-4 focus-within:ring-expensio-purple/10 focus-within:bg-white focus-within:border-expensio-purple group">
            <i class='bx bx-search text-gray-400 group-focus-within:text-expensio-purple text-[20px] transition-colors'></i>
            <input type="text" placeholder="Search transactions, budgets..." class="bg-transparent border-none outline-none text-[13px] font-bold w-52 placeholder-gray-400 text-gray-700 ml-3 transition-all">
            <div class="bg-white border border-gray-200 shadow-sm rounded-md flex items-center justify-center px-1.5 py-0.5 ml-3 opacity-70 group-hover:opacity-100 transition-opacity">
                <span class="text-[10px] text-gray-500 font-extrabold tracking-widest">⌘F</span>
            </div>
        </div>

        <!-- Theme Toggle -->
        <button onclick="expensioToggleTheme()" class="w-[44px] h-[44px] bg-white border border-gray-200/60 rounded-full flex items-center justify-center hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm hover:shadow-md hover:-translate-y-0.5 group" title="Toggle dark mode">
            <i class='bx bx-moon text-[22px] text-gray-500 group-hover:text-yellow-500 transition-colors duration-300' id="global-theme-icon"></i>
        </button>

        <!-- Notification Bell -->
        <div class="relative" id="notiWrapper">
            <button id="notiBtn" onclick="toggleNotifDropdown()" class="w-[44px] h-[44px] bg-white border border-gray-200/60 rounded-full flex items-center justify-center hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm hover:shadow-md hover:-translate-y-0.5 group relative">
                <i class='bx bx-bell text-[22px] text-gray-500 group-hover:text-expensio-purple transition-colors duration-300'></i>
                <span class="notif-dot absolute -top-1 -right-1 w-3.5 h-3.5 bg-red-500 rounded-full border-[3px] border-white shadow-sm" style="display:none"></span>
            </button>
            <!-- Notification Dropdown -->
            <div id="notiDropdown" class="hidden absolute right-0 top-full mt-2 w-80 bg-white rounded-2xl shadow-2xl border border-gray-100 z-[60] overflow-hidden">
                <div id="notiDropdownContent"></div>
            </div>
        </div>

        <!-- User Avatar & Profile Dropdown -->
        <div class="relative group cursor-pointer ml-4 border-l border-gray-200/60 pl-6 h-10 flex items-center">
            <div class="flex items-center gap-3.5 transition-all z-10 relative">
                <div class="w-11 h-11 rounded-full overflow-hidden shrink-0 shadow-md border-2 border-white hover:border-expensio-purple transition-colors duration-300">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($username ?? 'User'); ?>&background=145d3b&color=fff&size=80&bold=true" alt="Avatar" class="w-full h-full object-cover">
                </div>
                <!-- Text stacked -->
                <div class="hidden sm:flex flex-col mt-0.5">
                    <span class="text-[14px] font-black text-gray-900 leading-none"><?php echo htmlspecialchars($username ?? 'User'); ?></span>
                    <span class="text-[11px] font-bold text-gray-400 tracking-wide uppercase mt-1">Administrator</span>
                </div>
                <i class='bx bx-chevron-down text-gray-400 group-hover:text-expensio-purple transition-colors ml-1'></i>
            </div>
            <!-- Intelligent Dropdown -->
            <div class="absolute right-0 top-[110%] w-60 bg-white/95 backdrop-blur-md rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] border border-gray-100/50 opacity-0 invisible group-hover:opacity-100 group-hover:visible group-hover:top-full transition-all duration-300 z-50 overflow-hidden transform origin-top">
                <div class="p-2">
                    <div class="px-4 py-3 mb-1 bg-gray-50/50 rounded-xl mx-1 mt-1">
                        <p class="text-[13px] font-black text-gray-900"><?php echo htmlspecialchars($username ?? 'User'); ?></p>
                        <p class="text-[11px] font-semibold text-gray-500 mt-0.5">user@example.com</p>
                    </div>
                    <div class="border-t border-gray-100 my-1"></div>
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
