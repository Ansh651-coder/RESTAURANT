<?php
session_start();
include "DataBase.php";

ob_start(); // Prevent headers sent error

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

// Reservation submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserve_table'])) {
    $userId = $_SESSION['user_id'];
    $tableId = intval($_POST['selectedTableId']);
    $name = trim($_POST['customerName']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $date = $_POST['bookingDate'];
    $time = $_POST['bookingTime'];
    $partySize = intval($_POST['partySize']);
    $specialRequests = trim($_POST['specialRequests']);

    $pricePerSeat = 500;
    $totalAmount = $partySize * $pricePerSeat;

    // Insert reservation
    $stmt = $con->prepare("INSERT INTO reservations 
        (user_id, table_number, full_name, email, phone, booking_date, booking_time, party_size) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("SQL Prepare failed: " . $con->error);
    }
    $stmt->bind_param("iissssii", $userId, $tableId, $name, $email, $phone, $date, $time, $partySize);
    if ($stmt->execute()) {
        $reservationId = $stmt->insert_id;
        header("Location: payment.php?reservation_id=$reservationId&type=advance");
        exit();
    } else {
        $errorMsg = "⚠️ Error saving reservation: " . $stmt->error;
    }
}

// Fetch tables
$tables = $con->query("SELECT * FROM restaurant_tables")->fetch_all(MYSQLI_ASSOC);

// Fetch user reservations
$userId = $_SESSION['user_id'];
$sql = "SELECT r.*, t.table_number,
        COALESCE(SUM(CASE WHEN p.payment_type='advance' AND p.status='success' THEN p.amount END),0) as advance_paid,
        COALESCE(SUM(CASE WHEN p.payment_type='final' AND p.status='success' THEN p.amount END),0) as fully_paid
        FROM reservations r
        JOIN restaurant_tables t ON r.table_number = t.id
        LEFT JOIN payments p ON r.id = p.reservation_id
        WHERE r.user_id = ?
        GROUP BY r.id
        ORDER BY r.booking_date DESC, r.booking_time DESC";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$reservations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Table Reservation - Wok N Bowl</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
        }

        /* Navbar */
        header {
            background: linear-gradient(135deg, #e63946 0%, #c1121f 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
        }

        /* ✅ Navbar always fixed at top */
        header {
            background: linear-gradient(135deg, #e63946 0%, #c1121f 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            /* keep navbar always on top */
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 60px;
        }

        .logo {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.3rem;
        }

        .logo-icon {
            width: 35px;
            height: 35px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
            font-size: 1.2rem;
            color: #e63946;
        }

        nav ul {
            list-style: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 10px;
            border-radius: 5px;
            transition: 0.3s;
        }

        nav a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* ✅ Main wrapper: add padding so content is not hidden under fixed navbar */
        main {
            display: block;
            max-width: 1200px;
            margin: 20px auto;
            padding: 80px 20px 20px;
            /* 80px top padding = navbar height */
            text-align: left;
        }

        /* Table status */
        .table-status {
            margin: 20px auto;
            width: 100%;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .status-header h2 {
            color: #e63946;
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .status-legend {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .legend-dot.available {
            background: #28a745;
        }

        .legend-dot.occupied {
            background: #dc3545;
        }

        .legend-dot.reserved {
            background: #ffc107;
        }

        .table-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
        }

        .table-item {
            border: 2px solid;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }

        .table-item.available {
            border-color: #28a745;
            background: #f8fff9;
        }

        .table-item.occupied {
            border-color: #dc3545;
            background: #fff5f5;
            cursor: not-allowed;
        }

        .table-item.reserved {
            border-color: #ffc107;
            background: #fffbf0;
            cursor: not-allowed;
        }

        .table-item:hover:not(.occupied):not(.reserved) {
            transform: scale(1.05);
        }

        .table-number {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
        }

        .table-capacity {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 8px;
        }

        .table-status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
            color: white;
        }

        .available .table-status-badge {
            background: #28a745;
        }

        .occupied .table-status-badge {
            background: #dc3545;
        }

        .reserved .table-status-badge {
            background: #ffc107;
        }

        /* Reservation form */
        .reservation-form {
            margin: 20px auto;
            width: 100%;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            display: none;
            margin-bottom: 30px;
        }

        .reservation-form.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-header h1 {
            color: #e63946;
            font-size: 2rem;
            margin-bottom: 10px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 6px;
            display: block;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
        }

        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, #e63946, #c1121f);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }

        .submit-btn:hover {
            background: #c1121f;
        }

        /* My reservations */
        .reservation-list {
            margin: 20px auto;
            width: 100%;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .reservation-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .reservation-item a {
            margin-left: 15px;
            color: #e63946;
            font-weight: bold;
            text-decoration: none;
        }

        .reservation-item a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <header>
        <div class="nav-container">
            <a href="home.php" class="logo">
                <div class="logo-icon">🥡</div><span>Wok N Bowl</span>
            </a>
            <nav>
                <ul>
                    <li><a href="home.php#home">🏠 Home</a></li>
                    <li><a href="home.php#about">ℹ️ About Us</a></li>
                    <li><a href="menu.php">📋 Menu</a></li>
                    <li><a href="reservation.php" style="background-color: rgba(255,255,255,0.1);">📅 Reservation</a>
                    </li>
                    <li><a href="home.php#contact">📞 Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>

        <!-- Table Status -->
        <div class="table-status">
            <div class="status-header">
                <h2>Table Status</h2>
            </div>
            <div class="status-legend">
                <div class="legend-item">
                    <div class="legend-dot available"></div><span>Available</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot reserved"></div><span>Reserved</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot occupied"></div><span>Occupied</span>
                </div>
            </div>
            <div class="table-grid">
                <?php foreach ($tables as $t): ?>
                    <div class="table-item <?= $t['status'] ?>" data-id="<?= $t['id'] ?>"
                        data-table="<?= $t['table_number'] ?>" data-capacity="<?= $t['capacity'] ?>">
                        <div class="table-number"><?= $t['table_number'] ?></div>
                        <div class="table-capacity"><?= $t['capacity'] ?> seats</div>
                        <div class="table-status-badge"><?= $t['status'] ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Reservation Form -->
        <div class="reservation-form" id="reservationFormBox">
            <div class="form-header">
                <h1>📅 Table Reservation</h1>
                <p>Fill your details to reserve this table</p>
            </div>
            <?php if (!empty($successMsg))
                echo "<div class='success-message'>$successMsg</div>"; ?>
            <form method="POST">
                <input type="hidden" id="selectedTableId" name="selectedTableId">
                <div class="form-group"><label>Full Name *</label><input type="text" name="customerName" required></div>
                <div class="form-group"><label>Email *</label><input type="email" name="email" required></div>
                <div class="form-group"><label>Phone *</label><input type="tel" name="phone" required></div>
                <div class="form-group"><label>Date *</label><input type="date" name="bookingDate" required
                        min="<?= date('Y-m-d'); ?>"></div>
                <div class="form-group"><label>Time *</label>
                    <select name="bookingTime" required>
                        <option value="">Select Time</option>
                        <option value="12:00 PM">12:00 PM</option>
                        <option value="12:30 PM">12:30 PM</option>
                        <option value="6:00 PM">6:00 PM</option>
                        <option value="7:00 PM">7:00 PM</option>
                        <option value="8:00 PM">8:00 PM</option>
                        <option value="9:00 PM">9:00 PM</option>
                    </select>
                </div>
                <div class="form-group"><label>Number of People *</label>
                    <select name="partySize" required>
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?>     <?= $i == 1 ? 'Person' : 'People' ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group"><label>Selected Table</label><input type="text" id="selectedTable" readonly>
                </div>
                <div class="form-group"><label>Special Requests</label><textarea name="specialRequests"
                        rows="3"></textarea></div>
                <button type="submit" name="reserve_table" class="submit-btn">🎉 Reserve Table</button>
            </form>
        </div>

        <!-- My Reservations -->
        <div class="reservation-list">
            <h2>My Reservations</h2>
            <?php foreach ($reservations as $row):
                $pricePerSeat = 500;
                $totalAmount = $row['party_size'] * $pricePerSeat;
                $advanceRequired = $totalAmount * 0.5;
                $remainingRequired = $totalAmount - $row['advance_paid'];
                ?>
                <div class="reservation-card">
                    <h3>Table <?= $row['table_number'] ?> | <?= $row['booking_date'] ?>     <?= $row['booking_time'] ?></h3>
                    <p>Party Size: <?= $row['party_size'] ?> | Status: <?= ucfirst($row['status']) ?></p>
                    <p><b>Advance Payment:</b>
                        <?= $row['advance_paid'] ? "✅ Paid (₹$row[advance_paid])" : "❌ Pending (₹$advanceRequired)" ?></p>
                    <p><b>Final Payment:</b>
                        <?= $row['fully_paid'] ? "✅ Paid (₹$row[fully_paid])" : "❌ Pending (₹$remainingRequired)" ?></p>

                    <?php if (!$row['advance_paid']): ?>
                        <a href="payment.php?reservation_id=<?= $row['id'] ?>&type=advance" class="pay-btn">Pay Advance
                            ₹<?= $advanceRequired ?></a>
                    <?php elseif ($row['advance_paid'] && !$row['fully_paid']): ?>
                        <a href="payment.php?reservation_id=<?= $row['id'] ?>&type=final" class="pay-btn">Pay Remaining
                            ₹<?= $remainingRequired ?></a>
                    <?php else: ?>
                        <p style="color:green;"><b>✅ Fully Paid</b></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

    </main>
    <script>
        let formBox = document.getElementById("reservationFormBox");
        let tableInput = document.getElementById("selectedTable");
        let tableIdInput = document.getElementById("selectedTableId");

        document.querySelectorAll('.table-item.available').forEach(table => {
            table.addEventListener('click', function () {
                tableIdInput.value = this.dataset.id;
                tableInput.value = this.dataset.table + " (" + this.dataset.capacity + " seats)";
                formBox.classList.add("active");
                formBox.scrollIntoView({ behavior: "smooth" });
            });
        });
    </script>
</body>

</html>