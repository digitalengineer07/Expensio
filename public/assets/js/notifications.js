/**
 * Expensio Global Notification System
 * Handles in-app notifications with badge count, mark-as-read, and dismiss functionality.
 * Stored in localStorage for persistence across pages.
 */

const ExpensioNotifications = (function() {
    const STORAGE_KEY = 'expensio_notifications';

    const defaultNotifications = [
        {
            id: 1,
            type: 'warning',
            icon: 'bx-trending-down',
            iconBg: 'bg-red-100',
            iconColor: 'text-red-500',
            title: 'Expense limit reached',
            message: 'You have spent 90% of your Food budget.',
            time: 'Just now',
            read: false
        },
        {
            id: 2,
            type: 'income',
            icon: 'bx-wallet-alt',
            iconBg: 'bg-green-100',
            iconColor: 'text-green-500',
            title: 'Income Added',
            message: '₹5,000 has been credited to your wallet.',
            time: '2 hours ago',
            read: false
        },
        {
            id: 3,
            type: 'reminder',
            icon: 'bx-bell',
            iconBg: 'bg-blue-100',
            iconColor: 'text-blue-500',
            title: 'Monthly Report Ready',
            message: 'Your March financial report is ready to download.',
            time: 'Yesterday',
            read: true
        },
        {
            id: 4,
            type: 'settlement',
            icon: 'bx-group',
            iconBg: 'bg-purple-100',
            iconColor: 'text-purple-500',
            title: 'Group Settlement Due',
            message: 'You owe ₹500 to Rahul in "Goa Trip" group.',
            time: '2 days ago',
            read: true
        }
    ];

    function _load() {
        try {
            const stored = localStorage.getItem(STORAGE_KEY);
            return stored ? JSON.parse(stored) : defaultNotifications;
        } catch (e) {
            return defaultNotifications;
        }
    }

    function _save(notifs) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(notifs));
    }

    function getAll() {
        return _load();
    }

    function getUnreadCount() {
        return _load().filter(n => !n.read).length;
    }

    function markAsRead(id) {
        const notifs = _load();
        const idx = notifs.findIndex(n => n.id === id);
        if (idx !== -1) { notifs[idx].read = true; _save(notifs); }
        _refreshAllBadges();
    }

    function markAllRead() {
        const notifs = _load().map(n => ({ ...n, read: true }));
        _save(notifs);
        _refreshAllBadges();
    }

    function remove(id) {
        const notifs = _load().filter(n => n.id !== id);
        _save(notifs);
    }

    function add(notification) {
        const notifs = _load();
        const newNotif = {
            id: Date.now(),
            read: false,
            time: 'Just now',
            ...notification
        };
        notifs.unshift(newNotif);
        _save(notifs);
        _refreshAllBadges();
    }

    function _refreshAllBadges() {
        const count = getUnreadCount();
        document.querySelectorAll('.notif-badge-count').forEach(el => {
            el.textContent = count;
            el.style.display = count > 0 ? 'flex' : 'none';
        });
        document.querySelectorAll('.notif-dot').forEach(el => {
            el.style.display = count > 0 ? 'block' : 'none';
        });
    }

    function renderDropdown(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const notifs = _load();
        const unread = notifs.filter(n => !n.read).length;

        container.innerHTML = `
            <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-gray-900 text-sm">Notifications</h3>
                    ${unread > 0 ? `<p class="text-[10px] text-gray-400 font-medium mt-0.5">${unread} unread</p>` : ''}
                </div>
                ${unread > 0 ? `<button onclick="ExpensioNotifications.markAllRead(); ExpensioNotifications.renderDropdown('${containerId}')" class="text-[11px] font-semibold text-expensio-purple hover:underline outline-none">Mark all read</button>` : ''}
            </div>
            <div class="max-h-72 overflow-y-auto custom-scrollbar">
                ${notifs.length === 0 ? `
                    <div class="p-8 text-center">
                        <i class='bx bx-bell-off text-3xl text-gray-300'></i>
                        <p class="text-xs text-gray-400 mt-2 font-medium">No notifications</p>
                    </div>
                ` : notifs.map(n => `
                    <div onclick="ExpensioNotifications.markAsRead(${n.id}); ExpensioNotifications.renderDropdown('${containerId}')"
                         class="group p-4 border-b border-gray-50 hover:bg-gray-50 transition-colors cursor-pointer flex gap-3 relative ${!n.read ? 'bg-purple-50/40' : ''}">
                        ${!n.read ? '<span class="absolute left-1 top-1/2 -translate-y-1/2 w-1.5 h-1.5 bg-expensio-purple rounded-full"></span>' : ''}
                        <div class="w-8 h-8 rounded-full ${n.iconBg} ${n.iconColor} flex items-center justify-center shrink-0">
                            <i class='bx ${n.icon} text-sm'></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 leading-tight truncate">${n.title}</p>
                            <p class="text-[11px] text-gray-500 mt-0.5 leading-relaxed">${n.message}</p>
                            <p class="text-[10px] text-gray-400 mt-1 font-medium">${n.time}</p>
                        </div>
                        <button onclick="event.stopPropagation(); ExpensioNotifications.remove(${n.id}); ExpensioNotifications.renderDropdown('${containerId}')"
                                class="opacity-0 group-hover:opacity-100 transition-opacity text-gray-300 hover:text-red-400 text-xs shrink-0">
                            <i class='bx bx-x text-lg'></i>
                        </button>
                    </div>
                `).join('')}
            </div>
            <div class="p-3 border-t border-gray-100 text-center">
                <a href="transactions.php" class="text-xs font-bold text-gray-500 hover:text-expensio-purple transition-colors">View all activity →</a>
            </div>
        `;
    }

    // Initialize on DOM ready
    function init() {
        _refreshAllBadges();
    }

    return { getAll, getUnreadCount, markAsRead, markAllRead, remove, add, renderDropdown, init };
})();

// Auto-init when script loads
document.addEventListener('DOMContentLoaded', function() {
    ExpensioNotifications.init();
});
