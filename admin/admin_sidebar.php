<?php
// sidebar.php

// get the current file name
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    /* Sidebar Styles */
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 240px;
        height: 100%;
        background: #c0392b;
        color: #fff;
        padding-top: 20px;
        font-family: Arial, sans-serif;
    }

    .sidebar h2 {
        text-align: center;
        margin-bottom: 30px;
        font-size: 22px;
        font-weight: bold;
    }

    .sidebar a {
        display: block;
        padding: 12px 20px;
        color: #fff;
        text-decoration: none;
        transition: background 0.3s;
    }

    .sidebar a:hover {
        background: #e74c3c;
    }

    /* Highlight active page */
    .sidebar a.active {
        background: #a93226;
        font-weight: bold;
    }
</style>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="http://localhost/RESTAURANT/admin/admin_dashboard.php"
        class="<?php echo $current_page == 'admin_dashboard.php' ? 'active' : ''; ?>">Dashboard</a>

    <a href="http://localhost/RESTAURANT/admin/manage_menu.php"
        class="<?php echo $current_page == 'manage_menu.php' ? 'active' : ''; ?>">Manage Menu</a>

    <a href="http://localhost/RESTAURANT/admin/manage_reservations.php"
        class="<?php echo $current_page == 'reservations.php' ? 'active' : ''; ?>">Reservations</a>

    <a href="http://localhost/RESTAURANT/admin/orders.php"
        class="<?php echo $current_page == 'orders.php' ? 'active' : ''; ?>">Orders</a>

    <a href="http://localhost/RESTAURANT/admin/customers.php"
        class="<?php echo $current_page == 'customers.php' ? 'active' : ''; ?>">Customers</a>

    <a href="http://localhost/RESTAURANT/admin/staff.php"
        class="<?php echo $current_page == 'staff.php' ? 'active' : ''; ?>">Staff</a>

    <a href="http://localhost/RESTAURANT/admin/payments.php"
        class="<?php echo $current_page == 'payments.php' ? 'active' : ''; ?>">Payments</a>

    <a href="http://localhost/RESTAURANT/admin/reports.php"
        class="<?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">Reports</a>

    <a href="http://localhost/RESTAURANT/admin/homepage.php">Logout</a>
</div>