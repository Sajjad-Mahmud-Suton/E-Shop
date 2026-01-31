/**
 * Admin Panel JavaScript
 * Modern E-Commerce Platform
 */

// Initialize DataTables with custom config
function initDataTable(tableId, options = {}) {
    const defaultOptions = {
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        language: {
            search: "",
            searchPlaceholder: "Search...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            paginate: {
                first: '<i class="fas fa-angle-double-left"></i>',
                last: '<i class="fas fa-angle-double-right"></i>',
                next: '<i class="fas fa-angle-right"></i>',
                previous: '<i class="fas fa-angle-left"></i>'
            }
        },
        dom: '<"flex flex-wrap items-center justify-between gap-4 mb-4"<"flex items-center gap-2"l><"flex items-center gap-2"f>>rt<"flex flex-wrap items-center justify-between gap-4 mt-4"<"text-sm text-gray-500"i><"flex gap-1"p>>',
        drawCallback: function() {
            // Add Tailwind classes to pagination
            $('.dataTables_paginate .paginate_button').addClass('px-3 py-1 rounded-lg text-sm');
            $('.dataTables_paginate .paginate_button.current').addClass('bg-primary-500 text-white');
        }
    };

    return $(tableId).DataTable({ ...defaultOptions, ...options });
}

// Sidebar toggle
function toggleSidebar() {
    const sidebar = document.getElementById('admin-sidebar');
    const main = document.getElementById('admin-main');
    
    if (sidebar && main) {
        sidebar.classList.toggle('collapsed');
        main.classList.toggle('expanded');
        
        // Save state to localStorage
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    }
}

// Mobile sidebar toggle
function toggleMobileSidebar() {
    const sidebar = document.getElementById('admin-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    
    if (sidebar) {
        sidebar.classList.toggle('open');
    }
    
    if (overlay) {
        overlay.classList.toggle('hidden');
    }
}

// Initialize sidebar state
function initSidebar() {
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    const sidebar = document.getElementById('admin-sidebar');
    const main = document.getElementById('admin-main');
    
    if (isCollapsed && sidebar && main) {
        sidebar.classList.add('collapsed');
        main.classList.add('expanded');
    }
}

// Notification dropdown toggle
function toggleNotifications() {
    const dropdown = document.getElementById('notifications-dropdown');
    if (dropdown) {
        dropdown.classList.toggle('active');
    }
}

// User menu dropdown toggle
function toggleUserMenu() {
    const dropdown = document.getElementById('user-menu-dropdown');
    if (dropdown) {
        dropdown.classList.toggle('active');
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    const notifDropdown = document.getElementById('notifications-dropdown');
    const userDropdown = document.getElementById('user-menu-dropdown');
    
    if (notifDropdown && !e.target.closest('#notifications-btn') && !e.target.closest('#notifications-dropdown')) {
        notifDropdown.classList.remove('active');
    }
    
    if (userDropdown && !e.target.closest('#user-menu-btn') && !e.target.closest('#user-menu-dropdown')) {
        userDropdown.classList.remove('active');
    }
});

// Modal functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Close modal on overlay click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('admin-modal')) {
        e.target.classList.remove('active');
        document.body.style.overflow = '';
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.admin-modal.active');
        modals.forEach(modal => {
            modal.classList.remove('active');
        });
        document.body.style.overflow = '';
    }
});

// Toast notification
function showAdminToast(message, type = 'info') {
    let container = document.getElementById('admin-toast-container');
    
    if (!container) {
        container = document.createElement('div');
        container.id = 'admin-toast-container';
        container.className = 'fixed bottom-4 right-4 z-50 flex flex-col gap-2';
        document.body.appendChild(container);
    }
    
    const bgColor = type === 'success' ? 'bg-green-500' :
                    type === 'error' ? 'bg-red-500' :
                    type === 'warning' ? 'bg-amber-500' :
                    'bg-gray-800';
    
    const icon = type === 'success' ? 'check-circle' :
                 type === 'error' ? 'times-circle' :
                 type === 'warning' ? 'exclamation-triangle' :
                 'info-circle';
    
    const toast = document.createElement('div');
    toast.className = `px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 text-white transform translate-x-full transition-transform duration-300 ${bgColor}`;
    toast.innerHTML = `
        <i class="fas fa-${icon} text-xl"></i>
        <span class="font-medium">${message}</span>
        <button onclick="this.parentElement.remove()" class="ml-4 opacity-70 hover:opacity-100">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    container.appendChild(toast);
    setTimeout(() => toast.classList.remove('translate-x-full'), 10);
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

// Confirm delete dialog
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return new Promise((resolve) => {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 shadow-2xl transform scale-95 transition-transform duration-200">
                <div class="text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Confirm Delete</h3>
                    <p class="text-gray-600 mb-6">${message}</p>
                    <div class="flex gap-3 justify-center">
                        <button id="cancel-delete" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition-colors">
                            Cancel
                        </button>
                        <button id="confirm-delete" class="px-6 py-2 bg-red-500 text-white rounded-lg font-medium hover:bg-red-600 transition-colors">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Animate in
        setTimeout(() => {
            modal.querySelector('.bg-white').classList.remove('scale-95');
            modal.querySelector('.bg-white').classList.add('scale-100');
        }, 10);
        
        // Event handlers
        modal.querySelector('#confirm-delete').addEventListener('click', () => {
            modal.remove();
            resolve(true);
        });
        
        modal.querySelector('#cancel-delete').addEventListener('click', () => {
            modal.remove();
            resolve(false);
        });
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
                resolve(false);
            }
        });
    });
}

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        clearFieldError(field);
        
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        }
    });
    
    // Email validation
    const emailFields = form.querySelectorAll('[type="email"]');
    emailFields.forEach(field => {
        if (field.value && !isValidEmail(field.value)) {
            showFieldError(field, 'Please enter a valid email');
            isValid = false;
        }
    });
    
    return isValid;
}

function showFieldError(field, message) {
    field.classList.add('border-red-500');
    
    const error = document.createElement('p');
    error.className = 'text-red-500 text-sm mt-1 field-error';
    error.textContent = message;
    
    field.parentNode.appendChild(error);
}

function clearFieldError(field) {
    field.classList.remove('border-red-500');
    const error = field.parentNode.querySelector('.field-error');
    if (error) error.remove();
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Image preview
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0] && preview) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Format number
function formatNumber(num) {
    return new Intl.NumberFormat('en-US').format(num);
}

// Copy to clipboard
async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        showAdminToast('Copied to clipboard', 'success');
    } catch (err) {
        showAdminToast('Failed to copy', 'error');
    }
}

// Export table to CSV
function exportTableToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        
        cols.forEach(col => {
            // Skip action columns
            if (!col.classList.contains('no-export')) {
                let text = col.innerText.replace(/"/g, '""');
                rowData.push(`"${text}"`);
            }
        });
        
        csv.push(rowData.join(','));
    });
    
    // Download CSV
    const blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
}

// Chart initialization helper
function initChart(ctx, type, data, options = {}) {
    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    };
    
    return new Chart(ctx, {
        type: type,
        data: data,
        options: { ...defaultOptions, ...options }
    });
}

// Loading spinner
function showLoading(element) {
    if (typeof element === 'string') {
        element = document.getElementById(element);
    }
    
    if (element) {
        element.innerHTML = `
            <div class="flex items-center justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-primary-500"></div>
            </div>
        `;
    }
}

// AJAX helper
async function adminFetch(url, options = {}) {
    try {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            }
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Something went wrong');
        }
        
        return data;
    } catch (error) {
        showAdminToast(error.message, 'error');
        throw error;
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initSidebar();
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.admin-alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
    
    // Form submission with confirmation
    const deleteForms = document.querySelectorAll('[data-confirm-delete]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const confirmed = await confirmDelete(this.dataset.confirmDelete || 'Are you sure?');
            if (confirmed) {
                this.submit();
            }
        });
    });
});
