<?php
$current_page = basename($_SERVER['PHP_SELF']);

function navItem($href, $icon, $label, $current_page) {
    $page = basename($href);
    $isActive = ($current_page === $page)
        || ($page === 'analytics.php' && $current_page === 'index.php');
        
    if ($isActive) {
        return "
            <a href=\"{$href}\" class=\"relative flex items-center gap-3.5 px-4 py-3 rounded-2xl w-full transition-all duration-200 bg-expensio-purple/5 group\">
                <div class=\"absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-expensio-purple rounded-r-full\"></div>
                <i class='bx {$icon} text-[22px] shrink-0 w-6 text-center text-expensio-purple'></i>
                <span class=\"text-[14px] font-bold text-gray-900 tracking-wide truncate\">{$label}</span>
            </a>";
    } else {
        return "
            <a href=\"{$href}\" class=\"flex items-center gap-3.5 px-4 py-3 rounded-2xl w-full transition-all duration-200 text-gray-400 hover:text-gray-800 hover:bg-gray-50 group\">
                <i class='bx {$icon} text-[22px] shrink-0 w-6 text-center group-hover:text-gray-600 transition-colors'></i>
                <span class=\"text-[14px] font-medium tracking-wide truncate\">{$label}</span>
            </a>";
    }
}
?>

<aside class="fixed bottom-0 left-0 w-full h-16 md:relative md:w-[260px] md:min-w-[260px] bg-white dark:bg-[#1a1b1f] md:h-screen flex flex-row md:flex-col items-center justify-around md:items-stretch md:justify-start transition-all duration-300 z-50 border-t border-gray-100 md:border-t-0 md:border-r">
    
    <!-- Logo -->
    <div class="px-7 hidden md:flex items-center gap-3 md:h-[88px] shrink-0">
        <i class='bx bx-target-lock text-expensio-purple text-3xl'></i>
        <span class="text-[22px] font-bold text-gray-900 tracking-tight">Expensio</span>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 w-full flex flex-row md:flex-col items-center justify-around md:justify-start md:px-5 md:pt-4 md:space-y-0 px-2 overflow-y-auto custom-scrollbar">
        
        <!-- Mobile items -->
        <?php $mobilePages = [
            ['dashboard.php','bx-grid-alt','Dashboard'],
            ['wallet.php','bx-wallet','Wallet'],
            ['transactions.php','bx-list-ul','Transactions'],
            ['analytics.php','bx-bar-chart-alt-2','Analytics'],
            ['settings.php','bx-cog','Settings'],
        ];
        foreach ($mobilePages as [$href, $icon, $label]):
            $isActive = (basename($href) === $current_page || ($href === 'analytics.php' && $current_page === 'index.php'));
            $activeClass = $isActive ? 'text-expensio-purple' : 'text-gray-400';
        ?>
            <!-- Mobile: icon only -->
            <a href="<?php echo $href; ?>" class="flex md:hidden flex-col items-center gap-1 flex-1 py-2 <?php echo $activeClass; ?>">
                <i class='bx <?php echo $icon; ?> text-2xl'></i>
                <span class="text-[9px] font-semibold"><?php echo $label; ?></span>
            </a>
        <?php endforeach; ?>

        <!-- Desktop nav items -->
        <div class="hidden md:flex md:flex-col w-full space-y-1.5">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest px-4 mt-2 mb-2">Menu</span>
            
            <?php echo navItem('dashboard.php', 'bx-grid-alt', 'Dashboard', $current_page); ?>
            <?php echo navItem('wallet.php', 'bx-wallet', 'Tasks', $current_page); ?>
            <?php echo navItem('transactions.php', 'bx-calendar', 'Calendar', $current_page); ?>
            <?php echo navItem('analytics.php', 'bx-bar-chart-alt-2', 'Analytics', $current_page); ?>
            
            <div class="pt-6"></div>
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest px-4 mb-2">General</span>
            
            <?php echo navItem('settings.php', 'bx-cog', 'Settings', $current_page); ?>
            <?php echo navItem('profile.php', 'bx-support', 'Help', $current_page); ?>
            
            <div class="pt-6"></div>
            <div class="mx-4 border-t border-gray-100/50 mb-3 block"></div>
            
            <button onclick="expensioToggleTheme()" class="flex items-center gap-3.5 px-4 py-3 rounded-2xl text-gray-400 hover:bg-gray-50/80 hover:text-gray-800 transition-all duration-200 w-full text-left group">
                <i class='bx bx-moon text-[22px] shrink-0 w-6 text-center group-hover:text-yellow-500 transition-colors' id="sidebar-theme-icon"></i>
                <span class="text-[14px] font-medium tracking-wide truncate">Dark mode</span>
            </button>
            <a href="../app/Controllers/AuthController.php?action=logout" class="flex items-center gap-3.5 px-4 py-3 rounded-2xl text-gray-400 hover:bg-red-50/50 hover:text-red-500 transition-all duration-200 group w-full text-left">
                <i class='bx bx-log-out text-[22px] shrink-0 w-6 text-center group-hover:text-red-500 transition-colors'></i>
                <span class="text-[14px] font-medium tracking-wide truncate">Log out</span>
            </a>
            
            <div class="pb-10"></div>
        </div>

    </nav>


    
</aside>

<script>
    (function() {
        const icon = document.getElementById('sidebar-theme-icon');
        if (icon && document.documentElement.classList.contains('dark')) {
            icon.classList.replace('bx-moon', 'bx-sun');
        }
        window.addEventListener('themeChanged', function(e) {
            if (icon) {
                if (e.detail.theme === 'dark') {
                    icon.classList.replace('bx-moon', 'bx-sun');
                } else {
                    icon.classList.replace('bx-sun', 'bx-moon');
                }
            }
        });
    })();
</script>
