<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin'])) {
    header("Location: Login.php");
    exit();
}

// Detect if just came from login
$fromLogin = false;
if (isset($_SESSION['from_login']) && $_SESSION['from_login'] === true) {
    $fromLogin = true;
    unset($_SESSION['from_login']); // clear it after use
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wok N Bowl - Restaurant Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        /* Header & Navigation */
        header {
            background: linear-gradient(135deg, #e63946 0%, #c1121f 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
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

        nav li {
            position: relative;
        }

        nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 0.95rem;
            white-space: nowrap;
        }

        nav a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Auth Buttons */
        .auth-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .login-btn,
        .signup-btn {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .login-btn {
            color: white;
            border: 2px solid white;
            background: transparent;
        }

        .login-btn:hover {
            background: white;
            color: #e63946;
        }

        .signup-btn {
            background: white;
            color: #e63946;
            border: 2px solid white;
        }

        .signup-btn:hover {
            background: transparent;
            color: white;
            border-color: white;
        }

        /* Dropdown Menu */
        .dropdown {
            position: relative;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            min-width: 180px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            z-index: 1001;
        }

        .dropdown:hover .dropdown-content {
            display: block;
            animation: fadeInDown 0.3s ease;
        }

        .dropdown-content a {
            color: #333;
            padding: 10px 15px;
            display: block;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.9rem;
        }

        .dropdown-content a:hover {
            background-color: #f8f9fa;
            color: #e63946;
            transform: none;
        }

        /* Hero Section */
        .hero {
            background: url("uploads/home_banner.jpeg") center/cover no-repeat;
            height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-content p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-button {
            background: white;
            color: #e63946;
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        /* About Section */
        .about {
            padding: 80px 20px;
            background: #fff;
            text-align: center;
        }

        .about-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .about h2 {
            font-size: 2.5rem;
            color: #e63946;
            margin-bottom: 20px;
        }

        .about p {
            font-size: 1.1rem;
            color: #555;
            margin-bottom: 30px;
            line-height: 1.8;
        }

        .about-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            justify-content: center;
            margin-top: 40px;
        }

        .about-card {
            flex: 1 1 250px;
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .about-card:hover {
            transform: translateY(-5px);
        }

        .about-card h3 {
            color: #c1121f;
            margin-bottom: 15px;
        }

        .about-card p {
            color: #555;
        }


        /* Footer */
        footer {
            background: #333;
            color: white;
            padding: 40px 20px 20px;
            text-align: center;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: #e63946;
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .nav-container {
                flex-wrap: wrap;
                height: auto;
                padding: 10px 15px;
            }

            .logo {
                font-size: 1.2rem;
            }

            .logo-icon {
                width: 30px;
                height: 30px;
                font-size: 1rem;
            }

            nav ul {
                width: 100%;
                justify-content: center;
                margin-top: 10px;
                gap: 10px;
                flex-wrap: wrap;
            }

            nav a {
                font-size: 0.85rem;
                padding: 6px 10px;
            }

            .auth-buttons {
                flex-wrap: wrap;
                gap: 8px;
            }

            .login-btn,
            .signup-btn {
                font-size: 0.85rem;
                padding: 6px 12px;
            }

            .hero-content h1 {
                font-size: 2.5rem;
            }

            .hero-content p {
                font-size: 1.1rem;
            }

            .features {
                padding: 50px 20px;
            }

            .section-title h2 {
                font-size: 2rem;
            }

            .stats-grid {
                gap: 30px;
            }

            .stat-item h3 {
                font-size: 2.5rem;
            }
        }

        /* Hide/Show based on user role */
        .nav-customer,
        .nav-waiter,
        .nav-admin {
            display: none;
        }

        /* Default to customer view */
        body[data-role="customer"] .nav-customer,
        body[data-role="waiter"] .nav-waiter,
        body[data-role="admin"] .nav-admin {
            display: flex;
        }

        /* Hide sign up button for waiter and admin roles, hide login button for admin */
        body[data-role="waiter"] .signup-btn,
        body[data-role="admin"] .signup-btn,
        body[data-role="admin"] .login-btn {
            display: none;
        }

        /* Specials Section */
        .specials {
            background: #fff;
            text-align: center;
        }

        .specials h2 {
            color: #1d3557;
            margin-bottom: 30px;
        }

        .menu-items {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .item {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            width: 250px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .item:hover {
            transform: translateY(-5px);
        }

        .item img {
            width: 100%;
            border-radius: 15px;
            margin-bottom: 15px;
        }

        .item h3 {
            margin: 0;
            color: #e63946;
        }

        /* Offer Section */
        .offers {
            background: #fff;
            padding: 60px 20px;
            text-align: center;
        }

        .offers h2 {
            color: #e63946;
            font-size: 2rem;
            margin-bottom: 15px;
        }

        .offers-subtitle {
            color: #333;
            margin-bottom: 40px;
            font-size: 1.1rem;
        }

        .offers-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .offer-card {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 25px;
            width: 280px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .offer-card:hover {
            transform: translateY(-5px);
        }

        .offer-card h3 {
            color: #1d3557;
            margin-bottom: 10px;
        }

        .offer-card p {
            font-size: 0.95rem;
            margin-bottom: 15px;
        }

        .offer-tag {
            display: inline-block;
            background: #ffb703;
            color: #1d3557;
            padding: 6px 15px;
            border-radius: 30px;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .offer-tag.red {
            background: #e63946;
            color: white;
        }

        /* Contact us Section */

        .contact {
            background: #f8f9fa;
            padding: 60px 20px;
        }

        .contact-container {
            max-width: 1100px;
            margin: auto;
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
        }

        .contact-info {
            flex: 1;
            min-width: 300px;
        }

        .contact-info h2 {
            color: #e63946;
            margin-bottom: 20px;
        }

        .contact-info p {
            margin: 10px 0;
            font-size: 1.1rem;
        }

        .map {
            margin-top: 20px;
            border-radius: 15px;
            overflow: hidden;
        }

        .contact-form {
            flex: 1;
            min-width: 300px;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .contact-form h3 {
            color: #1d3557;
            margin-bottom: 20px;
        }

        .contact-form form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .contact-form input,
        .contact-form textarea {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }

        .contact-form button {
            background: #e63946;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .contact-form button:hover {
            background: #ffb703;
            color: #1d3557;
        }
    </style>
    <?php if (isset($_SESSION['user_id'])): ?>
        <script>
            // Only logged-in users get back button trap
            history.pushState(null, null, location.href);

            window.onpopstate = function () {
                if (!location.hash) {
                    window.location.href = "https://www.google.com";
                }
            };
        </script>
    <?php endif; ?>
</head>

<body data-role="customer">
    <header>
        <div class="nav-container">
            <a href="#" class="logo">
                <div class="logo-icon">🥡</div>
                <span>Wok N Bowl</span>
            </a>

            <nav>
                <ul>
                    <!-- Common Navigation -->
                    <li><a href="#home">🏠 Home</a></li>
                    <li><a href="#about">ℹ️ About Us</a></li>
                    <li><a href="menu.php">📋 Menu</a></li>
                    <li><a href="reservation.php">📅 Reservation</a></li>
                    <li><a href="home.php#contact">📞 Contact</a></li>
                    <li class="nav-customer dropdown">
                        <a href="#account">👤 Account ▾</a>
                        <div class="dropdown-content">
                            <a href="profile.php">👤 Profile</a>
                            <a href="#payment">💳 Payment</a>
                            <a href="#feedback">💭 Feedback</a>
                            <a href="#settings">⚙️ Settings</a>
                            <a href="Logout.php">🚪 Logout</a>
                        </div>
                    </li>

                    <!-- Auth Buttons -->
                    <li class="auth-buttons">
                        <a href="Login.php" class="login-btn">Login</a>
                        <a href="Register.php" class="signup-btn">Sign Up</a>
                    </li>

                </ul>
            </nav>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="hero" id="home">
            <div class="hero-content">
                <h1>Welcome to Wok N Bowl</h1>
                <p>Experience exceptional Asian dining with our complete restaurant management system. From ordering to
                    delivery, we've got everything covered.</p>
                <a href="http://localhost/RESTAURANT/menu.php" class="cta-button">Explore Menu</a>
            </div>
        </section>

        <!-- About Us Section -->
        <section class="about" id="about">
            <div class="about-container">
                <h2>About Us</h2>
                <p>
                    At <strong>Wok N Bowl</strong>, we believe food is more than just a meal – it’s an experience.
                    Founded with a passion for authentic Asian flavors, our restaurant brings together traditional
                    recipes and modern dining convenience.
                    Whether you're dining in, ordering online, or booking a table, our management system ensures a
                    smooth experience every time.
                </p>

                <div class="about-cards">
                    <!-- Card 1 -->
                    <div class="about-card">
                        <h3>🍲 Our Food</h3>
                        <p>We serve freshly prepared Asian delicacies with a focus on quality ingredients and authentic
                            taste.</p>
                    </div>

                    <!-- Card 2 -->
                    <div class="about-card">
                        <h3>👨‍🍳 Our Chefs</h3>
                        <p>Our skilled chefs bring traditional Asian recipes to life with passion and creativity.</p>
                    </div>

                    <!-- Card 3 -->
                    <div class="about-card">
                        <h3>🏮 Our Ambience</h3>
                        <p>We provide a cozy and welcoming atmosphere, perfect for family gatherings, friends, and
                            celebrations.</p>
                    </div>
                </div>
            </div>
        </section>
        <!-- Specials -->
        <section class="specials" id="specials">
            <h2>Our Specials</h2>
            <div class="menu-items">
                <div class="item">
                    <img src="uploads\menu\1757435086_Crispy-paneer-pakora.webp" alt="Pizza">
                    <h3>Cheese Burst Pizza</h3>
                    <p>₹299</p>
                </div>
                <div class="item">
                    <img src="uploads\menu\1757432243_Aloo Tikki.jpg" alt="Pasta">
                    <h3>Italian Pasta</h3>
                    <p>₹199</p>
                </div>
                <div class="item">
                    <img src="uploads\menu\1_IMG_9491_1e1968b7-b144-49ba-a4ed-2659e95a3bcb.webp" alt="Burger">
                    <h3>Grilled Burger</h3>
                    <p>₹149</p>
                </div>
            </div>
        </section>
        <!-- Offers / Promotions Section -->
        <section class="offers" id="offers">
            <h2>Special Offers & Promotions</h2>
            <p class="offers-subtitle">Enjoy our limited-time deals and save more while you dine!</p>

            <div class="offers-container">
                <!-- Offer Card 1 -->
                <div class="offer-card">
                    <h3>Weekend Combo</h3>
                    <p>Buy 1 Large Pizza + 2 Drinks & get <b>20% OFF</b></p>
                    <span class="offer-tag">₹499 Only</span>
                </div>

                <!-- Offer Card 2 -->
                <div class="offer-card">
                    <h3>Happy Hours</h3>
                    <p>Everyday 4PM - 7PM<br>Flat <b>30% OFF</b> on Burgers & Shakes</p>
                    <span class="offer-tag red">Don’t Miss!</span>
                </div>

                <!-- Offer Card 3 -->
                <div class="offer-card">
                    <h3>Family Feast</h3>
                    <p>Order for 4 & get a <b>Free Dessert</b> of your choice</p>
                    <span class="offer-tag">Best for Groups</span>
                </div>
            </div>
        </section>

        <!-- Contact Us -->
        <section class="contact" id="contact">
            <div class="contact-container">

                <!-- Contact Info -->
                <div class="contact-info">
                    <h2>Contact Us</h2>
                    <p>📍 Surat, Gujarat, India</p>
                    <p>📞 +91 98765 43210</p>
                    <p>✉️ info@myrestaurant.com</p>

                    <!-- Google Map -->
                    <div class="map">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3719.3822084292073!2d72.83106101540464!3d21.170240585924774!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be04e594e3e9b7b%3A0x1f0d0a8c4a6df1c4!2sSurat%2C%20Gujarat!5e0!3m2!1sen!2sin!4v1673204000000"
                            width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy">
                        </iframe>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="contact-form">
                    <h3>Send us a Message</h3>
                    <form action="contact_process.php" method="POST">
                        <input type="text" name="name" placeholder="Your Name" required>
                        <input type="email" name="email" placeholder="Your Email" required>
                        <textarea name="message" rows="5" placeholder="Your Message" required></textarea>
                        <button type="submit">Send Message</button>
                    </form>
                </div>

            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-links">
                <a href="#privacy">Privacy Policy</a>
                <a href="#terms">Terms of Service</a>
                <a href="#support">Support</a>
                <a href="#careers">Careers</a>
            </div>
            <p>&copy; 2025 Wok n Bowl Restaurant Management System. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function changeRole(role) {
            document.body.setAttribute('data-role', role);
        }

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        var fromLogin = <?php echo $fromLogin ? 'true' : 'false'; ?>;

        if (fromLogin) {
            // Prevent going back to login, redirect to Google instead
            history.pushState(null, "", location.href);
            window.onpopstate = function () {
                window.location.href = "https://www.google.com";
            };
        }

    </script>
</body>

</html>