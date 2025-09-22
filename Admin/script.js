// RTW Admin Dashboard JavaScript

// Initialize the dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
});

function initializeDashboard() {
    // Initialize Lucide icons
    lucide.createIcons();
    
    // Setup navigation
    setupNavigation();
    
    // Setup sidebar toggle
    setupSidebarToggle();
    
    // Setup user menu
    setupUserMenu();
    
    // Initialize charts
    initializeCharts();
    
    // Populate tables
    populateTables();
    
    // Setup search functionality
    setupSearch();
}

// Navigation Management
function setupNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Only handle internal dashboard pages
            if (this.dataset.page) {
                e.preventDefault();

                const pages = document.querySelectorAll('.page');
                navLinks.forEach(l => l.classList.remove('active'));
                pages.forEach(p => p.classList.remove('active'));

                this.classList.add('active');
                const pageId = this.dataset.page + '-page';
                const targetPage = document.getElementById(pageId);
                if (targetPage) targetPage.classList.add('active');

                window.location.hash = this.dataset.page;
            }
            // If NO data-page → normal link (like seller-product.html) will work
        });
    });
}


// Sidebar Toggle Functionality
function setupSidebarToggle() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    
    // Desktop sidebar toggle
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
    });
    
    // Mobile menu toggle
    mobileMenuToggle.addEventListener('click', function() {
        sidebar.classList.toggle('mobile-open');
    });
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                sidebar.classList.remove('mobile-open');
            }
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('mobile-open');
        }
    });
}

// User Menu Toggle
function setupUserMenu() {
    const userMenuToggle = document.getElementById('userMenuToggle');
    const userDropdown = document.getElementById('userDropdown');
    
    userMenuToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        userDropdown.classList.toggle('active');
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!userMenuToggle.contains(e.target) && !userDropdown.contains(e.target)) {
            userDropdown.classList.remove('active');
        }
    });
}

// Chart Initialization
function initializeCharts() {
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'line',
            data: revenueData,
            options: chartOptions.revenue
        });
    }
    
    // Category Chart
    const categoryCtx = document.getElementById('categoryChart');
    if (categoryCtx) {
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: categoryData,
            options: chartOptions.category
        });
    }
}

// Populate Data Tables
function populateTables() {
    populateTopProducts();
    populateRecentTransactions();
}

function populateTopProducts() {
    const container = document.getElementById('topProductsTable');
    if (!container) return;
    
    container.innerHTML = topProducts.map((product, index) => `
        <div class="table-item">
            <div class="item-left">
                <div class="item-rank">#${index + 1}</div>
                <div class="item-details">
                    <h4>${product.name}</h4>
                    <div class="item-meta">
                        <span class="badge outline">${product.category}</span>
                        <span>•</span>
                        <span>Stock: ${product.stock}</span>
                    </div>
                </div>
            </div>
            <div class="item-right">
                <div class="item-metrics">
                    <div class="item-value">${product.revenue}</div>
                    <div class="item-subtitle">${product.sales} sales</div>
                </div>
                <div class="trend-indicator ${product.trend === 'up' ? 'positive' : 'negative'}">
                    <i data-lucide="${product.trend === 'up' ? 'trending-up' : 'trending-down'}"></i>
                    <span>${product.change > 0 ? '+' : ''}${product.change}%</span>
                </div>
            </div>
        </div>
    `).join('');
    
    // Re-initialize icons for new content
    lucide.createIcons();
}

function populateRecentTransactions() {
    const container = document.getElementById('recentTransactionsTable');
    if (!container) return;
    
    container.innerHTML = recentTransactions.map(transaction => `
        <div class="table-item">
            <div class="item-left">
                <div class="item-avatar">
                    ${transaction.customer.split(' ').map(n => n[0]).join('')}
                </div>
                <div class="item-details">
                    <h4>${transaction.customer}</h4>
                    <div class="item-meta">
                        <span>${transaction.email}</span>
                        <span>•</span>
                        <span>${transaction.items} items</span>
                    </div>
                </div>
            </div>
            <div class="item-right">
                <div class="item-metrics">
                    <div class="item-value">${transaction.amount}</div>
                    <div class="item-subtitle">${transaction.date}</div>
                </div>
                <span class="badge ${getStatusBadgeClass(transaction.status)}">
                    ${transaction.status}
                </span>
            </div>
        </div>
    `).join('');
}

function getStatusBadgeClass(status) {
    switch (status) {
        case 'completed':
            return 'success';
        case 'processing':
            return 'warning';
        case 'failed':
            return 'danger';
        default:
            return 'outline';
    }
}

// Search Functionality
function setupSearch() {
    const searchInput = document.querySelector('.search-input');
    if (!searchInput) return;
    
    searchInput.addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase();
        // Implement search logic here
        console.log('Searching for:', query);
        
        // For demo purposes, just log the search
        // In a real app, this would filter the displayed data
    });
}

// Utility Functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

function formatNumber(number) {
    return new Intl.NumberFormat('en-US').format(number);
}

function getInitials(name) {
    return name.split(' ').map(n => n[0]).join('');
}

// Animation utilities
function animateValue(element, start, end, duration) {
    const startTime = performance.now();
    const isNumber = !isNaN(end);
    
    function updateValue(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        if (isNumber) {
            const current = start + (end - start) * progress;
            element.textContent = Math.floor(current).toLocaleString();
        }
        
        if (progress < 1) {
            requestAnimationFrame(updateValue);
        }
    }
    
    requestAnimationFrame(updateValue);
}

// Initialize counter animations for metric cards
function animateMetrics() {
    const metricValues = document.querySelectorAll('.metric-value');
    
    metricValues.forEach(element => {
        const text = element.textContent;
        const number = parseFloat(text.replace(/[^0-9.-]+/g, ''));
        
        if (!isNaN(number)) {
            element.textContent = '0';
            setTimeout(() => {
                animateValue(element, 0, number, 1000);
            }, Math.random() * 500);
        }
    });
}

// Real-time updates simulation
function simulateRealTimeUpdates() {
    setInterval(() => {
        // Simulate random metric updates
        const metricCards = document.querySelectorAll('.metric-card');
        const randomCard = metricCards[Math.floor(Math.random() * metricCards.length)];
        
        if (randomCard) {
            const changeElement = randomCard.querySelector('.metric-change');
            if (changeElement) {
                // Add a subtle flash animation to indicate update
                randomCard.style.backgroundColor = 'var(--color-accent)';
                setTimeout(() => {
                    randomCard.style.backgroundColor = '';
                }, 300);
            }
        }
    }, 30000); // Update every 30 seconds
}

// Start real-time updates simulation
setTimeout(simulateRealTimeUpdates, 5000);

// Export functions for potential external use
window.RTWDashboard = {
    initializeDashboard,
    setupNavigation,
    populateTables,
    animateMetrics,
    formatCurrency,
    formatNumber
};