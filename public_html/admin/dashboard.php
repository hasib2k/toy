<?php
require_once __DIR__ . '/includes/auth_check.php';

// Demo mode check - if no database, show demo data
$demoMode = false;
$orders = [];
$stats = ['total' => 0, 'pending' => 0, 'completed' => 0, 'cancelled' => 0, 'revenue' => 0];

try {
    require __DIR__ . '/../config/database.php';
    
    // Fetch orders
    $stmt = $pdo->query('SELECT o.id, o.order_key, o.name, o.phone, o.address, o.quantity, o.total_amount, o.status, o.created_at, p.name AS product_name FROM orders o LEFT JOIN products p ON o.product_id = p.id ORDER BY o.created_at DESC LIMIT 500');
    $orders = $stmt->fetchAll();
    
    // Calculate stats
    foreach ($orders as $order) {
        $stats['total']++;
        $stats['revenue'] += (float)$order['total_amount'];
        if ($order['status'] === 'pending') $stats['pending']++;
        if (in_array($order['status'], ['completed', 'delivered'])) $stats['completed']++;
        if ($order['status'] === 'cancelled') $stats['cancelled']++;
    }
} catch (Exception $e) {
    // Enable demo mode with sample data
    $demoMode = true;
    $orders = [
        ['id' => 1, 'order_key' => 'ORD-2026-001', 'name' => 'Rafiq Ahmed', 'phone' => '01712345678', 'address' => 'House 12, Road 5, Dhanmondi, Dhaka', 'quantity' => 2, 'total_amount' => 1980.00, 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour')), 'product_name' => 'Interactive Learning Book'],
        ['id' => 2, 'order_key' => 'ORD-2026-002', 'name' => 'Fatima Begum', 'phone' => '01898765432', 'address' => 'Apt 4B, Green Tower, Gulshan-2, Dhaka', 'quantity' => 1, 'total_amount' => 990.00, 'status' => 'confirmed', 'created_at' => date('Y-m-d H:i:s', strtotime('-3 hours')), 'product_name' => 'Interactive Learning Book'],
        ['id' => 3, 'order_key' => 'ORD-2026-003', 'name' => 'Kamal Hossain', 'phone' => '01556789012', 'address' => '45 Station Road, Chattogram', 'quantity' => 3, 'total_amount' => 2970.00, 'status' => 'processing', 'created_at' => date('Y-m-d H:i:s', strtotime('-5 hours')), 'product_name' => 'Interactive Learning Book'],
        ['id' => 4, 'order_key' => 'ORD-2026-004', 'name' => 'Nasrin Akter', 'phone' => '01634567890', 'address' => '78 College Road, Rajshahi', 'quantity' => 1, 'total_amount' => 990.00, 'status' => 'shipped', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')), 'product_name' => 'Interactive Learning Book'],
        ['id' => 5, 'order_key' => 'ORD-2026-005', 'name' => 'Abdul Rahman', 'phone' => '01923456789', 'address' => 'House 23, Sector 7, Uttara, Dhaka', 'quantity' => 2, 'total_amount' => 1980.00, 'status' => 'delivered', 'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')), 'product_name' => 'Interactive Learning Book'],
        ['id' => 6, 'order_key' => 'ORD-2026-006', 'name' => 'Shirin Sultana', 'phone' => '01812345678', 'address' => '12 New Market, Sylhet', 'quantity' => 1, 'total_amount' => 990.00, 'status' => 'completed', 'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')), 'product_name' => 'Interactive Learning Book'],
        ['id' => 7, 'order_key' => 'ORD-2026-007', 'name' => 'Mizanur Rahman', 'phone' => '01745678901', 'address' => '56 Main Road, Khulna', 'quantity' => 1, 'total_amount' => 990.00, 'status' => 'cancelled', 'created_at' => date('Y-m-d H:i:s', strtotime('-4 days')), 'product_name' => 'Interactive Learning Book'],
        ['id' => 8, 'order_key' => 'ORD-2026-008', 'name' => 'Ayesha Khan', 'phone' => '01678901234', 'address' => 'Flat 6C, Marina Heights, Banani, Dhaka', 'quantity' => 4, 'total_amount' => 3960.00, 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s', strtotime('-30 minutes')), 'product_name' => 'Interactive Learning Book'],
    ];
    
    $stats = ['total' => 8, 'pending' => 2, 'completed' => 2, 'cancelled' => 1, 'revenue' => 14850.00];
}

$ordersJson = json_encode($orders);
$adminUser = $_SESSION['admin_user'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Babu Toys Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-logo">
                    <div class="sidebar-logo-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    </div>
                    <div>
                        <div class="sidebar-logo-text">Babu Toys</div>
                        <div class="sidebar-logo-sub">Admin Panel</div>
                    </div>
                </a>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main</div>
                    <a href="dashboard.php" class="nav-item active">
                        <span class="nav-item-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        </span>
                        Dashboard
                    </a>
                    <a href="orders.php" class="nav-item">
                        <span class="nav-item-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        </span>
                        Orders
                        <?php if ($stats['pending'] > 0): ?>
                        <span class="nav-item-badge"><?= $stats['pending'] ?></span>
                        <?php endif; ?>
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Quick Actions</div>
                    <a href="../" target="_blank" class="nav-item">
                        <span class="nav-item-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        </span>
                        View Store
                    </a>
                </div>
            </nav>
        </aside>
        
        <!-- Overlay for mobile -->
        <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <header class="topbar">
                <div class="topbar-left">
                    <button class="menu-toggle" onclick="toggleSidebar()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                    </button>
                    <h1 class="page-title">Dashboard</h1>
                </div>
                
                <div class="topbar-right">
                    <div class="topbar-user">
                        <div class="user-avatar"><?= strtoupper(substr($adminUser, 0, 1)) ?></div>
                        <div class="user-info">
                            <div class="user-name"><?= htmlspecialchars($adminUser) ?></div>
                            <div class="user-role">Administrator</div>
                        </div>
                    </div>
                    <a href="logout.php" class="btn-logout">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        <span>Logout</span>
                    </a>
                </div>
            </header>
            
            <!-- Content Area -->
            <div class="content-area">
                <?php if ($demoMode): ?>
                <div class="demo-banner">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <strong>Demo Mode:</strong> Database not connected. Showing sample data. 
                    <a href="#" onclick="alert('Configure your database in config/database.php and run db/sql/schema.sql'); return false;">Setup Guide</a>
                </div>
                <?php endif; ?>
                
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card primary">
                        <div class="stat-card-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-title">Total Orders</div>
                            <div class="stat-card-value" id="statTotal"><?= $stats['total'] ?></div>
                        </div>
                    </div>
                    
                    <div class="stat-card warning">
                        <div class="stat-card-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-title">Pending</div>
                            <div class="stat-card-value" id="statPending"><?= $stats['pending'] ?></div>
                        </div>
                    </div>
                    
                    <div class="stat-card success">
                        <div class="stat-card-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-title">Completed</div>
                            <div class="stat-card-value" id="statCompleted"><?= $stats['completed'] ?></div>
                        </div>
                    </div>
                    
                    <div class="stat-card danger">
                        <div class="stat-card-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-title">Cancelled</div>
                            <div class="stat-card-value" id="statCancelled"><?= $stats['cancelled'] ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Orders Card -->
                <div class="card" id="orders-section">
                    <div class="card-header">
                        <h2 class="card-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                            Recent Orders
                        </h2>
                        
                        <div class="filters-bar">
                            <div class="filter-group">
                                <label for="statusFilter">Status:</label>
                                <select id="statusFilter" class="filter-select">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="processing">Processing</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label for="searchInput">Search:</label>
                                <input type="text" id="searchInput" class="filter-input" placeholder="Name, phone, or order key...">
                            </div>
                            
                            <button class="btn btn-primary" onclick="loadOrders()">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                                Refresh
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Address</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="ordersBody">
                                <tr>
                                    <td colspan="8">
                                        <div class="loading-state">
                                            <div class="loading-spinner"></div>
                                            <p>Loading orders...</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Order Detail Modal -->
    <div class="modal-overlay" id="orderModal">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title">Order Details</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="orderModalBody"></div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal()">Close</button>
                <button class="btn btn-primary" onclick="printOrder()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Print
                </button>
            </div>
        </div>
    </div>
    
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>
    
    <script>
        const DEMO_MODE = <?= $demoMode ? 'true' : 'false' ?>;
        let allOrders = <?= $ordersJson ?>;
        let currentOrderId = null;
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            renderOrders(allOrders);
            document.getElementById('statusFilter').addEventListener('change', filterOrders);
            document.getElementById('searchInput').addEventListener('input', debounce(filterOrders, 300));
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeModal();
            });
        });
        
        // Toggle sidebar for mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        }
        
        // Debounce function for search
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func(...args), wait);
            };
        }
        
        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Format number with commas
        function formatNumber(num) {
            return parseFloat(num).toLocaleString('en-BD');
        }
        
        // Format date
        function formatDate(dateStr, full = false) {
            const date = new Date(dateStr);
            if (full) {
                return date.toLocaleString('en-BD', { 
                    year: 'numeric', month: 'short', day: 'numeric',
                    hour: '2-digit', minute: '2-digit'
                });
            }
            return date.toLocaleDateString('en-BD', { month: 'short', day: 'numeric', year: 'numeric' });
        }
        
        // Show toast notification
        function showToast(message, type = 'info') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `
                <span>${escapeHtml(message)}</span>
                <button onclick="this.parentElement.remove()">&times;</button>
            `;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
        }
        
        // Close modal
        function closeModal() {
            document.getElementById('orderModal').classList.remove('active');
            currentOrderId = null;
        }
        
        // Load orders from API
        async function loadOrders() {
            if (DEMO_MODE) { 
                showToast('Demo mode: Using sample data', 'info'); 
                return; 
            }
            try {
                const response = await fetch('/api/orders.php');
                const data = await response.json();
                if (data.success) {
                    allOrders = data.orders;
                    updateStats();
                    filterOrders();
                    showToast('Orders refreshed', 'success');
                } else {
                    showToast('Failed to load orders', 'error');
                }
            } catch (error) {
                showToast('Error loading orders', 'error');
            }
        }
        
        // Update stats display
        function updateStats() {
            document.getElementById('statTotal').textContent = allOrders.length;
            document.getElementById('statPending').textContent = allOrders.filter(o => o.status === 'pending').length;
            document.getElementById('statCompleted').textContent = allOrders.filter(o => ['completed', 'delivered'].includes(o.status)).length;
            document.getElementById('statCancelled').textContent = allOrders.filter(o => o.status === 'cancelled').length;
        }
        
        // Filter orders by status and search
        function filterOrders() {
            const statusFilter = document.getElementById('statusFilter').value;
            const searchQuery = document.getElementById('searchInput').value.toLowerCase().trim();
            let filtered = allOrders;
            if (statusFilter) filtered = filtered.filter(o => o.status === statusFilter);
            if (searchQuery) filtered = filtered.filter(o => 
                o.name.toLowerCase().includes(searchQuery) ||
                o.phone.includes(searchQuery) ||
                o.order_key.toLowerCase().includes(searchQuery) ||
                o.address.toLowerCase().includes(searchQuery)
            );
            renderOrders(filtered);
        }
        
        // Render orders table
        function renderOrders(orders) {
            const tbody = document.getElementById('ordersBody');
            if (orders.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                                        <line x1="3" y1="6" x2="21" y2="6"/>
                                        <path d="M16 10a4 4 0 0 1-8 0"/>
                                    </svg>
                                </div>
                                <div class="empty-title">No orders found</div>
                                <p>Try adjusting your filters or create a new order from the store</p>
                            </div>
                        </td>
                    </tr>`;
                return;
            }
            
            tbody.innerHTML = orders.map(order => `
                <tr data-id="${order.id}">
                    <td>
                        <div class="order-id">#${order.id}</div>
                        <div class="order-key">${escapeHtml(order.order_key)}</div>
                    </td>
                    <td>
                        <div class="customer-info">
                            <div class="customer-name">${escapeHtml(order.name)}</div>
                            <div class="customer-phone">${escapeHtml(order.phone)}</div>
                        </div>
                    </td>
                    <td>
                        <div class="address-cell" title="${escapeHtml(order.address)}">${escapeHtml(order.address)}</div>
                    </td>
                    <td><strong>${order.quantity}</strong></td>
                    <td><span class="amount">৳${formatNumber(order.total_amount)}</span></td>
                    <td>
                        <select class="status-select status-${order.status}" onchange="updateStatus(${order.id}, this.value)">
                            <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>Pending</option>
                            <option value="confirmed" ${order.status === 'confirmed' ? 'selected' : ''}>Confirmed</option>
                            <option value="processing" ${order.status === 'processing' ? 'selected' : ''}>Processing</option>
                            <option value="shipped" ${order.status === 'shipped' ? 'selected' : ''}>Shipped</option>
                            <option value="delivered" ${order.status === 'delivered' ? 'selected' : ''}>Delivered</option>
                            <option value="completed" ${order.status === 'completed' ? 'selected' : ''}>Completed</option>
                            <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                        </select>
                    </td>
                    <td><div class="date-cell">${formatDate(order.created_at)}</div></td>
                    <td>
                        <div class="actions-cell">
                            <button class="btn-action btn-view" onclick="viewOrder(${order.id})">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                View
                            </button>
                            <button class="btn-action btn-delete" onclick="deleteOrder(${order.id})">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }
        
        // Update order status
        async function updateStatus(orderId, newStatus) {
            if (DEMO_MODE) {
                const order = allOrders.find(o => o.id == orderId);
                if (order) { 
                    order.status = newStatus; 
                    updateStats(); 
                    showToast(`Order #${orderId} updated to ${newStatus}`, 'success'); 
                }
                return;
            }
            try {
                const response = await fetch('/api/admin/update-order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_id: orderId, status: newStatus })
                });
                const data = await response.json();
                if (data.success) {
                    const order = allOrders.find(o => o.id == orderId);
                    if (order) order.status = newStatus;
                    updateStats();
                    showToast(`Order #${orderId} updated to ${newStatus}`, 'success');
                } else {
                    showToast(data.message || 'Failed to update', 'error');
                    loadOrders();
                }
            } catch (error) {
                showToast('Error updating status', 'error');
            }
        }
        
        // Delete order
        async function deleteOrder(orderId) {
            if (!confirm(`Delete Order #${orderId}? This cannot be undone.`)) return;
            
            if (DEMO_MODE) {
                allOrders = allOrders.filter(o => o.id != orderId);
                updateStats(); 
                filterOrders();
                showToast(`Order #${orderId} deleted`, 'success');
                return;
            }
            try {
                const response = await fetch('/api/admin/delete-order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_id: orderId })
                });
                const data = await response.json();
                if (data.success) {
                    allOrders = allOrders.filter(o => o.id != orderId);
                    updateStats(); 
                    filterOrders();
                    showToast(`Order #${orderId} deleted`, 'success');
                } else {
                    showToast(data.message || 'Failed to delete', 'error');
                }
            } catch (error) {
                showToast('Error deleting order', 'error');
            }
        }
        
        // View order details
        function viewOrder(orderId) {
            const order = allOrders.find(o => o.id == orderId);
            if (!order) return;
            currentOrderId = orderId;
            
            document.getElementById('orderModalBody').innerHTML = `
                <div class="detail-grid">
                    <div class="detail-row">
                        <div class="detail-label">Order ID</div>
                        <div class="detail-value"><strong>#${order.id}</strong></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Order Key</div>
                        <div class="detail-value"><code style="background:#f3f4f6;padding:4px 8px;border-radius:4px">${escapeHtml(order.order_key)}</code></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Customer</div>
                        <div class="detail-value">${escapeHtml(order.name)}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Phone</div>
                        <div class="detail-value"><a href="tel:${order.phone}">${escapeHtml(order.phone)}</a></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Address</div>
                        <div class="detail-value">${escapeHtml(order.address)}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Product</div>
                        <div class="detail-value">${escapeHtml(order.product_name || 'N/A')}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Quantity</div>
                        <div class="detail-value">${order.quantity}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Total</div>
                        <div class="detail-value"><strong style="font-size:18px;color:#059669">৳${formatNumber(order.total_amount)}</strong></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Status</div>
                        <div class="detail-value"><span class="status-badge status-${order.status}">${order.status}</span></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Date</div>
                        <div class="detail-value">${formatDate(order.created_at, true)}</div>
                    </div>
                </div>
            `;
            document.getElementById('orderModal').classList.add('active');
        }
        
        // Print order
        function printOrder() {
            if (!currentOrderId) return;
            const order = allOrders.find(o => o.id == currentOrderId);
            if (!order) return;
            
            const w = window.open('', '_blank');
            w.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Order #${order.id}</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 40px; max-width: 600px; margin: 0 auto; }
                        h1 { border-bottom: 2px solid #333; padding-bottom: 10px; }
                        .row { display: flex; padding: 10px 0; border-bottom: 1px solid #eee; }
                        .label { width: 140px; font-weight: bold; color: #666; }
                        .total { font-size: 24px; color: #059669; font-weight: bold; }
                    </style>
                </head>
                <body>
                    <h1>Babu Toys - Order #${order.id}</h1>
                    <div class="row"><div class="label">Customer:</div><div>${escapeHtml(order.name)}</div></div>
                    <div class="row"><div class="label">Phone:</div><div>${escapeHtml(order.phone)}</div></div>
                    <div class="row"><div class="label">Address:</div><div>${escapeHtml(order.address)}</div></div>
                    <div class="row"><div class="label">Product:</div><div>${escapeHtml(order.product_name || 'N/A')}</div></div>
                    <div class="row"><div class="label">Quantity:</div><div>${order.quantity}</div></div>
                    <div class="row"><div class="label">Total:</div><div class="total">৳${formatNumber(order.total_amount)}</div></div>
                    <div class="row"><div class="label">Status:</div><div>${order.status}</div></div>
                    <div class="row"><div class="label">Date:</div><div>${formatDate(order.created_at, true)}</div></div>
                </body>
                </html>
            `);
            w.document.close();
            w.print();
        }
    </script>
</body>
</html>
