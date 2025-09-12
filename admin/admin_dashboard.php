<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Restaurant Admin Dashboard</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #f8f9fa;
      color: #333;
    }

    /* Main Content */
    .main {
      margin-left: 240px;
      /* Sidebar width */
      padding: 20px;
    }

    .header {
      background: #fff;
      padding: 15px 20px;
      border-left: 6px solid #c0392b;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .header h1 {
      margin: 0;
      font-size: 24px;
      color: #c0392b;
    }

    .cards {
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      gap: 20px;
    }

    .card {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      text-align: center;
      border-top: 5px solid #c0392b;
      transition: all 0.3s ease;
    }

    .card:hover {
      transform: translateY(-5px) scale(1.05);
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
      cursor: pointer;
    }

    .card h3 {
      margin: 10px 0;
      font-size: 22px;
      color: #c0392b;
    }

    .card p {
      font-size: 16px;
      color: #555;
    }
  </style>
</head>

<body>

  <!-- Sidebar -->
  <?php include __DIR__ . "/admin_sidebar.php"; ?>

  <!-- Main Content -->
  <div class="main">
    <div class="header">
      <h1>Dashboard Overview</h1>
    </div>

    <div class="cards">
      <div class="card">
        <?php
        include "../DataBase.php";
        $customer_count = 0;
        if ($con) {
          $sql = "SELECT COUNT(*) AS total FROM register";
          $result = mysqli_query($con, $sql);
          if ($result) {
            $row = mysqli_fetch_assoc($result);
            $customer_count = $row['total'];
          }
        }
        ?>
        <h3><?php echo $customer_count; ?></h3>
        <p>Total Customers</p>
      </div>

      <div class="card">
        <?php
        include "../DataBase.php";
        $reservation_count = 0;
        if ($con) {
          $sql = "SELECT COUNT(*) AS total FROM reservations";
          $result = mysqli_query($con, $sql);
          if ($result) {
            $row = mysqli_fetch_assoc($result);
            $reservation_count = $row['total'];
          }
        }
        ?>
        <h3><?php echo $reservation_count; ?></h3>
        <p>Total Reservations</p>
      </div>

      <div class="card">
        <?php
        include "../DataBase.php";
        $active_reservations = 0;
        if ($con) {
          $sql = "SELECT COUNT(*) AS total FROM reservations WHERE status='active'";
          $result = mysqli_query($con, $sql);
          if ($result) {
            $row = mysqli_fetch_assoc($result);
            $active_reservations = $row['total'];
          }
        }
        ?>
        <h3><?php echo $active_reservations; ?></h3>
        <p>Active Reservations</p>
      </div>

      <div class="card">
        <h3>0</h3>
        <p>Total Orders</p>
      </div>
      <div class="card">
        <h3>0</h3>
        <p>Total Sales</p>
      </div>
      <div class="card">
        <h3>0</h3>
        <p>Pending Orders</p>
      </div>
      <div class="card">
        <h3>0</h3>
        <p>Completed Orders</p>
      </div>
    </div>
  </div>

</body>

</html>