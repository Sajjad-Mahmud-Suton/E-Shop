// LocalStorage Utilities
const getLocalStorage = (key) => JSON.parse(localStorage.getItem(key)) || [];
const setLocalStorage = (key, data) => localStorage.setItem(key, JSON.stringify(data));

// User and Cart Management
let users = getLocalStorage('users');
let cart = getLocalStorage('cart');
let currentUser = getLocalStorage('currentUser');
let isLoggedIn = !!currentUser;

// Auth Functions
const handleLogin = (event) => {
    event.preventDefault();
    const email = document.getElementById('loginEmail').value.trim();
    const password = document.getElementById('loginPassword').value.trim();

    if (!email || !password) {
        showAlert('ইমেইল এবং পাসওয়ার্ড পূরণ করুন!', 'danger');
        return;
    }

    const user = users.find(u => u.email === email && u.password === password);
    
    if (user) {
        currentUser = user;
        setLocalStorage('currentUser', currentUser);
        showAlert('লগইন সফল!', 'success');
        window.location.reload();
    } else {
        showAlert('ভুল ইমেইল বা পাসওয়ার্ড!', 'danger');
    }
};

const handleRegister = (event) => {
    event.preventDefault();
    const name = document.getElementById('regName').value.trim();
    const email = document.getElementById('regEmail').value.trim();
    const password = document.getElementById('regPassword').value.trim();
    const phone = document.getElementById('regPhone').value.trim();

    if (!/^01\d{9}$/.test(phone)) {
        showAlert('সঠিক ফোন নাম্বার দিন!', 'danger');
        return;
    }

    if (users.some(u => u.email === email)) {
        showAlert('এই ইমেইলটি ইতিমধ্যে ব্যবহার করা হয়েছে!', 'danger');
        return;
    }

    const newUser = { id: Date.now(), name, email, password, phone };
    users.push(newUser);
    setLocalStorage('users', users);
    showAlert('রেজিস্ট্রেশন সফল! লগইন করুন।', 'success');
    switchToLoginTab();
};

// Cart Functions
const addToCart = (product) => {
    if (!isLoggedIn) {
        showLoginModal();
        return;
    }

    const existingItem = cart.find(item => item.id === product.id);
    if (existingItem) {
        existingItem.quantity += product.quantity;
    } else {
        cart.push({...product, addedAt: new Date()});
    }

    setLocalStorage('cart', cart);
    updateCartUI();
    showAlert('পণ্য কার্টে যোগ হয়েছে!', 'success');
};

const updateCartUI = () => {
    const cartCount = document.getElementById('cartCount');
    cartCount.textContent = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    const cartTotal = document.getElementById('cartTotal');
    cartTotal.textContent = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0).toLocaleString();
};

// UI Helpers
const showAlert = (message, type) => {
    const alertBox = document.createElement('div');
    alertBox.className = `alert alert-${type} fixed-top text-center`;
    alertBox.textContent = message;
    document.body.prepend(alertBox);

    setTimeout(() => alertBox.remove(), 3000);
};

const togglePassword = (fieldId, button) => {
    const field = document.getElementById(fieldId);
    const isPassword = field.type === 'password';
    field.type = isPassword ? 'text' : 'password';
    button.innerHTML = isPassword ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
};

// Pagination
let currentPage = 1;
const ITEMS_PER_PAGE = 8;

const setupPagination = () => {
    const items = document.querySelectorAll('.product-card');
    const totalPages = Math.ceil(items.length / ITEMS_PER_PAGE);

    document.querySelectorAll('.page-item').forEach(btn => {
        btn.addEventListener('click', (e) => {
            currentPage = parseInt(e.target.dataset.page);
            updateProductVisibility(items);
            updatePaginationButtons(totalPages);
        });
    });
};

const updateProductVisibility = (items) => {
    const start = (currentPage - 1) * ITEMS_PER_PAGE;
    const end = start + ITEMS_PER_PAGE;

    items.forEach((item, index) => {
        item.style.display = (index >= start && index < end) ? 'block' : 'none';
    });
};

// Initialization
document.addEventListener('DOMContentLoaded', () => {
    // Auth Initialization
    if (currentUser) {
        document.getElementById('loginBtn').textContent = currentUser.name;
        document.getElementById('logoutBtn').classList.remove('d-none');
    }

    // Cart Initialization
    updateCartUI();

    // Event Listeners
    document.getElementById('loginForm').addEventListener('submit', handleLogin);
    document.getElementById('registerForm').addEventListener('submit', handleRegister);
    
    document.getElementById('logoutBtn').addEventListener('click', () => {
        localStorage.removeItem('currentUser');
        window.location.reload();
    });

    // Password Toggles
    document.querySelectorAll('.password-toggle').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const fieldId = e.target.closest('button').dataset.target;
            togglePassword(fieldId, e.target);
        });
    });

    // Initialize Pagination
    setupPagination();
});