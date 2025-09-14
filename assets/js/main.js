// Ultra Secret December Plan 2025 - Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Mobile navbar toggle
    const navbarToggle = document.getElementById('navbar-toggle');
    const navbarMenu = document.querySelector('.navbar-menu');
    
    if (navbarToggle && navbarMenu) {
        navbarToggle.addEventListener('click', function() {
            navbarMenu.classList.toggle('active');
        });
    }
    
    // Live time update
    updateLiveTime();
    setInterval(updateLiveTime, 1000);
    
    // Initialize calendar
    if (document.getElementById('calendar-widget')) {
        initializeCalendar();
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
    
    // Auto-hide alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
});

// Live time update function
function updateLiveTime() {
    const timeElement = document.getElementById('live-time');
    if (timeElement) {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', {
            hour12: false,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        timeElement.textContent = timeString;
    }
}

// Calendar initialization
let currentYear = new Date().getFullYear();
let currentMonth = new Date().getMonth();

function initializeCalendar() {
    const calendarWidget = document.getElementById('calendar-widget');
    if (!calendarWidget) return;
    
    renderCalendar(currentYear, currentMonth);
}

function renderCalendar(year, month) {
    const calendarWidget = document.getElementById('calendar-widget');
    if (!calendarWidget) return;
    
    const monthNames = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const firstDay = new Date(year, month, 1).getDay();
    const now = new Date();
    const isCurrentMonth = year === now.getFullYear() && month === now.getMonth();
    
    let calendarHTML = `
        <div class="calendar-header">
            <button class="calendar-nav-btn" onclick="changeMonth(-1)">
                <i class="fas fa-chevron-left"></i>
            </button>
            <h4>${monthNames[month]} ${year}</h4>
            <button class="calendar-nav-btn" onclick="changeMonth(1)">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        <div class="calendar-grid">
            <div class="calendar-day-header">Sun</div>
            <div class="calendar-day-header">Mon</div>
            <div class="calendar-day-header">Tue</div>
            <div class="calendar-day-header">Wed</div>
            <div class="calendar-day-header">Thu</div>
            <div class="calendar-day-header">Fri</div>
            <div class="calendar-day-header">Sat</div>
    `;
    
    // Add empty cells for days before the first day of the month
    for (let i = 0; i < firstDay; i++) {
        calendarHTML += '<div class="calendar-day empty"></div>';
    }
    
    // Add days of the month
    for (let day = 1; day <= daysInMonth; day++) {
        const isToday = isCurrentMonth && day === now.getDate();
        const dayClass = isToday ? 'calendar-day today' : 'calendar-day';
        const dateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        calendarHTML += `<div class="${dayClass}" data-date="${dateString}" onclick="selectDate('${dateString}')">
            <span class="day-number">${day}</span>
            <div class="day-todos" id="todos-${dateString}"></div>
        </div>`;
    }
    
    calendarHTML += '</div>';
    calendarHTML += `
        <div class="calendar-expanded" id="calendar-expanded" style="display: none;">
            <div class="calendar-todo-section">
                <h5>To-Do Items for <span id="selected-date"></span></h5>
                <div class="calendar-todo-list" id="calendar-todo-list">
                    <p class="loading">Loading...</p>
                </div>
            </div>
        </div>
    `;
    
    calendarWidget.innerHTML = calendarHTML;
    
    // Load todos for all days in the current month
    loadTodosForMonth(year, month);
}

function changeMonth(direction) {
    currentMonth += direction;
    if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
    } else if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
    }
    renderCalendar(currentYear, currentMonth);
}

function loadTodosForMonth(year, month) {
    const startDate = `${year}-${String(month + 1).padStart(2, '0')}-01`;
    const endDate = `${year}-${String(month + 1).padStart(2, '0')}-${new Date(year, month + 1, 0).getDate()}`;
    
    fetch(`api/get-todos.php?start_date=${startDate}&end_date=${endDate}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear existing todos
                document.querySelectorAll('.day-todos').forEach(day => {
                    day.innerHTML = '';
                });
                
                // Add todos to calendar days
                data.todos.forEach(todo => {
                    const dayElement = document.getElementById(`todos-${todo.due_date}`);
                    if (dayElement) {
                        const todoDot = document.createElement('div');
                        todoDot.className = `todo-dot ${todo.category.toLowerCase()}`;
                        todoDot.title = todo.title;
                        dayElement.appendChild(todoDot);
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error loading todos for month:', error);
        });
}

// Toggle calendar expansion
function toggleCalendarExpansion() {
    const expanded = document.getElementById('calendar-expanded');
    const btn = document.querySelector('.calendar-expand-btn i');
    
    if (expanded.style.display === 'none') {
        expanded.style.display = 'block';
        btn.className = 'fas fa-compress-arrows-alt';
    } else {
        expanded.style.display = 'none';
        btn.className = 'fas fa-expand-arrows-alt';
    }
}

// Select date and load todos
function selectDate(dateString) {
    const selectedDate = new Date(dateString);
    const dateDisplay = selectedDate.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    document.getElementById('selected-date').textContent = dateDisplay;
    
    // Show expanded section
    const expanded = document.getElementById('calendar-expanded');
    expanded.style.display = 'block';
    
    // Load todos for selected date
    loadTodosForDate(dateString);
}

// Load todos for selected date
function loadTodosForDate(dateString) {
    const todoList = document.getElementById('calendar-todo-list');
    todoList.innerHTML = '<p class="loading">Loading...</p>';
    
    // Make AJAX request to get todos for the date
    fetch(`api/get-todos.php?date=${dateString}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.todos.length === 0) {
                    todoList.innerHTML = '<p class="no-todos">No tasks for this date</p>';
                } else {
                    let todosHTML = '';
                    data.todos.forEach(todo => {
                        todosHTML += `
                            <div class="calendar-todo-item ${todo.is_completed ? 'completed' : ''}">
                                <div class="todo-content">
                                    <h6>${todo.title}</h6>
                                    ${todo.description ? `<p>${todo.description}</p>` : ''}
                                    <span class="todo-category">${todo.category}</span>
                                </div>
                            </div>
                        `;
                    });
                    todoList.innerHTML = todosHTML;
                }
            } else {
                todoList.innerHTML = '<p class="error">Error loading todos</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            todoList.innerHTML = '<p class="error">Error loading todos</p>';
        });
}

// Form validation
function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        } else {
            clearFieldError(field);
        }
    });
    
    // Passcode confirmation validation
    const passcode = form.querySelector('input[name="passcode"]');
    const confirmPasscode = form.querySelector('input[name="confirm_passcode"]');
    
    if (passcode && confirmPasscode) {
        if (passcode.value !== confirmPasscode.value) {
            showFieldError(confirmPasscode, 'Passcodes do not match');
            isValid = false;
        }
    }
    
    // Email validation
    const email = form.querySelector('input[type="email"]');
    if (email && email.value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email.value)) {
            showFieldError(email, 'Please enter a valid email address');
            isValid = false;
        }
    }
    
    return isValid;
}

// Show field error
function showFieldError(field, message) {
    clearFieldError(field);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    errorDiv.style.color = '#c33';
    errorDiv.style.fontSize = '0.9rem';
    errorDiv.style.marginTop = '0.25rem';
    
    field.parentNode.appendChild(errorDiv);
    field.style.borderColor = '#c33';
}

// Clear field error
function clearFieldError(field) {
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    field.style.borderColor = '#e1e5e9';
}

// Utility function to show notifications
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#4caf50' : type === 'error' ? '#f44336' : '#2196f3'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 5000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background: #e1e5e9;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .calendar-day-header {
        background: #f8f9fa;
        padding: 0.5rem;
        text-align: center;
        font-weight: 600;
        font-size: 0.9rem;
        color: #666;
    }
    
    .calendar-day {
        background: white;
        padding: 0.5rem;
        text-align: center;
        cursor: pointer;
        transition: background 0.3s ease;
        min-height: 60px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        position: relative;
    }
    
    .day-number {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    
    .day-todos {
        display: flex;
        flex-wrap: wrap;
        gap: 2px;
        justify-content: center;
        margin-top: 0.25rem;
    }
    
    .todo-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
    }
    
    .todo-dot.lab {
        background: #ff6b6b;
    }
    
    .todo-dot.school {
        background: #4ecdc4;
    }
    
    .todo-dot.personal {
        background: #45b7d1;
    }
    
    .todo-dot.relationship {
        background: #f9ca24;
    }
    
    .calendar-day:hover {
        background: #f8f9fa;
    }
    
    .calendar-day.today {
        background: #ff6b6b;
        color: white;
        font-weight: 600;
    }
    
    .calendar-day.empty {
        background: #f8f9fa;
        cursor: default;
    }
    
    .calendar-header {
        text-align: center;
        margin-bottom: 1rem;
    }
    
    .calendar-header h4 {
        color: #333;
        font-size: 1.2rem;
    }
    
    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .calendar-nav-btn {
        background: #ff6b6b;
        color: white;
        border: none;
        padding: 0.5rem;
        border-radius: 50%;
        cursor: pointer;
        transition: background 0.3s ease;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .calendar-nav-btn:hover {
        background: #ff5252;
    }
    
    .calendar-expand-btn {
        background: #ff6b6b;
        color: white;
        border: none;
        padding: 0.5rem;
        border-radius: 50%;
        cursor: pointer;
        transition: background 0.3s ease;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .calendar-expand-btn:hover {
        background: #ff5252;
    }
    
    .calendar-expanded {
        margin-top: 1rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e1e5e9;
    }
    
    .calendar-todo-section h5 {
        color: #333;
        margin-bottom: 1rem;
        font-size: 1.1rem;
    }
    
    .calendar-todo-list {
        max-height: 300px;
        overflow-y: auto;
    }
    
    .calendar-todo-item {
        background: white;
        padding: 1rem;
        margin-bottom: 0.5rem;
        border-radius: 6px;
        border-left: 4px solid #ff6b6b;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .calendar-todo-item.completed {
        opacity: 0.6;
        border-left-color: #4caf50;
    }
    
    .calendar-todo-item h6 {
        margin: 0 0 0.5rem 0;
        color: #333;
        font-size: 1rem;
    }
    
    .calendar-todo-item p {
        margin: 0 0 0.5rem 0;
        color: #666;
        font-size: 0.9rem;
    }
    
    .calendar-todo-item .todo-category {
        background: #ff6b6b;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .loading, .no-todos, .error {
        text-align: center;
        padding: 2rem;
        color: #666;
        font-style: italic;
    }
    
    .error {
        color: #c33;
    }
    
    /* Admin Content Editing Styles */
    .admin-content-section {
        background: #f8f9fa;
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        border: 1px solid #e1e5e9;
    }
    
    .content-editing-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 2rem;
    }
    
    .tab-btn {
        background: #e1e5e9;
        color: #666;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px 8px 0 0;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
    }
    
    .tab-btn.active {
        background: #ff6b6b;
        color: white;
    }
    
    .tab-btn:hover:not(.active) {
        background: #d1d5db;
    }
    
    .content-tab {
        display: none;
    }
    
    .content-tab.active {
        display: block;
    }
    
    .content-form {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #333;
    }
    
    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #e1e5e9;
        border-radius: 6px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }
    
    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #ff6b6b;
        box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
    }
    
    .form-group textarea {
        resize: vertical;
        min-height: 200px;
    }
    
    .alert {
        padding: 1rem;
        border-radius: 6px;
        margin-bottom: 1rem;
        font-weight: 500;
    }
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    /* Large card styles */
    .large-card {
        grid-column: span 2;
        grid-row: span 2;
    }
    
    @media (max-width: 768px) {
        .large-card {
            grid-column: span 1;
            grid-row: span 1;
        }
    }
`;
document.head.appendChild(style);
