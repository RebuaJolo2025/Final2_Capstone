// DOM Elements
const productsGrid = document.getElementById('productsGrid');
const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilter');
const statusFilter = document.getElementById('statusFilter');
const clearFiltersBtn = document.getElementById('clearFilters');
const productCount = document.getElementById('productCount');
const emptyState = document.getElementById('emptyState');
const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');

// Modal elements
const addProductModal = document.getElementById('addProductModal');
const addProductBtn = document.getElementById('addProductBtn');
const closeModalBtn = document.getElementById('closeModal');
const cancelBtn = document.getElementById('cancelBtn');
const productForm = document.getElementById('productForm');
const productIdInput = document.getElementById('productId');
const productNameInput = document.getElementById('productName');
const productPriceInput = document.getElementById('productPrice');
const productStockInput = document.getElementById('productStock');
const productCategorySelect = document.getElementById('productCategory');
const productDescriptionTextarea = document.getElementById('productDescription');

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
let isEditing = false;
let currentFilters = { search: '', category: 'all', status: 'all' };
const selectedIds = new Set();

// Fetch products from database
async function fetchProducts() {
    try {
        const response = await fetch('get_products.php');
        if (!response.ok) throw new Error('Network response was not ok');
        products = await response.json();
        selectedIds.clear();
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
    if (deleteSelectedBtn) {
        deleteSelectedBtn.addEventListener('click', async () => {
            if (selectedIds.size === 0) { showToast('No products selected', 'error'); return; }
            if (!confirm(`Delete ${selectedIds.size} selected product(s)? This cannot be undone.`)) return;
            await bulkDelete(Array.from(selectedIds));
        });
    }

    addProductBtn.addEventListener('click', (e) => { e.preventDefault(); startAdd(); });
    closeModalBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    productForm.addEventListener('submit', handleSubmitProduct);

    addProductModal.addEventListener('click', (e) => {
        if (e.target === addProductModal) closeModal();
    });

    // removed dropdown menu handlers
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
                    <img src="${product.image}" alt="${product.name}" loading="lazy" 
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="image-placeholder" style="display:none; width:100%; height:200px; background:#f3f4f6; align-items:center; justify-content:center; color:#9ca3af; font-size:14px;">
                        📷 No Image
                    </div>
                </div>
                <div class="product-content">
                    <div class="product-header">
                        <label style="display:flex; align-items:center; gap:8px;">
                          <input type="checkbox" class="row-select" data-id="${product.id}" ${selectedIds.has(product.id) ? 'checked' : ''}>
                          <h3 class="product-title" style="margin:0;">${product.name}</h3>
                        </label>
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

        // attach checkbox listeners
        document.querySelectorAll('.row-select').forEach(cb => {
            cb.addEventListener('change', (e) => {
                const id = parseInt(e.target.getAttribute('data-id'));
                if (e.target.checked) selectedIds.add(id); else selectedIds.delete(id);
            });
        });
    }

    productCount.textContent = `Showing ${filtered.length} of ${products.length} products`;
}

// Dropdown removed
function editProduct(id) {
    const p = products.find(prod => prod.id === id);
    if (!p) return;
    isEditing = true;
    productIdInput.value = p.id;
    productNameInput.value = p.name || '';
    productPriceInput.value = p.price ?? '';
    productStockInput.value = p.stock ?? '';
    productCategorySelect.value = p.category || '';
    productDescriptionTextarea.value = p.description || '';
    // Change header and button text if present
    const header = addProductModal.querySelector('.modal-header h2');
    if (header) header.textContent = 'Edit Product';
    const submitBtn = productForm.querySelector('button[type="submit"]');
    if (submitBtn) submitBtn.textContent = 'Update Product';
    openModal();
}
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
function closeModal() {
    addProductModal.classList.remove('show');
    document.body.style.overflow = 'auto';
    productForm.reset();
    productIdInput.value = '';
    isEditing = false;
    // Reset header/button text
    const header = addProductModal.querySelector('.modal-header h2');
    if (header) header.textContent = 'Add New Product';
    const submitBtn = productForm.querySelector('button[type="submit"]');
    if (submitBtn) submitBtn.textContent = 'Add Product';
}

function startAdd() {
    isEditing = false;
    productIdInput.value = '';
    openModal();
}

// Add/Update product
async function handleSubmitProduct(e) {
    e.preventDefault();
    const formData = new FormData(productForm);
    const hasId = (formData.get('id') || '').toString().trim() !== '';

    try {
        const url = hasId ? 'update_product.php' : 'save_product.php';
        const response = await fetch(url, { method: 'POST', body: formData });
        const result = await response.json();
        if (result.success) {
            showToast(hasId ? 'Product updated successfully!' : 'Product added successfully!');
            fetchProducts();
            closeModal();
        } else {
            showToast(result.message || (hasId ? 'Failed to update product' : 'Failed to add product'), 'error');
        }
    } catch (error) {
        console.error(error);
        showToast(hasId ? 'Failed to update product' : 'Failed to add product', 'error');
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
