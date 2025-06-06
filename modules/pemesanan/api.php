
    <script>

    // Pharmacy App - Fixed Version with API Integration
    // Configuration
    const API_URL = 'api_pemesanan.php';
    const USER_ID = 1; // Dalam implementasi nyata, ini akan didapat dari session/login

    // Global variables
    let products = [];
    let cart = [];
    let currentPage = 'catalog';

    // Data obat fallback (jika API gagal)
    const defaultProducts = [
    {
        ID_obat: 1,
        nama_obat: 'Paracetamol',
        jenis_obat: 'Tablet',
        harga_obat: 6000,
        stok_obat: 100,
        deskripsi_obat: 'Obat untuk mengurangi demam dan nyeri ringan',
        tanggal_kadaluarsa: '2025-12-31'
    },
    {
        ID_obat: 2,
        nama_obat: 'Amoxicillin',
        jenis_obat: 'Kapsul',
        harga_obat: 15000,
        stok_obat: 50,
        deskripsi_obat: 'Antibiotik untuk infeksi bakteri',
        tanggal_kadaluarsa: '2024-02-15'
    },
    {
        ID_obat: 3,
        nama_obat: 'OBH Combi',
        jenis_obat: 'Sirup',
        harga_obat: 25000,
        stok_obat: 30,
        deskripsi_obat: 'Obat batuk dan flu',
        tanggal_kadaluarsa: '2025-08-20'
    },
    {
        ID_obat: 4,
        nama_obat: 'Antasida',
        jenis_obat: 'Tablet',
        harga_obat: 8000,
        stok_obat: 75,
        deskripsi_obat: 'Obat untuk mengatasi sakit maag',
        tanggal_kadaluarsa: '2025-11-30'
    },
    {
        ID_obat: 5,
        nama_obat: 'Vitamin C',
        jenis_obat: 'Tablet',
        harga_obat: 12000,
        stok_obat: 80,
        deskripsi_obat: 'Suplemen vitamin C untuk daya tahan tubuh',
        tanggal_kadaluarsa: '2026-01-15'
    }
];

// Utility Functions
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

function formatCurrency(amount) {
    return 'Rp ' + amount.toLocaleString('id-ID');
}

function validatePhoneNumber(phone) {
    const phoneRegex = /^(\+62|62|0)[2-9][0-9]{7,11}$/;
    return phoneRegex.test(phone.replace(/\s|-/g, ''));
}

// API Functions
async function apiRequest(endpoint, options = {}) {
    try {
        const response = await fetch(endpoint, {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || `HTTP Error: ${response.status}`);
        }
        
        return data;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

// Load products from API with fallback
async function loadProductsFromAPI() {
    try {
        showLoadingMessage('Memuat data obat...');
        const response = await apiRequest(`${API_URL}?action=get_products`);
        
        if (response.success && response.data) {
            products = response.data;
            console.log('Products loaded from API:', products.length);
        } else {
            throw new Error('Invalid API response format');
        }
        
        displayProducts(products);
        hideLoadingMessage();
        
    } catch (error) {
        console.warn('Failed to load products from API:', error);
        products = [...defaultProducts];
        displayProducts(products);
        hideLoadingMessage();
        showNotification('Menggunakan data offline. Koneksi ke server bermasalah.', 'warning');
    }
}


// Display loading message
function showLoadingMessage(message) {
    const grid = document.getElementById('productsGrid');
    if (grid) {
        grid.innerHTML = `
            <div class="loading-message" style="grid-column: 1 / -1; text-align: center; padding: 2rem;">
                <div style="font-size: 2rem; margin-bottom: 1rem;">⏳</div>
                <p>${message}</p>
            </div>
        `;
    }
}

function hideLoadingMessage() {
    const loadingMsg = document.querySelector('.loading-message');
    if (loadingMsg) {
        loadingMsg.remove();
    }
}

// Display products in grid
function displayProducts(productsToShow) {
    const grid = document.getElementById('productsGrid');
    
    if (!grid) {
        console.error('Products grid element not found!');
        return;
    }
    
    if (productsToShow.length === 0) {
        grid.innerHTML = `
            <div class="alert alert-info" style="grid-column: 1 / -1; text-align: center;">
                Tidak ada produk yang ditemukan.
            </div>
        `;
        return;
    }
    
    grid.innerHTML = '';

    productsToShow.forEach(product => {
        // Handle both API format and fallback format
        const id = product.ID_obat || product.id;
        const name = product.nama_obat || product.name;
        const type = product.jenis_obat || product.type;
        const price = product.harga_obat || product.price;
        const stock = product.stok_obat || product.stock;
        const expiry = product.tanggal_kadaluarsa || product.expiry;
        
        const isLowStock = stock < 10;
        const isExpiringSoon = new Date(expiry) < new Date(Date.now() + 30 * 24 * 60 * 60 * 1000);
        
        const productCard = document.createElement('div');
        productCard.className = 'product-card';
        productCard.innerHTML = `
            <div class="product-image">💊</div>
            <div class="product-name">${escapeHtml(name)}</div>
            <div class="product-type">${escapeHtml(type)}</div>
            <div class="product-price">${formatCurrency(price)}</div>
            <div class="product-stock">Stok: ${stock} ${type.toLowerCase()}</div>
            ${isLowStock ? '<div class="alert alert-warning">⚠️ Stok Terbatas!</div>' : ''}
            ${isExpiringSoon ? '<div class="alert alert-error">⚠️ Segera Kadaluarsa!</div>' : ''}
            <button class="btn" onclick="showProductDetail(${id})" ${stock === 0 ? 'disabled' : ''}>
                ${stock === 0 ? 'Stok Habis' : 'Lihat Detail'}
            </button>
        `;
        grid.appendChild(productCard);
    });
}

// Search products
function searchProducts(query) {
    if (!query || query.trim() === '') {
        displayProducts(products);
        return;
    }
    
    const filtered = products.filter(product => {
        const name = product.nama_obat || product.name || '';
        const type = product.jenis_obat || product.type || '';
        const searchTerm = query.toLowerCase();
        
        return name.toLowerCase().includes(searchTerm) || 
               type.toLowerCase().includes(searchTerm);
    });
    
    displayProducts(filtered);
}

// Show product detail modal
function showProductDetail(productId) {
    const product = products.find(p => (p.ID_obat || p.id) === productId);
    if (!product) {
        showNotification('Produk tidak ditemukan', 'error');
        return;
    }

    const modal = document.getElementById('detailModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');

    if (!modal || !modalTitle || !modalBody) {
        console.error('Modal elements not found');
        return;
    }

    // Handle both API format and fallback format
    const id = product.ID_obat || product.id;
    const name = product.nama_obat || product.name;
    const type = product.jenis_obat || product.type;
    const price = product.harga_obat || product.price;
    const stock = product.stok_obat || product.stock;
    const description = product.deskripsi_obat || product.description;
    const expiry = product.tanggal_kadaluarsa || product.expiry;

    modalTitle.textContent = name;
    
    const expiryDate = new Date(expiry);
    const isExpiringSoon = expiryDate < new Date(Date.now() + 30 * 24 * 60 * 60 * 1000);
    const isLowStock = stock < 10;

    modalBody.innerHTML = `
        <div class="product-image" style="margin-bottom: 1.5rem;">💊</div>
        <div style="margin-bottom: 1rem;">
            <strong>Jenis:</strong> ${escapeHtml(type)}
        </div>
        <div style="margin-bottom: 1rem;">
            <strong>Harga:</strong> <span style="color: #38a169; font-size: 1.25rem; font-weight: bold;">${formatCurrency(price)}</span>
        </div>
        <div style="margin-bottom: 1rem;">
            <strong>Stok:</strong> ${stock} ${type.toLowerCase()}
        </div>
        <div style="margin-bottom: 1rem;">
            <strong>Tanggal Kadaluarsa:</strong> ${expiryDate.toLocaleDateString('id-ID')}
        </div>
        <div style="margin-bottom: 1.5rem;">
            <strong>Deskripsi:</strong><br>
            ${escapeHtml(description)}
        </div>
        ${isLowStock ? '<div class="alert alert-warning">⚠️ Stok Terbatas!</div>' : ''}
        ${isExpiringSoon ? '<div class="alert alert-error">⚠️ Segera Kadaluarsa!</div>' : ''}
        ${stock > 0 ? `
            <div class="quantity-selector">
                <button class="quantity-btn" onclick="decreaseQuantity()">-</button>
                <input type="number" class="quantity-input" id="productQuantity" value="1" min="1" max="${stock}">
                <button class="quantity-btn" onclick="increaseQuantity()">+</button>
            </div>
            <button class="btn" onclick="addToCart(${id})" style="margin-top: 1rem;">
                Tambah ke Keranjang
            </button>
        ` : `
            <div class="alert alert-error">Stok habis</div>
        `}
    `;

    modal.style.display = 'flex';
}

// Close modal
function closeModal() {
    const modal = document.getElementById('detailModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Quantity controls
function increaseQuantity() {
    const input = document.getElementById('productQuantity');
    if (!input) return;
    
    const max = parseInt(input.getAttribute('max'));
    const current = parseInt(input.value);
    
    if (current < max) {
        input.value = current + 1;
    }
}

function decreaseQuantity() {
    const input = document.getElementById('productQuantity');
    if (!input) return;
    
    const current = parseInt(input.value);
    if (current > 1) {
        input.value = current - 1;
    }
}

// Add to cart
function addToCart(productId) {
    const product = products.find(p => (p.ID_obat || p.id) === productId);
    const quantityInput = document.getElementById('productQuantity');
    
    if (!product || !quantityInput) return;
    
    const quantity = parseInt(quantityInput.value);
    const stock = product.stok_obat || product.stock;
    
    if (quantity > stock) {
        showNotification(`Stok tidak mencukupi. Stok tersedia: ${stock}`, 'error');
        return;
    }
    
    const existingItem = cart.find(item => item.id === productId);
    
    if (existingItem) {
        const newQuantity = existingItem.quantity + quantity;
        if (newQuantity > stock) {
            showNotification(`Total quantity melebihi stok. Stok tersedia: ${stock}`, 'error');
            return;
        }
        existingItem.quantity = newQuantity;
    } else {
        // Normalize product data for cart
        cart.push({
            id: product.ID_obat || product.id,
            name: product.nama_obat || product.name,
            type: product.jenis_obat || product.type,
            price: product.harga_obat || product.price,
            stock: product.stok_obat || product.stock,
            quantity: quantity
        });
    }
    
    updateCartCount();
    closeModal();
    showNotification('Obat berhasil ditambahkan ke keranjang!', 'success');
}

// Update cart count
function updateCartCount() {
    const cartCount = document.getElementById('cartCount');
    if (!cartCount) return;
    
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    if (totalItems > 0) {
        cartCount.textContent = totalItems;
        cartCount.style.display = 'inline';
    } else {
        cartCount.style.display = 'none';
    }
}

// Show notification
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notif => notif.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification alert alert-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        max-width: 300px;
        padding: 1rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Load cart items
function loadCartItems() {
    const cartItemsContainer = document.getElementById('cartItems');
    const cartTotalContainer = document.getElementById('cartTotal');
    
    if (!cartItemsContainer || !cartTotalContainer) return;
    
    if (cart.length === 0) {
        cartItemsContainer.innerHTML = '<p style="text-align: center; color: #718096; padding: 2rem;">Keranjang belanja kosong</p>';
        cartTotalContainer.style.display = 'none';
        return;
    }
    
    cartItemsContainer.innerHTML = '';
    let total = 0;
    
    cart.forEach(item => {
        const subtotal = item.price * item.quantity;
        total += subtotal;
        
        const cartItem = document.createElement('div');
        cartItem.className = 'cart-item';
        cartItem.innerHTML = `
            <div class="cart-item-info">
                <div class="cart-item-name">${escapeHtml(item.name)}</div>
                <div class="product-type">${escapeHtml(item.type)}</div>
                <div class="cart-item-price">${formatCurrency(item.price)} x ${item.quantity}</div>
                <div class="cart-item-subtotal">Subtotal: ${formatCurrency(subtotal)}</div>
            </div>
            <div class="cart-item-controls">
                <div class="quantity-selector">
                    <button class="quantity-btn" onclick="updateCartItemQuantity(${item.id}, ${item.quantity - 1})">-</button>
                    <span style="padding: 0 1rem;">${item.quantity}</span>
                    <button class="quantity-btn" onclick="updateCartItemQuantity(${item.id}, ${item.quantity + 1})">+</button>
                </div>
                <button class="remove-btn" onclick="removeFromCart(${item.id})" title="Hapus dari keranjang">🗑️</button>
            </div>
        `;
        cartItemsContainer.appendChild(cartItem);
    });
    
    const shippingCost = 10000;
    const grandTotal = total + shippingCost;
    
    cartTotalContainer.style.display = 'block';
    cartTotalContainer.innerHTML = `
        <div class="total-row">
            <span>Subtotal:</span>
            <span>${formatCurrency(total)}</span>
        </div>
        <div class="total-row">
            <span>Ongkos Kirim:</span>
            <span>${formatCurrency(shippingCost)}</span>
        </div>
        <div class="total-row" style="font-weight: bold; font-size: 1.1rem; border-top: 1px solid #e2e8f0; padding-top: 0.5rem;">
            <span>Total:</span>
            <span>${formatCurrency(grandTotal)}</span>
        </div>
        <button class="btn" onclick="showPage('checkout')" style="margin-top: 1rem; width: 100%;">
            Lanjut ke Checkout
        </button>
    `;
}

// Update cart item quantity
function updateCartItemQuantity(productId, newQuantity) {
    if (newQuantity <= 0) {
        removeFromCart(productId);
        return;
    }
    
    const item = cart.find(item => item.id === productId);
    const product = products.find(p => (p.ID_obat || p.id) === productId);
    
    if (!item || !product) return;
    
    const availableStock = product.stok_obat || product.stock;
    
    if (newQuantity > availableStock) {
        showNotification(`Stok tidak mencukupi. Stok tersedia: ${availableStock}`, 'error');
        return;
    }
    
    item.quantity = newQuantity;
    updateCartCount();
    loadCartItems();
}

// Remove from cart
function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    updateCartCount();
    loadCartItems();
    showNotification('Item berhasil dihapus dari keranjang', 'info');
}

// Load checkout
function loadCheckout() {
    const checkoutTotal = document.getElementById('checkoutTotal');
    const checkoutContent = document.getElementById('checkoutContent');
    
    if (!checkoutTotal || !checkoutContent) return;
    
    if (cart.length === 0) {
        checkoutContent.innerHTML = `
            <div style="text-align: center; padding: 2rem; color: #718096;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🛒</div>
                <h3>Keranjang Belanja Kosong</h3>
                <p>Tambahkan obat ke keranjang untuk melanjutkan pemesanan</p>
                <button class="btn" onclick="showPage('catalog')" style="margin-top: 1rem;">
                    Mulai Belanja
                </button>
            </div>
        `;
        checkoutTotal.innerHTML = '';
        return;
    }
    
    let total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const shippingCost = 10000;
    const grandTotal = total + shippingCost;
    
    checkoutTotal.innerHTML = `
        <h4 style="margin-bottom: 1rem;">Ringkasan Pesanan</h4>
        ${cart.map(item => `
            <div class="total-row">
                <span>${escapeHtml(item.name)} (${item.quantity}x)</span>
                <span>${formatCurrency(item.price * item.quantity)}</span>
            </div>
        `).join('')}
        <hr style="margin: 1rem 0; border: none; border-top: 1px solid #e2e8f0;">
        <div class="total-row">
            <span>Ongkos Kirim:</span>
            <span>${formatCurrency(shippingCost)}</span>
        </div>
        <div class="total-row" style="font-weight: bold; font-size: 1.2rem; color: #2d3748;">
            <span>Total Pembayaran:</span>
            <span>${formatCurrency(grandTotal)}</span>
        </div>
    `;
}

// Enhanced form validation
function validateCheckoutForm() {
    const customerName = document.getElementById('customerName')?.value.trim();
    const customerPhone = document.getElementById('customerPhone')?.value.trim();
    const customerAddress = document.getElementById('customerAddress')?.value.trim();
    const paymentMethod = document.getElementById('paymentMethod')?.value;
    
    const errors = [];
    
    if (!customerName || customerName.length < 2) {
        errors.push('Nama lengkap harus diisi minimal 2 karakter');
    }
    
    if (!customerPhone) {
        errors.push('Nomor telepon harus diisi');
    } else if (!validatePhoneNumber(customerPhone)) {
        errors.push('Format nomor telepon tidak valid (contoh: 081234567890)');
    }
    
    if (!customerAddress || customerAddress.length < 10) {
        errors.push('Alamat lengkap harus diisi minimal 10 karakter');
    }
    
    if (!paymentMethod) {
        errors.push('Metode pembayaran harus dipilih');
    }
    
    return errors;
}

// Process order with API integration
async function processOrder() {
    const customerName = document.getElementById('customerName')?.value.trim();
    const customerPhone = document.getElementById('customerPhone')?.value.trim();
    const customerAddress = document.getElementById('customerAddress')?.value.trim();
    const paymentMethod = document.getElementById('paymentMethod')?.value;
    const orderNotes = document.getElementById('orderNotes')?.value.trim();
    
    // Validasi form
    const validationErrors = validateCheckoutForm();
    if (validationErrors.length > 0) {
        showNotification('Error: ' + validationErrors.join(', '), 'error');
        return;
    }
    
    if (cart.length === 0) {
        showNotification('Keranjang belanja kosong!', 'error');
        return;
    }
    
    // Disable button untuk mencegah double click
    const processBtn = document.getElementById('processOrderBtn');
    if (processBtn) {
        processBtn.disabled = true;
        processBtn.textContent = 'Memproses...';
    }
    
    try {
        // Prepare order data for API
        const orderData = {
            action: 'create_order',
            id_user: USER_ID,
            alamat_pengiriman: customerAddress,
            nomor_telepon: customerPhone,
            metode_pembayaran: paymentMethod,
            catatan: orderNotes,
            items: cart.map(item => ({
                id: item.id,
                quantity: item.quantity
            }))
        };
        
        // Send order to API
        const response = await apiRequest(API_URL, {
            method: 'POST',
            body: JSON.stringify(orderData)
        });
        
        if (response.success) {
            // Calculate totals
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const shippingCost = 10000;
            const total = subtotal + shippingCost;
            
            // Show success message
            showOrderSuccess(response.data.nomor_pemesanan, {
                customerName,
                customerPhone,
                customerAddress,
                paymentMethod,
                orderNotes,
                items: cart,
                subtotal,
                shippingCost,
                total
            });
            
            // Reset cart and reload products
            cart = [];
            updateCartCount();
            await loadProductsFromAPI(); // Refresh stock from server
            
        } else {
            throw new Error(response.error || 'Failed to create order');
        }
        
    } catch (error) {
        console.error('Order processing failed:', error);
        showNotification('Gagal memproses pesanan: ' + error.message, 'error');
        
        // Re-enable button
        if (processBtn) {
            processBtn.disabled = false;
            processBtn.textContent = 'Proses Pesanan';
        }
    }
}

// Show order success
function showOrderSuccess(orderNumber, orderDetails) {
    const checkoutContent = document.getElementById('checkoutContent');
    if (!checkoutContent) return;
    
    checkoutContent.innerHTML = `
        <div style="text-align: center; padding: 2rem;">
            <div style="font-size: 4rem; color: #38a169; margin-bottom: 1rem;">✅</div>
            <h2 style="color: #38a169; margin-bottom: 2rem;">Pesanan Berhasil!</h2>
            
            <div style="background: #f7fafc; padding: 2rem; border-radius: 10px; margin-bottom: 2rem; text-align: left;">
                <h3 style="margin-bottom: 1rem; color: #2d3748;">Detail Pesanan</h3>
                <p><strong>Nomor Pesanan:</strong> ${orderNumber}</p>
                <p><strong>Nama:</strong> ${escapeHtml(orderDetails.customerName)}</p>
                <p><strong>Telepon:</strong> ${escapeHtml(orderDetails.customerPhone)}</p>
                <p><strong>Alamat:</strong> ${escapeHtml(orderDetails.customerAddress)}</p>
                <p><strong>Metode Pembayaran:</strong> ${getPaymentMethodText(orderDetails.paymentMethod)}</p>
                ${orderDetails.orderNotes ? `<p><strong>Catatan:</strong> ${escapeHtml(orderDetails.orderNotes)}</p>` : ''}
                
                <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid #e2e8f0;">
                
                <h4 style="margin-bottom: 1rem;">Obat yang Dipesan:</h4>
                ${orderDetails.items.map(item => `
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span>${escapeHtml(item.name)} (${escapeHtml(item.type)}) x${item.quantity}</span>
                        <span>${formatCurrency(item.price * item.quantity)}</span>
                    </div>
                `).join('')}
                
                <hr style="margin: 1rem 0; border: none; border-top: 1px solid #e2e8f0;">
                
                <div style="display: flex; justify-content: space-between;">
                    <span>Subtotal:</span>
                    <span>${formatCurrency(orderDetails.subtotal)}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Ongkos Kirim:</span>
                    <span>${formatCurrency(orderDetails.shippingCost)}</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.2rem; color: #38a169; margin-top: 0.5rem;">
                    <span>Total:</span>
                    <span>${formatCurrency(orderDetails.total)}</span>
                </div>
            </div>
            
            <div style="background: #e6fffa; padding: 1.5rem; border-radius: 8px; border: 1px solid #81e6d9; margin-bottom: 2rem;">
                <h4 style="color: #234e52; margin-bottom: 1rem;">Informasi Pengiriman</h4>
                <p style="color: #234e52; margin-bottom: 0.5rem;">📦 Pesanan akan diproses dalam 1-2 jam kerja</p>
                <p style="color: #234e52; margin-bottom: 0.5rem;">🚚 Estimasi pengiriman 1-3 hari kerja</p>
                <p style="color: #234e52;">📱 Anda akan mendapat konfirmasi via WhatsApp</p>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <button class="btn" onclick="startNewOrder()" style="flex: 1; min-width: 200px;">
                    Pesan Lagi
                </button>
                <button class="btn btn-secondary" onclick="printOrder('${orderNumber}')" style="flex: 1; min-width: 200px;">
                    Cetak Struk
                </button>
            </div>
        </div>
    `;
}

// Get payment method text
function getPaymentMethodText(method) {
    const methods = {
        'tunai': 'Tunai (COD)',
        'transfer': 'Transfer Bank',
        'e-wallet': 'E-Wallet (OVO/GoPay/DANA)',
        'kartu_kredit': 'Kartu Kredit'
    };
    return methods[method] || method;

        
        // Start new order
        function startNewOrder() {
            // Reset form
            document.getElementById('customerName').value = '';
            document.getElementById('customerPhone').value = '';
            document.getElementById('customerAddress').value = '';
            document.getElementById('paymentMethod').value = '';
            document.getElementById('orderNotes').value = '';
            
            // Show catalog page
            showPage('catalog');
        }
        
        // Print order (simulasi)
        function printOrder(orderId) {
            alert(`Struk pesanan ${orderId} akan dicetak.\n\nDalam implementasi nyata, ini akan membuka jendela print atau men-download PDF struk.`);
        }

        // Show page
        function showPage(pageName) {
            // Hide all pages
            document.querySelectorAll('.page').forEach(page => {
                page.classList.remove('active');
            });
            
            // Remove active class from nav links
            document.querySelectorAll('.nav-links a').forEach(link => {
                link.classList.remove('active');
            });
            
            // Show selected page
            document.getElementById(pageName).classList.add('active');
            
            // Add active class to current nav link
            document.querySelectorAll('.nav-links a').forEach(link => {
                if (link.textContent.toLowerCase().includes(pageName) || 
                    (pageName === 'catalog' && link.textContent.includes('Katalog')) ||
                    (pageName === 'cart' && link.textContent.includes('Keranjang')) ||
                    (pageName === 'checkout' && link.textContent.includes('Checkout'))) {
                    link.classList.add('active');
                }
            });
            
            currentPage = pageName;
            
            // Load page specific content
            if (pageName === 'catalog') {
                loadProducts();
            } else if (pageName === 'cart') {
                loadCartItems();
            } else if (pageName === 'checkout') {
                loadCheckout();
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('detailModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(event) {
            // ESC to close modal
            if (event.key === 'Escape') {
                closeModal();
            }
            
            // Ctrl+1, Ctrl+2, Ctrl+3 untuk navigasi cepat
            if (event.ctrlKey) {
                switch(event.key) {
                    case '1':
                        event.preventDefault();
                        showPage('catalog');
                        break;
                    case '2':
                        event.preventDefault();
                        showPage('cart');
                        break;
                    case '3':
                        event.preventDefault();
                        showPage('checkout');
                        break;
                }
            }
        });

        // Auto-save form data (simulasi - dalam implementasi nyata akan menggunakan localStorage)
        function autoSaveForm() {
            const formData = {
                customerName: document.getElementById('customerName')?.value || '',
                customerPhone: document.getElementById('customerPhone')?.value || '',
                customerAddress: document.getElementById('customerAddress')?.value || '',
                paymentMethod: document.getElementById('paymentMethod')?.value || '',
                orderNotes: document.getElementById('orderNotes')?.value || ''
            };
            
            // Dalam implementasi nyata, data ini akan disimpan ke localStorage
            // localStorage.setItem('docto_form_data', JSON.stringify(formData));
            console.log('Form data auto-saved:', formData);
        }

        // Load saved form data (simulasi)
        function loadSavedFormData() {
            // Dalam implementasi nyata, data akan dimuat dari localStorage
            // const savedData = localStorage.getItem('docto_form_data');
            // if (savedData) {
            //     const formData = JSON.parse(savedData);
            //     document.getElementById('customerName').value = formData.customerName || '';
            //     // ... dan seterusnya
            // }
        }

        // Initialize app
        function initApp() {
            console.log('Initializing DocTo Pharmacy App...');
            
            // Load products immediately
            loadProducts();
            updateCartCount();
            
            // Ensure catalog page is active on load
            showPage('catalog');
            
            // Auto-save form setiap 30 detik
            setInterval(autoSaveForm, 30000);
            
            // Load saved form data
            loadSavedFormData();
            
            console.log('DocTo Pharmacy App initialized successfully!');
            console.log('Products loaded:', products.length);
        }

        // Initialize when DOM is loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initApp);
        } else {
            // DOM already loaded
            initApp();
        }

        // Handle page refresh warning if cart has items
        window.addEventListener('beforeunload', function(event) {
            if (cart.length > 0) {
                event.preventDefault();
                event.returnValue = 'Anda memiliki obat di keranjang. Yakin ingin meninggalkan halaman?';
                return 'Anda memiliki obat di keranjang. Yakin ingin meninggalkan halaman?';
            }
        });

        // Utility function to format currency
        function formatCurrency(amount) {
            return 'Rp ' + amount.toLocaleString('id-ID');
        }

        // Utility function to validate phone number
        function validatePhoneNumber(phone) {
            const phoneRegex = /^(\+62|62|0)[2-9][0-9]{7,11}$/;
            return phoneRegex.test(phone);
        }

        // Enhanced form validation
        function validateCheckoutForm() {
            const customerName = document.getElementById('customerName').value.trim();
            const customerPhone = document.getElementById('customerPhone').value.trim();
            const customerAddress = document.getElementById('customerAddress').value.trim();
            const paymentMethod = document.getElementById('paymentMethod').value;
            
            const errors = [];
            
            if (!customerName || customerName.length < 2) {
                errors.push('Nama lengkap harus diisi minimal 2 karakter');
            }
            
            if (!customerPhone) {
                errors.push('Nomor telepon harus diisi');
            } else if (!validatePhoneNumber(customerPhone)) {
                errors.push('Format nomor telepon tidak valid');
            }
            
            if (!customerAddress || customerAddress.length < 10) {
                errors.push('Alamat lengkap harus diisi minimal 10 karakter');
            }
            
            if (!paymentMethod) {
                errors.push('Metode pembayaran harus dipilih');
            }
            
            return errors;
        }

        // Update process order with enhanced validation
        const originalProcessOrder = processOrder;
        processOrder = function() {
            const validationErrors = validateCheckoutForm();
            
            if (validationErrors.length > 0) {
                alert('Terdapat kesalahan pada form:\n' + validationErrors.join('\n'));
                return;
            }
            
            originalProcessOrder();
        };

        // Add some sample notifications (dalam implementasi nyata akan menggunakan WebSocket atau SSE)
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type}`;
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.zIndex = '9999';
            notification.style.maxWidth = '300px';
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }

        // Simulate real-time stock updates
        function simulateStockUpdates() {
            setInterval(() => {
                // Randomly update one product stock (simulasi pembelian dari user lain)
                if (Math.random() < 0.1) { // 10% chance every interval
                    const randomIndex = Math.floor(Math.random() * products.length);
                    const product = products[randomIndex];
                    
                    if (product.stock > 0) {
                        const decrease = Math.floor(Math.random() * 3) + 1; // 1-3 items
                        product.stock = Math.max(0, product.stock - decrease);
                        
                        if (currentPage === 'catalog') {
                            loadProducts();
                        }
                        
                        if (product.stock < 5 && product.stock > 0) {
                            showNotification(`⚠️ Stok ${product.name} hampir habis! Tersisa ${product.stock} unit`, 'warning');
                        }
                    }
                }
            }, 10000); // Check every 10 seconds
        }

        // Start stock simulation after a delay
        setTimeout(simulateStockUpdates, 5000);
    }