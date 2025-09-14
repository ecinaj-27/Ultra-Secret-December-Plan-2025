// Passcode Login JavaScript

let currentPasscode = '';
let selectedUser = null;
let users = [];

document.addEventListener('DOMContentLoaded', function() {
    loadUsers();
    initializeKeypad();
    initializeEventListeners();
});

// Load users from database
async function loadUsers() {
    try {
        const response = await fetch('api/get-users.php');
        const data = await response.json();
        
        if (data.success) {
            users = data.users;
            displayUsers();
        } else {
            console.error('Failed to load users:', data.message);
            showUsernameInput();
        }
    } catch (error) {
        console.error('Error loading users:', error);
        showUsernameInput();
    }
}

// Display users in the user list
function displayUsers() {
    const userList = document.getElementById('user-list');
    
    if (users.length === 0) {
        showUsernameInput();
        return;
    }
    
    userList.innerHTML = users.map(user => `
        <div class="user-item" onclick="selectUser(${user.id})">
            <div class="user-avatar">
                <img src="${user.profile_picture || 'assets/images/default-avatar.png'}" alt="${user.name}">
            </div>
            <div class="user-info">
                <h3>${user.name}</h3>
                <p>@${user.username}</p>
            </div>
        </div>
    `).join('');
}

// Select a user
function selectUser(userId) {
    selectedUser = users.find(user => user.id == userId);
    
    if (selectedUser) {
        document.getElementById('selected-username').textContent = selectedUser.name;
        document.getElementById('selected-user-avatar').src = selectedUser.profile_picture || 'assets/images/default-avatar.png';
        document.getElementById('passcode-hint-text').textContent = `Hint: ${selectedUser.password_hint}`;
        
        document.getElementById('username-section').style.display = 'none';
        document.getElementById('passcode-section').style.display = 'block';
        
        // Reset passcode
        currentPasscode = '';
        updatePasscodeDisplay();
    }
}

// Change user
function changeUser() {
    document.getElementById('username-section').style.display = 'block';
    document.getElementById('passcode-section').style.display = 'none';
    currentPasscode = '';
    selectedUser = null;
}

// Show username input
function showUsernameInput() {
    document.getElementById('user-list').style.display = 'none';
    document.getElementById('username-input').style.display = 'block';
    document.getElementById('username-input').focus();
}

// Initialize keypad
function initializeKeypad() {
    console.log('Initializing keypad...');
    const keys = document.querySelectorAll('.key[data-number]');
    const deleteKey = document.getElementById('delete-key');
    
    console.log('Found keys:', keys.length);
    console.log('Delete key:', deleteKey);
    
    keys.forEach((key, index) => {
        key.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Key clicked:', this.getAttribute('data-number'));
            const number = this.getAttribute('data-number');
            addToPasscode(number);
        });
        
        // Add visual feedback
        key.addEventListener('mousedown', function() {
            this.style.transform = 'scale(0.95)';
        });
        
        key.addEventListener('mouseup', function() {
            this.style.transform = 'scale(1)';
        });
        
        key.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
    
    if (deleteKey) {
        deleteKey.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Delete key clicked');
            removeFromPasscode();
        });
    } else {
        console.error('Delete key not found!');
    }
}

// Add number to passcode
function addToPasscode(number) {
    console.log('Adding to passcode:', number, 'Current length:', currentPasscode.length);
    if (currentPasscode.length < 6) {
        currentPasscode += number;
        updatePasscodeDisplay();
        
        // Check if passcode is complete
        if (currentPasscode.length === 6) {
            console.log('Passcode complete, submitting...');
            setTimeout(() => {
                submitPasscode();
            }, 300);
        }
    } else {
        console.log('Passcode already at max length');
    }
}

// Remove number from passcode
function removeFromPasscode() {
    console.log('Removing from passcode. Current length:', currentPasscode.length);
    if (currentPasscode.length > 0) {
        currentPasscode = currentPasscode.slice(0, -1);
        updatePasscodeDisplay();
        console.log('New passcode:', currentPasscode);
    } else {
        console.log('Passcode already empty');
    }
}

// Update passcode display
function updatePasscodeDisplay() {
    const dots = document.querySelectorAll('.dot');
    
    dots.forEach((dot, index) => {
        if (index < currentPasscode.length) {
            dot.classList.add('filled');
        } else {
            dot.classList.remove('filled');
        }
    });
}

// Submit passcode
function submitPasscode() {
    if (!selectedUser) {
        // Handle manual username entry
        const username = document.getElementById('username-input').value;
        if (!username) {
            showError('Please enter a username');
            return;
        }
        selectedUser = { username: username };
    }
    
    // Submit the form
    document.getElementById('form-username').value = selectedUser.username;
    document.getElementById('form-passcode').value = currentPasscode;
    document.getElementById('login-form').submit();
}

// Show error message
function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`;
    
    const passcodeSection = document.getElementById('passcode-section');
    const existingError = passcodeSection.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }
    
    passcodeSection.insertBefore(errorDiv, passcodeSection.querySelector('.passcode-hint'));
    
    // Shake animation
    const dots = document.querySelector('.passcode-dots');
    dots.classList.add('shake');
    setTimeout(() => {
        dots.classList.remove('shake');
    }, 500);
    
    // Clear passcode
    currentPasscode = '';
    updatePasscodeDisplay();
}

// Initialize event listeners
function initializeEventListeners() {
    // Handle manual username input
    const usernameInput = document.getElementById('username-input');
    usernameInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            selectUser({ username: this.value });
        }
    });
    
    // Handle keyboard input for passcode
    document.addEventListener('keydown', function(e) {
        const passcodeSection = document.getElementById('passcode-section');
        if (passcodeSection && passcodeSection.style.display !== 'none') {
            console.log('Key pressed:', e.key);
            if (e.key >= '0' && e.key <= '9') {
                e.preventDefault();
                addToPasscode(e.key);
            } else if (e.key === 'Backspace') {
                e.preventDefault();
                removeFromPasscode();
            } else if (e.key === 'Enter' && currentPasscode.length > 0) {
                e.preventDefault();
                submitPasscode();
            }
        }
    });
    
    // Handle touch events for mobile
    let touchStartY = 0;
    document.addEventListener('touchstart', function(e) {
        touchStartY = e.touches[0].clientY;
    });
    
    document.addEventListener('touchend', function(e) {
        const touchEndY = e.changedTouches[0].clientY;
        const diff = touchStartY - touchEndY;
        
        // Swipe up to show alternative login
        if (diff > 50) {
            document.querySelector('.alternative-login').style.opacity = '1';
        }
    });
}

// Add haptic feedback for mobile
function addHapticFeedback() {
    if ('vibrate' in navigator) {
        navigator.vibrate(50);
    }
}

// Enhanced key press with haptic feedback
document.addEventListener('DOMContentLoaded', function() {
    const keys = document.querySelectorAll('.key');
    keys.forEach(key => {
        key.addEventListener('click', function() {
            addHapticFeedback();
        });
    });
});
