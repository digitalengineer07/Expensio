<!-- Global Expensio Theme Head — Included in every page -->
<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
<!-- Boxicons -->
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<!-- Global Notifications -->
<script src="assets/js/notifications.js" defer></script>

<script>
    // Apply theme IMMEDIATELY to prevent flash (runs before body renders)
    (function() {
        const saved = localStorage.getItem('theme');
        if (saved === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    })();

    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                fontFamily: {
                    sans: ['Inter', 'sans-serif'],
                    display: ['Poppins', 'sans-serif'],
                },
                colors: {
                    'expensio-dark': '#1F2024',
                    'expensio-purple': '#145d3b', /* Primary green from reference */
                    'finset-bg': '#F4F5F7',
                }
            }
        }
    }
</script>

<style>
    /* ===== GLOBAL EXPENSIO DESIGN SYSTEM ===== */

    /* Glass Panel */
    .glass-panel {
        background: #ffffff;
        border: 1px solid #F3F4F6;
        border-radius: 24px;
        box-shadow: 0 4px 20px -4px rgba(0,0,0,0.03);
    }
    .dark .glass-panel {
        background: #1F2024;
        border-color: rgba(255, 255, 255, 0.08);
    }

    /* Background */
    .bg-expensio {
        background-color: #F4F5F7;
    }
    .dark .bg-expensio {
        background-color: #121317;
    }

    /* Global Dark Mode Text/BG Overrides */
    .dark .text-gray-900 { color: #F3F4F6 !important; }
    .dark .text-gray-800 { color: #E5E7EB !important; }
    .dark .text-gray-700 { color: #D1D5DB !important; }
    .dark .text-gray-600 { color: #9CA3AF !important; }
    .dark .text-gray-500 { color: #6B7280 !important; }
    .dark .text-gray-400 { color: #9CA3AF !important; }
    .dark .bg-white { background-color: #1F2024 !important; }
    .dark .bg-white\/50 { background-color: rgba(31, 32, 36, 0.5) !important; }
    .dark .bg-white\/40 { background-color: rgba(31, 32, 36, 0.4) !important; }
    .dark .bg-white\/80 { background-color: rgba(31, 32, 36, 0.8) !important; }
    .dark .bg-gray-50 { background-color: #1a1b1f !important; }
    .dark .bg-gray-100 { background-color: #1a1b1f !important; }
    .dark .border-gray-100 { border-color: rgba(255,255,255,0.06) !important; }
    .dark .border-gray-200 { border-color: rgba(255,255,255,0.08) !important; }
    .dark .border-gray-300 { border-color: rgba(255,255,255,0.1) !important; }
    .dark .border-white { border-color: rgba(255, 255, 255, 0.08) !important; }
    .dark .border-white\/60 { border-color: rgba(255, 255, 255, 0.04) !important; }
    .dark .shadow-sm { box-shadow: 0 1px 2px rgba(0,0,0,0.3) !important; }
    .dark .hover\:bg-gray-50:hover { background-color: rgba(255,255,255,0.05) !important; }
    .dark .divide-gray-100 > :not([hidden]) ~ :not([hidden]) { border-color: rgba(255,255,255,0.06) !important; }

    /* Sidebar */
    .glass-sidebar {
        background: #ffffff;
        border-right: 1px solid #F3F4F6;
    }
    .dark .glass-sidebar {
        background: #1a1b1f;
        border-right-color: rgba(255,255,255,0.05);
        border-top-color: rgba(255,255,255,0.05);
    }

    /* Nav Active */
    .nav-item-active {
        background: #7C3AED;
        color: #ffffff !important;
        border-radius: 9999px;
    }
    .nav-item-active * {
        color: #ffffff !important;
    }

    /* Scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(0,0,0,0.1);
        border-radius: 10px;
    }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.1);
    }

    /* Animation */
    @keyframes slideIn {
        from { transform: translateX(-20px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    .animate-fade {
        animation: fadeIn 0.6s ease-out forwards;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Notification Badge */
    .noti-badge {
        position: absolute;
        top: 0; right: 0;
        width: 10px; height: 10px;
        background: #EF4444;
        border-radius: 50%;
        border: 2px solid white;
    }
    .dark .noti-badge {
        border-color: #1F2024;
    }

    /* Toast */
    .toast-show {
        transform: translateY(0) !important;
        opacity: 1 !important;
    }

    /* Global Theme Toggle Function */
</style>

<script>
    // Global theme functions available on every page
    function expensioToggleTheme() {
        const html = document.documentElement;
        if (html.classList.contains('dark')) {
            html.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        } else {
            html.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        }
        // Dispatch event for any listeners (e.g. settings toggles)
        window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: localStorage.getItem('theme') } }));
    }

    function expensioGetTheme() {
        return localStorage.getItem('theme') || 'light';
    }
</script>
