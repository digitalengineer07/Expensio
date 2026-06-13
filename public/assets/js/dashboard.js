/* dashboard.js - Chart Logic & Premium Calendar Interaction */

let currentMonth = new Date().getMonth();
let currentYear = new Date().getFullYear();
const today = new Date();

document.addEventListener('DOMContentLoaded', () => {
    initCharts();
    renderCalendar(currentMonth, currentYear);
});

function initCharts() {
    // 1. Budget Balance Doughnut Chart
    const budgetCtx = document.getElementById('budgetChart');
    if (budgetCtx) {
        new Chart(budgetCtx, {
            type: 'doughnut',
            data: {
                labels: ['Spent', 'Remaining'],
                datasets: [{
                    data: [65, 35],
                    backgroundColor: ['#3B82F6', '#E2E8F0'],
                    borderWidth: 0,
                    cutout: '85%'
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                maintainAspectRatio: false,
                responsive: true
            }
        });
    }

    // 2. Spending Activity Bar Chart
    const activityCtx = document.getElementById('activityChart');
    if (activityCtx) {
        new Chart(activityCtx, {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [
                    {
                        label: 'Expenses',
                        data: [420, 310, 580, 240, 610, 390, 480],
                        backgroundColor: '#3B82F6',
                        borderRadius: 8,
                        barThickness: 12
                    },
                    {
                        label: 'Budget',
                        data: [700, 700, 700, 700, 700, 700, 700],
                        backgroundColor: '#F1F5F9',
                        borderRadius: 8,
                        barThickness: 12
                    }
                ]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, border: { display: false } },
                    y: {
                        display: false,
                        beginAtZero: true
                    }
                },
                maintainAspectRatio: false,
                responsive: true
            }
        });
    }
}

// Calendar Logic
function renderCalendar(month, year) {
    const calendarDays = document.getElementById('calendar-days');
    const monthYearLabel = document.getElementById('calendar-month-year');

    if (!calendarDays) return;

    calendarDays.innerHTML = '';
    const date = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0).getDate();
    const startDay = date.getDay(); // 0 (Sun) to 6 (Sat)

    // Adjust for Monday start
    const offset = (startDay === 0) ? 6 : startDay - 1;

    const monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"];

    monthYearLabel.innerText = `${monthNames[month]} ${year}`;

    // Add day labels (Mo, Tu, etc.)
    const labels = ["Mo", "Tu", "We", "Th", "Fr", "Sa", "Su"];
    labels.forEach(label => {
        const div = document.createElement('div');
        div.className = 'calendar-day-label';
        div.innerText = label;
        calendarDays.appendChild(div);
    });

    // Add empty slots
    for (let i = 0; i < offset; i++) {
        const div = document.createElement('div');
        div.className = 'calendar-day empty';
        calendarDays.appendChild(div);
    }

    // Add days
    for (let i = 1; i <= lastDay; i++) {
        const div = document.createElement('div');
        div.className = 'calendar-day';
        div.innerText = i;

        if (i === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
            div.classList.add('today');
            div.classList.add('active'); // Default select today
        }

        div.addEventListener('click', () => {
            document.querySelectorAll('.calendar-day').forEach(d => d.classList.remove('active'));
            div.classList.add('active');
            updateEventsForDate(i, month, year);
        });

        calendarDays.appendChild(div);
    }
}

function changeMonth(delta) {
    currentMonth += delta;
    if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
    } else if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
    }
    renderCalendar(currentMonth, currentYear);
}

function updateEventsForDate(day, month, year) {
    // This would typically fetch from an API
    // For now, let's mock changing the events list
    const list = document.getElementById('upcoming-events-list');
    if (!list) return;

    // Simulate different events for different days
    const mockEvents = [
        { title: `Audit ${day}/${month + 1}`, time: '09:00 AM', icon: 'check-shield' },
        { title: 'Contract Renewal', time: '11:30 AM', icon: 'file-doc' },
        { title: 'Budget Sync', time: '04:00 PM', icon: 'sync' }
    ];

    list.style.opacity = '0';
    setTimeout(() => {
        list.innerHTML = '';
        const dayEvents = mockEvents.filter(() => Math.random() > 0.4); // Randomly show some events

        if (dayEvents.length === 0) {
            list.innerHTML = '<div style="text-align:center; padding: 20px; opacity:0.5; font-size:13px;">No events scheduled for this day</div>';
        } else {
            dayEvents.forEach(event => {
                const item = document.createElement('div');
                item.className = 'upcoming-item';
                item.innerHTML = `
                    <div class="upcoming-icon"><i class='bx bx-${event.icon}'></i></div>
                    <div>
                        <div style="font-size: 14px; font-weight: 600;">${event.title}</div>
                        <div style="font-size: 12px; opacity: 0.5;">${event.time}</div>
                    </div>
                `;
                list.appendChild(item);
            });
        }
        list.style.opacity = '1';
    }, 200);
}
