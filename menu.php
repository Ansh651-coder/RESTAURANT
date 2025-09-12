<?php

// -------------------- DB CONNECTION --------------------
$host = "localhost";
$user = "root";
$pass = "";
$db = "restaurant";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// -------------------- FETCH CATEGORIES --------------------
$cats = [];
$catResult = $conn->query("SELECT id, name, slug, COALESCE(description,'') AS description FROM menu_categories ORDER BY id ASC");
if ($catResult) {
    $cats = $catResult->fetch_all(MYSQLI_ASSOC);
} else {
    die("Error fetching categories: " . $conn->error);
}

// -------------------- FETCH MENU ITEMS --------------------
$items = [];
$itemResult = $conn->query("SELECT id, category_id, name, course, price, COALESCE(description,'') AS description,
                                   COALESCE(image,'') AS image, is_available 
                            FROM menu_items 
                            ORDER BY category_id, FIELD(course,'Starter','Main Course','Dessert'), name");
if ($itemResult) {
    $items = $itemResult->fetch_all(MYSQLI_ASSOC);
} else {
    die("Error fetching menu items: " . $conn->error);
}

// -------------------- GROUP ITEMS BY CATEGORY --------------------
$byCat = [];
foreach ($items as $it) {
    $byCat[$it['category_id']][] = $it;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Our Menu - Wok N Bowl</title>
    <style>
        :root {
            --brand: #e63946;
            --brand-dark: #c1121f;
            --muted: #f8f9fa;
            --text: #333;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
            line-height: 1.6
        }

        header {
            background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .1)
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 60px
        }

        .logo {
            display: flex;
            align-items: center;
            color: #fff;
            text-decoration: none;
            font-weight: 700
        }

        .logo-icon {
            width: 35px;
            height: 35px;
            background: #fff;
            color: var(--brand);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 8px;
            align-items: center
        }

        nav a {
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 10px;
            border-radius: 6px
        }

        nav a:hover {
            background: rgba(255, 255, 255, .1)
        }

        /* Menu page */
        .menu-hero {
            padding: 60px 20px;
            background: #fff;
            text-align: center;
            border-bottom: 1px solid #eee
        }

        .menu-hero h1 {
            font-size: 2.8rem;
            color: var(--brand);
            margin-bottom: 8px
        }

        .menu-hero p {
            color: #555
        }

        .menu-wrap {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px
        }

        .tabs {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px
        }

        .tab-btn {
            border: 1px solid #ddd;
            background: #fff;
            padding: 10px 14px;
            border-radius: 999px;
            cursor: pointer
        }

        .tab-btn.active {
            background: var(--brand);
            color: #fff;
            border-color: var(--brand)
        }

        .filters {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin: 10px 0 25px
        }

        .filter-btn {
            border: 1px solid #ddd;
            background: #fff;
            padding: 6px 12px;
            border-radius: 999px;
            cursor: pointer;
            font-size: .9rem
        }

        .filter-btn.active {
            background: #222;
            color: #fff;
            border-color: #222
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px
        }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .06);
            overflow: hidden;
            display: flex;
            flex-direction: column
        }

        .card img {
            width: 375px;
            height: 260px;
            object-fit: fill;
            background: #f3f3f3
        }

        .card-body {
            padding: 14px
        }

        .pill {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            background: #f1f1f1;
            font-size: .78rem;
            margin-bottom: 8px
        }

        .title {
            font-weight: 700;
            margin-bottom: 4px
        }

        .desc {
            font-size: .95rem;
            color: #555;
            min-height: 38px
        }

        .price-row {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center
        }

        .price {
            font-weight: 800;
            color: #111
        }

        .badge-unavail {
            font-size: .78rem;
            color: #b00020;
            border: 1px solid #f0cfcf;
            background: #fff5f5;
            border-radius: 6px;
            padding: 4px 8px
        }

        @media (max-width:992px) {
            .grid {
                grid-template-columns: repeat(2, 1fr)
            }
        }

        @media (max-width:600px) {
            .grid {
                grid-template-columns: 1fr
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="nav-container">
            <a class="logo" href="home.php">
                <div class="logo-icon">🥡</div><span>Wok N Bowl</span>
            </a>
            <nav>
                <ul>
                    <li><a href="home.php#home">🏠 Home</a></li>
                    <li><a href="home.php#about">ℹ️ About Us</a></li>
                    <li><a href="menu.php">📋 Menu</a></li>
                    <li><a href="reservation.php">📅 Reservation</a></li>
                    <li><a href="home.php#contact">📞 Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="menu-hero">
        <h1>Our Menu</h1>
        <p>Pure Veg • Starters • Main Course • Desserts</p>
    </section>

    <div class="menu-wrap">
        <!-- Cuisine Tabs -->
        <div class="tabs" id="tabs">
            <?php foreach ($cats as $index => $c): ?>
                <button class="tab-btn <?= $index === 0 ? 'active' : '' ?>" data-tab="cat-<?= $c['id'] ?>">
                    <?= htmlspecialchars($c['name']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Course Filters -->
        <div class="filters" id="filters">
            <?php
            $courses = ["All", "Starter", "Main Course", "Dessert"];
            foreach ($courses as $i => $co) {
                $cls = $i === 0 ? 'active' : '';
                echo '<button class="filter-btn ' . $cls . '" data-course="' . htmlspecialchars($co) . '">' . $co . '</button>';
            }
            ?>
        </div>

        <!-- Items per category -->
        <?php foreach ($cats as $idx => $c): ?>
            <div class="category-panel" id="cat-<?= $c['id'] ?>" style="<?= $idx === 0 ? '' : 'display:none' ?>">
                <div class="grid">
                    <?php foreach ($byCat[$c['id']] ?? [] as $it): ?>
                        <div class="card" data-course="<?= htmlspecialchars($it['course']) ?>">
                            <img src="<?= htmlspecialchars($it['image']) ?>" alt="<?= htmlspecialchars($it['name']) ?>"
                                onerror="this.onerror=null; this.src='https://via.placeholder.com/600x400?text=<?= urlencode($it['name']) ?>'">

                            <div class="card-body">
                                <span class="pill"><?= htmlspecialchars($it['course']) ?></span>
                                <div class="title"><?= htmlspecialchars($it['name']) ?></div>
                                <div class="desc"><?= htmlspecialchars($it['description']) ?></div>
                                <div class="price-row">
                                    <div class="price">₹<?= number_format($it['price'], 2) ?></div>
                                    <?php if (!$it['is_available']): ?>
                                        <span class="badge-unavail">Not available</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        // Tabs
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const id = btn.dataset.tab;
                document.querySelectorAll('.category-panel').forEach(p => p.style.display = 'none');
                const panel = document.getElementById(id);
                if (panel) panel.style.display = '';
                // reset filter to All on tab switch
                document.querySelectorAll('.filter-btn').forEach(f => f.classList.remove('active'));
                document.querySelector('.filter-btn[data-course="All"]').classList.add('active');
                applyFilter('All', panel);
            });
        });

        // Filters
        document.querySelectorAll('.filter-btn').forEach(f => {
            f.addEventListener('click', () => {
                document.querySelectorAll('.filter-btn').forEach(x => x.classList.remove('active'));
                f.classList.add('active');
                const activePanel = document.querySelector('.category-panel:not([style*="display: none"])');
                applyFilter(f.dataset.course, activePanel);
            });
        });

        function applyFilter(course, panel) {
            if (!panel) return;
            panel.querySelectorAll('.card').forEach(card => {
                if (course === 'All' || card.dataset.course === course) card.style.display = '';
                else card.style.display = 'none';
            });
        }
    </script>
</body>

</html>