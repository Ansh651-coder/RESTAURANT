<?php
session_start();
include "../DataBase.php";

// ✅ Handle Reservation Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    // Fetch table_id before update
    $stmt = $con->prepare("SELECT table_id FROM reservations WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($tableId);
    $stmt->fetch();
    $stmt->close();

    if ($action == "approve") {
        $con->query("UPDATE reservations SET status='active' WHERE id=$id");
        $con->query("UPDATE restaurant_tables SET status='reserved' WHERE id=$tableId");
    } elseif ($action == "cancel") {
        $con->query("UPDATE reservations SET status='cancelled' WHERE id=$id");
        $con->query("UPDATE restaurant_tables SET status='available' WHERE id=$tableId");
    } elseif ($action == "complete") {
        $con->query("UPDATE reservations SET status='completed' WHERE id=$id");
        $con->query("UPDATE restaurant_tables SET status='available' WHERE id=$tableId");
    } elseif ($action == "delete") {
        $con->query("DELETE FROM reservations WHERE id=$id");
    }
    header("Location: manage_reservations.php");
    exit();
}

// ✅ Handle Table Status Update
if (isset($_GET['table_action']) && isset($_GET['table_id'])) {
    $table_id = intval($_GET['table_id']);
    $new_status = $_GET['table_action'];

    $allowed = ['available', 'reserved', 'occupied'];
    if (in_array($new_status, $allowed)) {
        $stmt = $con->prepare("UPDATE restaurant_tables SET status=? WHERE id=?");
        $stmt->bind_param("si", $new_status, $table_id);
        $stmt->execute();
    }
    header("Location: manage_reservations.php");
    exit();
}

// ✅ Fetch Reservations
$sql = "SELECT r.*, t.table_number, u.fullName 
        FROM reservations r
        JOIN restaurant_tables t ON r.table_id = t.id
        JOIN register u ON r.user_id = u.id
        ORDER BY r.booking_date DESC, r.booking_time DESC";
$reservations = $con->query($sql);

// ✅ Fetch Tables
$tables = $con->query("SELECT * FROM restaurant_tables ORDER BY CAST(SUBSTRING(table_number, 2) AS UNSIGNED) ASC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Reservations</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            color: #333;
            display: flex;
            /* sidebar + content */
        }

        .main {
            margin-left: 240px;
            /* leave space for sidebar */
            padding: 20px;
            flex: 1;
            /* take remaining width */
            height: 100vh;
            /* full screen */
            overflow-y: auto;
            /* enable scrolling */
            box-sizing: border-box;
        }

        h1 {
            color: #c0392b;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background: #c0392b;
            color: white;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        a.action {
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
            margin: 2px;
            display: inline-block;
        }

        .approve {
            background: #28a745;
            color: white;
        }

        .cancel {
            background: #e74c3c;
            color: white;
        }

        .complete {
            background: #17a2b8;
            color: white;
        }

        .delete {
            background: #6c757d;
            color: white;
        }

        /* Badges */
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.85rem;
            display: inline-block;
        }

        .badge.active {
            background: #28a745;
            color: white;
        }

        /* Green */
        .badge.cancelled {
            background: #e74c3c;
            color: white;
        }

        /* Red */
        .badge.completed {
            background: #2980b9;
            color: white;
        }

        /* Blue */

        .badge.available {
            background: #27ae60;
            color: white;
        }

        /* Green */
        .badge.reserved {
            background: #f1c40f;
            color: black;
        }

        /* Yellow */
        .badge.occupied {
            background: #e74c3c;
            color: white;
        }

        /* Red */
    </style>
</head>

<body>
    <?php include "admin_sidebar.php"; ?> <!-- ✅ reuse sidebar -->
    <div class="main">
        <h1>Manage Reservations</h1>
        <table>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Table</th>
                <th>Date</th>
                <th>Time</th>
                <th>People</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $reservations->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['fullName']) ?><br><?= $row['email'] ?><br><?= $row['phone'] ?></td>
                    <td><?= $row['table_number'] ?></td>
                    <td><?= $row['booking_date'] ?></td>
                    <td><?= $row['booking_time'] ?></td>
                    <td><?= $row['party_size'] ?></td>
                    <td><span class="badge <?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                    <td>
                        <?php if ($row['status'] == "active"): ?>
                            <a class="complete action" href="?action=complete&id=<?= $row['id'] ?>">Complete</a>
                            <a class="cancel action" href="?action=cancel&id=<?= $row['id'] ?>"
                                onclick="return confirm('Cancel this reservation?')">Cancel</a>
                        <?php elseif ($row['status'] == "cancelled"): ?>
                            <a class="delete action" href="?action=delete&id=<?= $row['id'] ?>"
                                onclick="return confirm('Delete this record?')">Delete</a>
                        <?php elseif ($row['status'] == "completed"): ?>
                            <a class="delete action" href="?action=delete&id=<?= $row['id'] ?>"
                                onclick="return confirm('Delete this record?')">Delete</a>
                        <?php else: ?>
                            <a class="approve action" href="?action=approve&id=<?= $row['id'] ?>">Approve</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <h1>Manage Tables</h1>
        <table>
            <tr>
                <th>Table No</th>
                <th>Capacity</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php while ($t = $tables->fetch_assoc()): ?>
                <tr>
                    <td><?= $t['table_number'] ?></td>
                    <td><?= $t['capacity'] ?> seats</td>
                    <td><span class="badge <?= $t['status'] ?>"><?= ucfirst($t['status']) ?></span></td>
                    <td>
                        <?php if ($t['status'] == 'available'): ?>
                            <a class="action approve" href="?table_action=reserved&table_id=<?= $t['id'] ?>">Reserve</a>
                            <a class="action complete" href="?table_action=occupied&table_id=<?= $t['id'] ?>">Occupy</a>
                        <?php elseif ($t['status'] == 'reserved'): ?>
                            <a class="action approve" href="?table_action=available&table_id=<?= $t['id'] ?>">Available</a>
                            <a class="action complete" href="?table_action=occupied&table_id=<?= $t['id'] ?>">Occupy</a>
                        <?php elseif ($t['status'] == 'occupied'): ?>
                            <a class="action approve" href="?table_action=available&table_id=<?= $t['id'] ?>">Available</a>
                            <a class="action complete" href="?table_action=reserved&table_id=<?= $t['id'] ?>">Reserve</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>

</html>