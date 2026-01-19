<?php
require_once __DIR__ . '/includes/auth_check.php';

// Demo mode check - if no database, show demo data
$demoMode = false;
$orders = [];
$stats = ['total' => 0, 'pending' => 0, 'confirmed' => 0, 'processing' => 0, 'shipped' => 0, 'delivered' => 0, 'completed' => 0, 'cancelled' => 0];

try {
    require __DIR__ . '/../config/database.php';
    
    // Fetch orders
    $stmt = $pdo->query('SELECT o.id, o.order_key, o.name, o.phone, o.address, o.quantity, o.total_amount, o.status, o.created_at, p.name AS product_name FROM orders o LEFT JOIN products p ON o.product_id = p.id ORDER BY o.created_at DESC LIMIT 500');
    $orders = $stmt->fetchAll();
    
    // Calculate stats
    foreach ($orders as $order) {
        $stats['total']++;
        if (isset($stats[$order['status']])) {
            $stats[$order['status']]++;
        }
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
    
    $stats = ['total' => 8, 'pending' => 2, 'confirmed' => 1, 'processing' => 1, 'shipped' => 1, 'delivered' => 1, 'completed' => 1, 'cancelled' => 1];
}

$ordersJson = json_encode($orders);
$adminUser = $_SESSION['admin_user'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders — Babu Toys Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        /* Orders Page Specific Styles */
        .orders-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }
        
        .orders-header h1 {
            font-size: 28px;
            font-weight: 800;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .orders-header h1 svg {
            color: var(--primary);
        }
        
        .header-actions {
            display: flex;
            gap: 12px;
        }
        
        /* Status Pills Navigation */
        .status-pills {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            padding: 20px 24px;
            background: #fff;
            border-radius: var(--radius-lg);
            margin-bottom: 24px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-100);
        }
        
        .status-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 2px solid transparent;
            background: var(--gray-100);
            color: var(--gray-600);
        }
        
        .status-pill:hover {
            background: var(--gray-200);
        }
        
        .status-pill.active {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }
        
        .status-pill .count {
            background: rgba(255,255,255,0.2);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
        }
        
        .status-pill:not(.active) .count {
            background: var(--gray-200);
        }
        
        .status-pill.pending { --pill-color: var(--warning); }
        .status-pill.confirmed { --pill-color: var(--info); }
        .status-pill.processing { --pill-color: #8b5cf6; }
        .status-pill.shipped { --pill-color: #06b6d4; }
        .status-pill.delivered { --pill-color: var(--success); }
        .status-pill.completed { --pill-color: var(--success); }
        .status-pill.cancelled { --pill-color: var(--danger); }
        
        .status-pill.pending.active { background: var(--warning); }
        .status-pill.confirmed.active { background: var(--info); }
        .status-pill.processing.active { background: #8b5cf6; }
        .status-pill.shipped.active { background: #06b6d4; }
        .status-pill.delivered.active,
        .status-pill.completed.active { background: var(--success); }
        .status-pill.cancelled.active { background: var(--danger); }
        
        /* Search Bar */
        .search-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            background: #fff;
            border-radius: var(--radius-lg);
            margin-bottom: 24px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-100);
        }
        
        .search-bar svg {
            color: var(--gray-400);
            flex-shrink: 0;
        }
        
        .search-bar input {
            flex: 1;
            border: none;
            outline: none;
            font-size: 15px;
            font-family: inherit;
            color: var(--gray-800);
            background: transparent;
        }
        
        .search-bar input::placeholder {
            color: var(--gray-400);
        }
        
        .search-bar .search-shortcut {
            background: var(--gray-100);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            color: var(--gray-500);
            font-weight: 500;
        }
        
        /* Orders Grid View */
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 20px;
        }
        
        .order-card {
            background: #fff;
            border-radius: var(--radius-lg);
            border: 1px solid var(--gray-100);
            overflow: hidden;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .order-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
            border-color: var(--primary-light);
        }
        
        .order-card-header {
            padding: 16px 20px;
            background: linear-gradient(135deg, var(--gray-50) 0%, #fff 100%);
            border-bottom: 1px solid var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .order-card-id {
            font-weight: 700;
            color: var(--gray-900);
            font-size: 15px;
        }
        
        .order-card-id span {
            color: var(--primary);
        }
        
        .order-card-date {
            font-size: 12px;
            color: var(--gray-500);
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .order-card-body {
            padding: 20px;
        }
        
        .order-card-customer {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }
        
        .customer-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 18px;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }
        
        .customer-details h4 {
            font-size: 15px;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 2px;
        }
        
        .customer-details p {
            font-size: 13px;
            color: var(--gray-500);
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .order-card-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 16px;
        }
        
        .order-info-item {
            padding: 14px;
            background: linear-gradient(135deg, var(--gray-50) 0%, #fff 100%);
            border-radius: var(--radius-md);
            border: 1px solid var(--gray-100);
        }
        
        .order-info-item label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        
        .order-info-item span {
            font-size: 16px;
            font-weight: 700;
            color: var(--gray-900);
        }
        
        .order-info-item span.amount {
            color: var(--success);
        }
        
        .order-card-address {
            font-size: 13px;
            color: var(--gray-600);
            padding: 14px;
            background: var(--gray-50);
            border-radius: var(--radius-md);
            margin-bottom: 16px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            line-height: 1.5;
        }
        
        .order-card-address svg {
            flex-shrink: 0;
            color: var(--primary);
            margin-top: 2px;
        }
        
        .order-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 16px;
            border-top: 1px solid var(--gray-100);
        }
        
        .order-card-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }
        
        .order-card-status::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .order-card-status.pending { background: var(--warning-light); color: #92400e; }
        .order-card-status.pending::before { background: var(--warning); }
        
        .order-card-status.confirmed { background: var(--info-light); color: #1e40af; }
        .order-card-status.confirmed::before { background: var(--info); }
        
        .order-card-status.processing { background: #ede9fe; color: #5b21b6; }
        .order-card-status.processing::before { background: #8b5cf6; }
        
        .order-card-status.shipped { background: #cffafe; color: #0e7490; }
        .order-card-status.shipped::before { background: #06b6d4; }
        
        .order-card-status.delivered,
        .order-card-status.completed { background: var(--success-light); color: #065f46; }
        .order-card-status.delivered::before,
        .order-card-status.completed::before { background: var(--success); animation: none; }
        
        .order-card-status.cancelled { background: var(--danger-light); color: #991b1b; }
        .order-card-status.cancelled::before { background: var(--danger); animation: none; }
        
        .order-card-actions {
            display: flex;
            gap: 8px;
        }
        
        .order-card-actions button {
            width: 38px;
            height: 38px;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-card-view {
            background: var(--gray-100);
            color: var(--gray-600);
        }
        
        .btn-card-view:hover {
            background: var(--primary);
            color: #fff;
            transform: scale(1.05);
        }
        
        .btn-card-delete {
            background: var(--danger-light);
            color: var(--danger);
        }
        
        .btn-card-delete:hover {
            background: var(--danger);
            color: #fff;
            transform: scale(1.05);
        }
        
        /* Empty State */
        .empty-orders {
            text-align: center;
            padding: 80px 40px;
            background: #fff;
            border-radius: var(--radius-lg);
            border: 2px dashed var(--gray-200);
        }
        
        .empty-orders svg {
            color: var(--gray-300);
            margin-bottom: 20px;
        }
        
        .empty-orders h3 {
            font-size: 20px;
            font-weight: 700;
            color: var(--gray-700);
            margin-bottom: 8px;
        }
        
        .empty-orders p {
            color: var(--gray-500);
            font-size: 15px;
        }
        
        /* Loading Skeleton */
        .skeleton-card {
            background: #fff;
            border-radius: var(--radius-lg);
            padding: 20px;
            border: 1px solid var(--gray-100);
        }
        
        .skeleton {
            background: linear-gradient(90deg, var(--gray-100) 25%, var(--gray-50) 50%, var(--gray-100) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: var(--radius-sm);
        }
        
        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .orders-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .orders-header h1 {
                font-size: 22px;
            }
            
            .header-actions {
                width: 100%;
            }
            
            .header-actions .btn {
                flex: 1;
            }
            
            .status-pills {
                padding: 12px 16px;
                gap: 8px;
                overflow-x: auto;
                flex-wrap: nowrap;
                -webkit-overflow-scrolling: touch;
            }
            
            .status-pill {
                padding: 8px 14px;
                font-size: 12px;
                white-space: nowrap;
                flex-shrink: 0;
            }
            
            .search-bar {
                padding: 12px 16px;
            }
            
            .search-bar .search-shortcut {
                display: none;
            }
            
            .orders-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .order-card-body {
                padding: 16px;
            }
            
            .order-card-info {
                grid-template-columns: 1fr;
            }
        }
    </style>
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
                    <a href="dashboard.php" class="nav-item">
                        <span class="nav-item-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        </span>
                        Dashboard
                    </a>
                    <a href="orders.php" class="nav-item active">
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
                    <h1 class="page-title">Orders</h1>
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
                </div>
                <?php endif; ?>
                
                <!-- Page Header -->
                <div class="orders-header">
                    <h1>
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        Order Management
                    </h1>
                    <div class="header-actions">
                        <button class="btn btn-secondary" onclick="loadOrders()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                            Refresh
                        </button>
                        <button class="btn btn-primary" onclick="exportOrders()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Export
                        </button>
                    </div>
                </div>
                
                <!-- Status Pills -->
                <div class="status-pills">
                    <button class="status-pill active" data-status="" onclick="filterByStatus('')">
                        All Orders
                        <span class="count" id="countAll"><?= $stats['total'] ?></span>
                    </button>
                    <button class="status-pill pending" data-status="pending" onclick="filterByStatus('pending')">
                        Pending
                        <span class="count" id="countPending"><?= $stats['pending'] ?></span>
                    </button>
                    <button class="status-pill confirmed" data-status="confirmed" onclick="filterByStatus('confirmed')">
                        Confirmed
                        <span class="count" id="countConfirmed"><?= $stats['confirmed'] ?></span>
                    </button>
                    <button class="status-pill processing" data-status="processing" onclick="filterByStatus('processing')">
                        Processing
                        <span class="count" id="countProcessing"><?= $stats['processing'] ?></span>
                    </button>
                    <button class="status-pill shipped" data-status="shipped" onclick="filterByStatus('shipped')">
                        Shipped
                        <span class="count" id="countShipped"><?= $stats['shipped'] ?></span>
                    </button>
                    <button class="status-pill delivered" data-status="delivered" onclick="filterByStatus('delivered')">
                        Delivered
                        <span class="count" id="countDelivered"><?= $stats['delivered'] ?></span>
                    </button>
                    <button class="status-pill cancelled" data-status="cancelled" onclick="filterByStatus('cancelled')">
                        Cancelled
                        <span class="count" id="countCancelled"><?= $stats['cancelled'] ?></span>
                    </button>
                </div>
                
                <!-- Search Bar -->
                <div class="search-bar">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="searchInput" placeholder="Search by customer name, phone, order ID, or address...">
                    <span class="search-shortcut">Ctrl+K</span>
                </div>
                
                <!-- Orders Grid -->
                <div class="orders-grid" id="ordersGrid">
                    <!-- Orders will be rendered here -->
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
                <select class="status-select" id="modalStatusSelect" onchange="updateStatusFromModal()">
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
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
        let currentFilter = '';
        
        document.addEventListener('DOMContentLoaded', function() {
            renderOrders(allOrders);
            
            // Search input
            document.getElementById('searchInput').addEventListener('input', debounce(filterOrders, 300));
            
            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeModal();
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    document.getElementById('searchInput').focus();
                }
            });
        });
        
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func(...args), wait);
            };
        }
        
        function filterByStatus(status) {
            currentFilter = status;
            
            // Update pills
            document.querySelectorAll('.status-pill').forEach(pill => {
                pill.classList.remove('active');
                if (pill.dataset.status === status) {
                    pill.classList.add('active');
                }
            });
            
            filterOrders();
        }
        
        function filterOrders() {
            const searchQuery = document.getElementById('searchInput').value.toLowerCase().trim();
            
            let filtered = allOrders;
            
            if (currentFilter) {
                filtered = filtered.filter(o => o.status === currentFilter);
            }
            
            if (searchQuery) {
                filtered = filtered.filter(o => 
                    o.name.toLowerCase().includes(searchQuery) ||
                    o.phone.includes(searchQuery) ||
                    o.order_key.toLowerCase().includes(searchQuery) ||
                    o.address.toLowerCase().includes(searchQuery) ||
                    o.id.toString().includes(searchQuery)
                );
            }
            
            renderOrders(filtered);
        }
        
        function renderOrders(orders) {
            const grid = document.getElementById('ordersGrid');
            
            if (orders.length === 0) {
                grid.innerHTML = `
                    <div class="empty-orders" style="grid-column: 1 / -1;">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        <h3>No orders found</h3>
                        <p>Try adjusting your search or filter criteria</p>
                    </div>
                `;
                return;
            }
            
            grid.innerHTML = orders.map(order => `
                <div class="order-card" onclick="viewOrder(${order.id})">
                    <div class="order-card-header">
                        <div class="order-card-id">Order <span>#${order.id}</span></div>
                        <div class="order-card-date">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            ${formatDate(order.created_at)}
                        </div>
                    </div>
                    <div class="order-card-body">
                        <div class="order-card-customer">
                            <div class="customer-avatar">${order.name.charAt(0).toUpperCase()}</div>
                            <div class="customer-details">
                                <h4>${escapeHtml(order.name)}</h4>
                                <p>
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    ${escapeHtml(order.phone)}
                                </p>
                            </div>
                        </div>
                        <div class="order-card-info">
                            <div class="order-info-item">
                                <label>Quantity</label>
                                <span>${order.quantity} items</span>
                            </div>
                            <div class="order-info-item">
                                <label>Total Amount</label>
                                <span class="amount">৳${formatNumber(order.total_amount)}</span>
                            </div>
                        </div>
                        <div class="order-card-address">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <span>${escapeHtml(order.address)}</span>
                        </div>
                        <div class="order-card-footer">
                            <span class="order-card-status ${order.status}">${order.status}</span>
                            <div class="order-card-actions" onclick="event.stopPropagation()">
                                <button class="btn-card-view" onclick="viewOrder(${order.id})" title="View Details">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                                <button class="btn-card-delete" onclick="deleteOrder(${order.id})" title="Delete">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
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
                    updateCounts();
                    filterOrders();
                    showToast('Orders refreshed successfully', 'success');
                } else {
                    showToast('Failed to load orders', 'error');
                }
            } catch (error) {
                showToast('Error loading orders', 'error');
            }
        }
        
        function updateCounts() {
            const counts = {
                all: allOrders.length,
                pending: 0, confirmed: 0, processing: 0, shipped: 0, delivered: 0, completed: 0, cancelled: 0
            };
            
            allOrders.forEach(order => {
                if (counts[order.status] !== undefined) counts[order.status]++;
            });
            
            document.getElementById('countAll').textContent = counts.all;
            document.getElementById('countPending').textContent = counts.pending;
            document.getElementById('countConfirmed').textContent = counts.confirmed;
            document.getElementById('countProcessing').textContent = counts.processing;
            document.getElementById('countShipped').textContent = counts.shipped;
            document.getElementById('countDelivered').textContent = counts.delivered;
            document.getElementById('countCancelled').textContent = counts.cancelled;
        }
        
        function viewOrder(orderId) {
            const order = allOrders.find(o => o.id == orderId);
            if (!order) return;
            
            currentOrderId = orderId;
            
            document.getElementById('orderModalBody').innerHTML = `
                <div class="detail-grid">
                    <div class="detail-row"><div class="detail-label">Order ID</div><div class="detail-value"><strong>#${order.id}</strong></div></div>
                    <div class="detail-row"><div class="detail-label">Order Key</div><div class="detail-value"><code style="background:#f3f4f6;padding:4px 8px;border-radius:4px">${escapeHtml(order.order_key)}</code></div></div>
                    <div class="detail-row"><div class="detail-label">Customer</div><div class="detail-value">${escapeHtml(order.name)}</div></div>
                    <div class="detail-row"><div class="detail-label">Phone</div><div class="detail-value"><a href="tel:${order.phone}">${escapeHtml(order.phone)}</a></div></div>
                    <div class="detail-row"><div class="detail-label">Address</div><div class="detail-value">${escapeHtml(order.address)}</div></div>
                    <div class="detail-row"><div class="detail-label">Product</div><div class="detail-value">${escapeHtml(order.product_name || 'N/A')}</div></div>
                    <div class="detail-row"><div class="detail-label">Quantity</div><div class="detail-value">${order.quantity}</div></div>
                    <div class="detail-row"><div class="detail-label">Total</div><div class="detail-value"><strong style="font-size:18px;color:#059669">৳${formatNumber(order.total_amount)}</strong></div></div>
                    <div class="detail-row"><div class="detail-label">Date</div><div class="detail-value">${formatDate(order.created_at, true)}</div></div>
                </div>
            `;
            
            document.getElementById('modalStatusSelect').value = order.status;
            document.getElementById('orderModal').classList.add('active');
        }
        
        async function updateStatusFromModal() {
            const newStatus = document.getElementById('modalStatusSelect').value;
            await updateStatus(currentOrderId, newStatus);
        }
        
        async function updateStatus(orderId, newStatus) {
            if (DEMO_MODE) {
                const order = allOrders.find(o => o.id == orderId);
                if (order) {
                    order.status = newStatus;
                    updateCounts();
                    filterOrders();
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
                    updateCounts();
                    filterOrders();
                    showToast(`Order #${orderId} updated`, 'success');
                } else {
                    showToast(data.message || 'Failed to update', 'error');
                }
            } catch (error) {
                showToast('Error updating status', 'error');
            }
        }
        
        async function deleteOrder(orderId) {
            if (!confirm(`Delete Order #${orderId}? This cannot be undone.`)) return;
            
            if (DEMO_MODE) {
                allOrders = allOrders.filter(o => o.id != orderId);
                updateCounts();
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
                    updateCounts();
                    filterOrders();
                    showToast(`Order #${orderId} deleted`, 'success');
                } else {
                    showToast(data.message || 'Failed to delete', 'error');
                }
            } catch (error) {
                showToast('Error deleting order', 'error');
            }
        }
        
        function printOrder() {
            if (!currentOrderId) return;
            const order = allOrders.find(o => o.id == currentOrderId);
            if (!order) return;
            
            const w = window.open('', '_blank');
            w.document.write(`<!DOCTYPE html><html><head><title>Order #${order.id}</title><style>body{font-family:Arial,sans-serif;padding:40px;max-width:600px;margin:0 auto}h1{border-bottom:2px solid #333;padding-bottom:10px}.row{display:flex;padding:10px 0;border-bottom:1px solid #eee}.label{width:140px;font-weight:bold;color:#666}.total{font-size:24px;color:#059669;font-weight:bold}@media print{body{padding:20px}}</style></head><body><h1>Babu Toys - Order #${order.id}</h1><div class="row"><div class="label">Customer:</div><div>${order.name}</div></div><div class="row"><div class="label">Phone:</div><div>${order.phone}</div></div><div class="row"><div class="label">Address:</div><div>${order.address}</div></div><div class="row"><div class="label">Product:</div><div>${order.product_name||'N/A'}</div></div><div class="row"><div class="label">Quantity:</div><div>${order.quantity}</div></div><div class="row"><div class="label">Total:</div><div class="total">৳${formatNumber(order.total_amount)}</div></div><div class="row"><div class="label">Status:</div><div>${order.status}</div></div></body></html>`);
            w.document.close();
            w.print();
        }
        
        function exportOrders() {
            const csv = [
                ['Order ID', 'Order Key', 'Customer', 'Phone', 'Address', 'Quantity', 'Total', 'Status', 'Date'],
                ...allOrders.map(o => [o.id, o.order_key, o.name, o.phone, `"${o.address}"`, o.quantity, o.total_amount, o.status, o.created_at])
            ].map(row => row.join(',')).join('\n');
            
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `orders-${new Date().toISOString().split('T')[0]}.csv`;
            a.click();
            URL.revokeObjectURL(url);
            showToast('Orders exported successfully', 'success');
        }
        
        function closeModal() {
            document.getElementById('orderModal').classList.remove('active');
            currentOrderId = null;
        }
        
        document.getElementById('orderModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
        
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('overlay').classList.toggle('active');
        }
        
        function showToast(message, type = 'info') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            const icons = { success: '✓', error: '✕', warning: '!', info: 'i' };
            toast.innerHTML = `<span class="toast-icon">${icons[type]||icons.info}</span><span>${escapeHtml(message)}</span>`;
            container.appendChild(toast);
            setTimeout(() => { toast.classList.add('hiding'); setTimeout(() => toast.remove(), 300); }, 3000);
        }
        
        function formatNumber(num) {
            return parseFloat(num).toLocaleString('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        
        function formatDate(dateStr, full = false) {
            const date = new Date(dateStr);
            return full ? date.toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        }
        
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>