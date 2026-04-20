// Student Event Management System - Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
    
    // Form Validation Utilities
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phonePattern = /^[\d\-\+\(\)\s]+$/;
    
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize event filters
    initializeEventFilters();
    
    // Initialize AJAX registration
    initializeAjaxRegistration();
    
    // Initialize tooltips
    initializeTooltips();
});

// Form Validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', () => validateInput(input));
            input.addEventListener('input', () => clearError(input));
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const requiredInputs = form.querySelectorAll('[required]');
    
    requiredInputs.forEach(input => {
        if (!validateInput(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateInput(input) {
    const value = input.value.trim();
    let isValid = true;
    let errorMessage = '';
    
    // Check if required field is empty
    if (input.hasAttribute('required') && !value) {
        errorMessage = 'This field is required';
        isValid = false;
    }
    // Email validation
    else if (input.type === 'email' && value && !emailPattern.test(value)) {
        errorMessage = 'Please enter a valid email address';
        isValid = false;
    }
    // Phone validation
    else if (input.type === 'tel' && value && !phonePattern.test(value)) {
        errorMessage = 'Please enter a valid phone number';
        isValid = false;
    }
    // Password confirmation
    else if (input.name === 'confirm_password') {
        const passwordInput = document.querySelector('input[name="password"]');
        if (passwordInput && value !== passwordInput.value) {
            errorMessage = 'Passwords do not match';
            isValid = false;
        }
    }
    // Minimum length
    else if (input.hasAttribute('minlength') && value.length < input.getAttribute('minlength')) {
        errorMessage = `Minimum ${input.getAttribute('minlength')} characters required`;
        isValid = false;
    }
    
    if (isValid) {
        showSuccess(input);
    } else {
        showError(input, errorMessage);
    }
    
    return isValid;
}

function showError(input, message) {
    input.classList.remove('is-valid');
    input.classList.add('is-invalid');
    
    let feedback = input.parentNode.querySelector('.invalid-feedback');
    if (feedback) {
        feedback.textContent = message;
    }
}

function showSuccess(input) {
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
}

function clearError(input) {
    input.classList.remove('is-invalid', 'is-valid');
}

// Event Filters
function initializeEventFilters() {
    const searchInput = document.getElementById('eventSearch');
    const dateFilter = document.getElementById('dateFilter');
    const organizerFilter = document.getElementById('organizerFilter');
    
    if (searchInput) {
        searchInput.addEventListener('input', debounce(filterEvents, 300));
    }
    
    if (dateFilter) {
        dateFilter.addEventListener('change', filterEvents);
    }
    
    if (organizerFilter) {
        organizerFilter.addEventListener('change', filterEvents);
    }
}

function filterEvents() {
    const searchTerm = document.getElementById('eventSearch')?.value.toLowerCase() || '';
    const dateFilter = document.getElementById('dateFilter')?.value || '';
    const organizerFilter = document.getElementById('organizerFilter')?.value || '';
    
    const eventCards = document.querySelectorAll('.event-card');
    
    eventCards.forEach(card => {
        const title = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
        const description = card.querySelector('.card-text')?.textContent.toLowerCase() || '';
        const date = card.dataset.date || '';
        const organizer = card.dataset.organizer || '';
        
        let show = true;
        
        // Text search
        if (searchTerm && !title.includes(searchTerm) && !description.includes(searchTerm)) {
            show = false;
        }
        
        // Date filter
        if (dateFilter && date !== dateFilter) {
            show = false;
        }
        
        // Organizer filter
        if (organizerFilter && organizer !== organizerFilter) {
            show = false;
        }
        
        card.closest('.col-md-4').style.display = show ? 'block' : 'none';
    });
}

// AJAX Event Registration
function initializeAjaxRegistration() {
    const registerButtons = document.querySelectorAll('.register-btn');
    
    registerButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            registerForEvent(this);
        });
    });
}

function registerForEvent(button) {
    const eventId = button.dataset.eventId;
    const originalText = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Registering...';
    button.disabled = true;
    
    fetch('ajax/register_event.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            event_id: eventId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.innerHTML = 'Registered';
            button.classList.remove('btn-primary');
            button.classList.add('btn-success');
            showToast('Successfully registered for the event!', 'success');
            
            // Update registration count
            updateRegistrationCount(eventId, data.new_count);
        } else {
            button.innerHTML = originalText;
            button.disabled = false;
            showToast(data.message || 'Registration failed. Please try again.', 'danger');
        }
    })
    .catch(error => {
        button.innerHTML = originalText;
        button.disabled = false;
        showToast('Network error. Please try again.', 'danger');
    });
}

function updateRegistrationCount(eventId, newCount) {
    const countElements = document.querySelectorAll(`[data-event-id="${eventId}"] .registration-count`);
    countElements.forEach(element => {
        element.textContent = newCount;
    });
}

// Toast Notifications
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remove toast after it's hidden
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    document.body.appendChild(container);
    return container;
}

// Initialize Tooltips
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Utility Functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Loading State Management
function showLoading(element) {
    const originalText = element.innerHTML;
    element.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Loading...';
    element.disabled = true;
    return originalText;
}

function hideLoading(element, originalText) {
    element.innerHTML = originalText;
    element.disabled = false;
}

// Confirmation Dialogs
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Auto-hide alerts
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert:not(.alert-persistent)');
    alerts.forEach(alert => {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    });
}, 5000);

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

// Add fade-in animation to cards
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('fade-in');
        }
    });
}, observerOptions);

document.querySelectorAll('.card').forEach(card => {
    observer.observe(card);
});