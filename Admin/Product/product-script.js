// DOM Elements
const productsGrid = document.getElementById('productsGrid');
const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilter');
const statusFilter = document.getElementById('statusFilter');
const clearFiltersBtn = document.getElementById('clearFilters');
const productCount = document.getElementById('productCount');
const emptyState = document.getElementById('emptyState');

// Modal elements
const addProductModal = document.getElementById('addProductModal');
const addProductBtn = document.getElementById('addProductBtn');
const closeModalBtn = document.getElementById('closeModal');
const cancelBtn = document.getElementById('cancelBtn');
const productForm = document.getElementById('productForm');

// Toast
const toast = document.getElementById('toast');
const toastMessage = document.getElementById('toastMessage');

// Stats elements
const totalProductsEl = document.getElementById('totalProducts');
const activeProductsEl = document.getElementById('activeProducts');
const lowStockProductsEl = document.getElementById('lowStockProducts');
const totalSoldEl = document.getElementById('totalSold');

// Filter state
let products = [];
let currentFilters = { search: '', category: 'all', status: 'all' };

// Fetch products from database
async function fetchProducts() {
    try {
        const response = await fetch('get_products.php');
        if (!response.ok) throw new Error('Network response was not ok');
        products = await response.json();
        renderProducts();
        updateStats();
    } catch (error) {
        console.error('Error fetching products:', error);
        showToast('Failed to load products from database', 'error');
    }
}

// Initialize the app
function init() {
    currentFilters = { search: '', category: 'all', status: 'all' };
    searchInput.value = '';
    categoryFilter.value = 'all';
    statusFilter.value = 'all';
    productForm.reset();

    fetchProducts();
    setupEventListeners();
}

// Setup event listeners
function setupEventListeners() {
    searchInput.addEventListener('input', handleSearch);
    categoryFilter.addEventListener('change', handleCategoryFilter);
    statusFilter.addEventListener('change', handleStatusFilter);
    clearFiltersBtn.addEventListener('click', clearFilters);

    addProductBtn.addEventListener('click', openModal);
    closeModalBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    productForm.addEventListener('submit', handleAddProduct);

    addProductModal.addEventListener('click', (e) => {
        if (e.target === addProductModal) closeModal();
    });

    document.addEventListener('click', (e) => {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            if (!menu.closest('.action-menu').contains(e.target)) {
                menu.classList.remove('show');
            }
        });
    });
}

// Handle search and filters
function handleSearch(e) { currentFilters.search = e.target.value.toLowerCase(); renderProducts(); }
function handleCategoryFilter(e) { currentFilters.category = e.target.value; renderProducts(); }
function handleStatusFilter(e) { currentFilters.status = e.target.value; renderProducts(); }
function clearFilters() {
    currentFilters = { search: '', category: 'all', status: 'all' };
    searchInput.value = '';
    categoryFilter.value = 'all';
    statusFilter.value = 'all';
    renderProducts();
}

// Filter products
function filterProducts() {
    return products.filter(product => {
        const matchesSearch = product.name.toLowerCase().includes(currentFilters.search);
        const matchesCategory = currentFilters.category === 'all' || product.category === currentFilters.category;
        const matchesStatus = currentFilters.status === 'all' || product.status === currentFilters.status;
        return matchesSearch && matchesCategory && matchesStatus;
    });
}

// Render products
function renderProducts() {
    const filtered = filterProducts();

    if (filtered.length === 0) {
        productsGrid.style.display = 'none';
        emptyState.style.display = 'block';
    } else {
        productsGrid.style.display = 'grid';
        emptyState.style.display = 'none';
        productsGrid.innerHTML = filtered.map(product => `
            <div class="product-card">
                <div class="product-image">
                    <img src="${product.image}" alt="${product.name}" loading="lazy">
                    <div class="product-actions">
                        <div class="action-menu">
                            <button class="action-btn" onclick="toggleDropdown(${product.id})">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                            <div class="dropdown-menu" id="dropdown-${product.id}">
                                <button class="dropdown-item" onclick="editProduct(${product.id})">
                                    <i class="fas fa-edit"></i> Edit Product
                                </button>
                                <button class="dropdown-item" onclick="viewProduct(${product.id})">
                                    <i class="fas fa-eye"></i> View Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="product-content">
                    <div class="product-header">
                        <h3 class="product-title">${product.name}</h3>
                        <span class="product-category">${product.category}</span>
                    </div>
                    <div class="product-details">
                        <div>
                            <div class="product-price">₱${product.price.toLocaleString()}</div>
                            <div class="product-stock">Stock: ${product.stock}</div>
                        </div>
                        <div class="product-stats">
                            <div class="product-sold">${product.sold} sold</div>
                            <div class="product-status">
                                <span>${product.status === 'active' ? 'Active' : 'Inactive'}</span>
                                <div class="status-toggle ${product.status === 'active' ? 'active' : ''}" 
                                     onclick="toggleProductStatus(${product.id})"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    productCount.textContent = `Showing ${filtered.length} of ${products.length} products`;
}

// Dropdown & actions
function toggleDropdown(id) {
    const dropdown = document.getElementById(`dropdown-${id}`);
    document.querySelectorAll('.dropdown-menu').forEach(menu => { if (menu !== dropdown) menu.classList.remove('show'); });
    dropdown.classList.toggle('show');
}
function editProduct(id) { showToast(`Editing ${products.find(p=>p.id===id).name}`); }
function viewProduct(id) { showToast(`Viewing ${products.find(p=>p.id===id).name}`); }
function toggleProductStatus(id) {
    const index = products.findIndex(p => p.id === id);
    if (index !== -1) {
        products[index].status = products[index].status === 'active' ? 'inactive' : 'active';
        renderProducts();
        updateStats();
        showToast('Product status updated');
    }
}

// Modal
function openModal() { addProductModal.classList.add('show'); document.body.style.overflow = 'hidden'; }
function closeModal() { addProductModal.classList.remove('show'); document.body.style.overflow = 'auto'; productForm.reset(); }

// Add product
async function handleAddProduct(e) {
    e.preventDefault();
    const formData = new FormData(productForm);

    try {
        const response = await fetch('add_product.php', { method: 'POST', body: formData });
        const result = await response.json();
        if (result.success) {
            showToast('Product added successfully!');
            fetchProducts();
            closeModal();
        } else {
            showToast(result.message || 'Failed to add product', 'error');
        }
    } catch (error) {
        console.error(error);
        showToast('Failed to add product', 'error');
    }
}

// Update stats
function updateStats() {
    totalProductsEl.textContent = products.length;
    activeProductsEl.textContent = products.filter(p => p.status==='active').length;
    lowStockProductsEl.textContent = products.filter(p => p.stock<10).length;
    totalSoldEl.textContent = products.reduce((sum,p)=>sum+p.sold,0);
}

// Toast
function showToast(msg, type='success') {
    toastMessage.textContent = msg;
    toast.style.background = type==='error' ? 'var(--color-destructive)' : 'var(--color-success)';
    toast.querySelector('i').className = type==='error' ? 'fas fa-exclamation-circle' : 'fas fa-check-circle';
    toast.classList.add('show');
    setTimeout(()=>toast.classList.remove('show'), 3000);
}

// Initialize
init();
