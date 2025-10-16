<?php
require_once 'config/database.php';

// Get featured destinations
$featuredDestinations = $db->fetchAll(
    "SELECT * FROM destinations WHERE is_featured = 1 AND is_active = 1 ORDER BY rating DESC LIMIT 10"
);

// Get featured packages
$featuredPackages = $db->fetchAll(
    "SELECT * FROM packages WHERE is_featured = 1 AND is_active = 1 ORDER BY created_at DESC LIMIT 3"
);

// Get deals (we'll create a deals table or use packages with discounts)
$deals = $db->fetchAll(
    "SELECT * FROM packages WHERE is_active = 1 ORDER BY RAND() LIMIT 6"
);

// Get wonders (7 wonders of the world)
$wonders = $db->fetchAll(
    "SELECT * FROM destinations WHERE category = 'culture' AND is_active = 1 ORDER BY rating DESC LIMIT 7"
);

// Get testimonials
$testimonials = $db->fetchAll(
    "SELECT r.*, u.first_name, u.last_name 
     FROM reviews r 
     JOIN users u ON r.user_id = u.id 
     WHERE r.is_approved = 1 
     ORDER BY r.created_at DESC LIMIT 3"
);

// Get FAQ
$faqs = $db->fetchAll(
    "SELECT * FROM faqs WHERE is_active = 1 ORDER BY order_index ASC"
);

// Get volunteers data (static for now)
$volunteers = [
    ['title' => 'First Year Volunteers', 'description' => 'Our journey begins with passionate travelers and volunteers joining hands to explore and promote sustainable tourism.'],
    ['title' => 'Local Partners', 'description' => 'Kindora has started collaborations with local guides and cultural storytellers to bring authentic experiences.'],
    ['title' => 'Sustainability Ambassadors', 'description' => 'Early volunteers working on eco-travel, heritage protection, and community-led projects.'],
    ['title' => 'Launch Event', 'description' => 'Kindora officially launched in 2025 with a vision to inspire world exploration.'],
    ['title' => 'Virtual Culture Exchange', 'description' => 'Hosted our first online cultural session connecting travelers from different continents.'],
    ['title' => 'Eco & Community Activities', 'description' => 'Beginning small eco-drives and heritage awareness campaigns with local groups.'],
    ['title' => 'Countries Featured', 'description' => 'Within the first year, Kindora highlights major attractions from Asia, Europe, and Africa.'],
    ['title' => 'Community Growth', 'description' => 'Thousands of explorers inspired to travel responsibly since our launch.'],
    ['title' => 'Future Vision', 'description' => 'Expanding our global reach by adding more destinations, volunteer programs, and cultural events in upcoming years.']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta charset="UTF-8" />
    <link rel="icon" type="image/png" href="assets/images/kindora-logo.ico" />
    <title>Kindora - Explore the World</title>
    <link href="assets/css/styles.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Background Video -->
    <video autoplay muted loop id="bg-video">
        <source src="assets/videos/bgvideo.mp4" type="video/mp4" />
    </video>

    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-logo">
                <i class="fas fa-globe-americas"></i>
                <span>Kindora</span>
            </a>

            <div class="nav-menu" id="nav-menu">
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link" id="inspireBtn">
                        <i class="fas fa-compass"></i>
                        Be Inspired
                        <i class="fas fa-chevron-down"></i>
                    </a>
                    <div class="dropdown-content" id="inspireDropdown">
                        <div class="dropdown-grid">
                            <a href="pages/asia.html" class="dropdown-item">
                                <img src="assets/images/banner/asia.jpg" alt="Asia" />
                                <span>Asia</span>
                            </a>
                            <a href="pages/europe.html" class="dropdown-item">
                                <img src="assets/images/banner/europe.jpeg" alt="Europe" />
                                <span>Europe</span>
                            </a>
                            <a href="pages/africa.html" class="dropdown-item">
                                <img src="assets/images/banner/africa.jpg" alt="Africa" />
                                <span>Africa</span>
                            </a>
                            <a href="pages/north_america.html" class="dropdown-item">
                                <img src="assets/images/banner/north_america.jpg" alt="North America" />
                                <span>North America</span>
                            </a>
                            <a href="pages/south_america.html" class="dropdown-item">
                                <img src="assets/images/7wonders/Machu_Picchu.jpeg" alt="South America" />
                                <span>South America</span>
                            </a>
                            <a href="pages/australia.html" class="dropdown-item">
                                <img src="assets/images/banner/australia.jpg" alt="Australia" />
                                <span>Australia</span>
                            </a>
                            <a href="pages/antarctica.html" class="dropdown-item">
                                <img src="assets/images/banner/antarctica.jpeg" alt="Antarctica" />
                                <span>Antarctica</span>
                            </a>
                        </div>
                    </div>
                </div>
                <a href="pages/explore.php" class="nav-link">
                    <i class="fas fa-map-marker-alt"></i>
                    Places to Go
                </a>
                <a href="pages/things_to_do.php" class="nav-link">
                    <i class="fas fa-list-ul"></i>
                    Things to Do
                </a>
                <a href="pages/booking.php" class="nav-link">
                    <i class="fas fa-calendar-check"></i>
                    Plan Your Trip
                </a>
                <a href="pages/login.php" class="nav-link cta-nav">
                    <i class="fas fa-user"></i>
                    Login
                </a>
            </div>

            <div class="hamburger" id="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title">
                <span class="title-line">Explore the</span>
                <span class="title-line highlight">World</span>
            </h1>
            <p class="hero-subtitle">Your journey to unforgettable adventures starts here</p>
            <div class="hero-buttons">
                <button class="btn btn-primary" onclick="scrollToSection('popular')">
                    <i class="fas fa-rocket"></i>
                    Start Exploring
                </button>
                <button class="btn btn-secondary" onclick="scrollToSection('packages')">
                    <i class="fas fa-gift"></i>
                    View Packages
                </button>
            </div>
        </div>
        <div class="scroll-indicator">
            <div class="scroll-arrow"></div>
        </div>
    </section>

    <!-- Search Bar -->
    <section class="search-section">
        <div class="container">
            <div class="search-container">
                <h2>Find Your Perfect Destination</h2>
                <div class="search-form">
                    <div class="search-input-group">
                        <i class="fas fa-search"></i>
                        <input type="text" id="destinationSearch" placeholder="Where do you want to go?" />
                    </div>
                    <div class="search-filters">
                        <select id="categoryFilter">
                            <option value="">All Categories</option>
                            <option value="beach">Beach</option>
                            <option value="mountain">Mountain</option>
                            <option value="city">City</option>
                            <option value="nature">Nature</option>
                            <option value="culture">Culture</option>
                        </select>
                        <select id="budgetFilter">
                            <option value="">Any Budget</option>
                            <option value="low">$0 - $500</option>
                            <option value="medium">$500 - $2000</option>
                            <option value="high">$2000+</option>
                        </select>
                        <button class="btn btn-search">
                            <i class="fas fa-search"></i>
                            Search
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Places Section -->
    <section id="popular" class="popular-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Top 10 Most Popular Places</h2>
                <p class="section-subtitle">Discover the world's most breathtaking destinations</p>
            </div>
            <div class="places-grid" id="placesGrid">
                <?php foreach ($featuredDestinations as $destination): ?>
                    <div class="place-card" data-category="<?php echo $destination['category']; ?>" data-budget="<?php echo $destination['budget_level']; ?>">
                        <a href="pages/destination_detail.php?id=<?php echo $destination['id']; ?>">
                            <div class="card-image">
                                <img src="<?php echo htmlspecialchars($destination['image_url']); ?>" alt="<?php echo htmlspecialchars($destination['name']); ?>" onerror="this.src='assets/images/placeholder.jpg'" />
                                <div class="card-overlay">
                                    <div class="card-rating">
                                        <i class="fas fa-star"></i>
                                        <span><?php echo number_format($destination['rating'], 1); ?></span>
                                    </div>
                                    <div class="card-price">From $<?php echo number_format(rand(99, 999)); ?></div>
                                </div>
                            </div>
                            <div class="card-content">
                                <h3><?php echo htmlspecialchars($destination['name']); ?></h3>
                                <p><?php echo htmlspecialchars($destination['country']); ?></p>
                                <div class="card-tags">
                                    <span class="tag"><?php echo ucfirst($destination['category']); ?></span>
                                    <span class="tag"><?php echo ucfirst($destination['budget_level']); ?> Budget</span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Packages Section -->
    <section id="packages" class="packages-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Our Travel Packages</h2>
                <p class="section-subtitle">Carefully crafted experiences for every type of traveler</p>
            </div>
            <div class="packages-grid">
                <?php foreach ($featuredPackages as $package): ?>
                    <div class="package-card">
                        <div class="package-image">
                            <img src="<?php echo htmlspecialchars($package['image_url']); ?>" alt="<?php echo htmlspecialchars($package['name']); ?>" onerror="this.src='assets/images/placeholder.jpg'" />
                            <div class="package-badge"><?php echo ucfirst($package['category']); ?></div>
                        </div>
                        <div class="package-content">
                            <h3><?php echo htmlspecialchars($package['name']); ?></h3>
                            <p><?php echo htmlspecialchars($package['short_description']); ?></p>
                            <div class="package-features">
                                <span><i class="fas fa-calendar"></i> <?php echo $package['duration_days']; ?> days</span>
                                <span><i class="fas fa-users"></i> Max <?php echo $package['max_participants']; ?> people</span>
                                <span><i class="fas fa-star"></i> <?php echo ucfirst($package['difficulty_level']); ?> level</span>
                            </div>
                            <div class="package-price">
                                <span class="price">From $<?php echo number_format($package['price']); ?></span>
                                <span class="duration"><?php echo $package['duration_days']; ?> days</span>
                            </div>
                            <a href="pages/package_detail.php?id=<?php echo $package['id']; ?>" class="btn btn-package">Book Now</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Deals Carousel -->
    <section class="deals-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Special Offers</h2>
                <p class="section-subtitle">Limited time deals you don't want to miss</p>
            </div>
            <div class="deals-carousel">
                <?php foreach (array_slice($deals, 0, 6) as $index => $deal): ?>
                    <div class="deal-slide <?php echo $index === 0 ? 'active' : ''; ?>">
                        <div class="deal-content">
                            <div class="deal-image">
                                <img src="<?php echo htmlspecialchars($deal['image_url']); ?>" alt="<?php echo htmlspecialchars($deal['name']); ?>" onerror="this.src='assets/images/placeholder.jpg'" />
                                <div class="deal-discount"><?php echo rand(20, 40); ?>% OFF</div>
                            </div>
                            <div class="deal-info">
                                <h3><?php echo htmlspecialchars($deal['name']); ?></h3>
                                <p><?php echo htmlspecialchars($deal['short_description']); ?></p>
                                <div class="deal-price">
                                    <span class="original-price">$<?php echo number_format($deal['price'] * 1.3); ?></span>
                                    <span class="sale-price">$<?php echo number_format($deal['price']); ?></span>
                                </div>
                                <a href="pages/package_detail.php?id=<?php echo $deal['id']; ?>" class="btn btn-deal">Book Now</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="carousel-dots">
                <?php for ($i = 0; $i < min(6, count($deals)); $i++): ?>
                    <span class="dot <?php echo $i === 0 ? 'active' : ''; ?>" onclick="currentSlide(<?php echo $i + 1; ?>)"></span>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <!-- 7 Wonders Section -->
    <section class="wonders-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">7 Wonders of the World</h2>
                <p class="section-subtitle">Discover the most magnificent man-made structures on Earth</p>
            </div>
            <div class="wonders-grid">
                <?php foreach (array_slice($wonders, 0, 7) as $wonder): ?>
                    <div class="wonder-card">
                        <a href="pages/wonder_detail.php?id=<?php echo $wonder['id']; ?>">
                            <div class="wonder-image">
                                <img src="<?php echo htmlspecialchars($wonder['image_url']); ?>" alt="<?php echo htmlspecialchars($wonder['name']); ?>" onerror="this.src='assets/images/placeholder.jpg'" />
                                <div class="wonder-overlay">
                                    <div class="wonder-info">
                                        <h3><?php echo htmlspecialchars($wonder['name']); ?></h3>
                                        <p><?php echo htmlspecialchars($wonder['country']); ?></p>
                                        <div class="wonder-rating">
                                            <i class="fas fa-star"></i>
                                            <span><?php echo number_format($wonder['rating'], 1); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">What Our Travelers Say</h2>
                <p class="section-subtitle">Real experiences from real adventurers</p>
            </div>
            <div class="testimonials-carousel">
                <?php foreach ($testimonials as $index => $testimonial): ?>
                    <div class="testimonial-slide <?php echo $index === 0 ? 'active' : ''; ?>">
                        <div class="testimonial-content">
                            <div class="testimonial-image">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($testimonial['first_name'] . ' ' . $testimonial['last_name']); ?>&background=003366&color=fff&size=120" alt="<?php echo htmlspecialchars($testimonial['first_name'] . ' ' . $testimonial['last_name']); ?>" />
                            </div>
                            <div class="testimonial-text">
                                <div class="stars">
                                    <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                                        <i class="fas fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                                <p>"<?php echo htmlspecialchars($testimonial['comment']); ?>"</p>
                                <h4><?php echo htmlspecialchars($testimonial['first_name'] . ' ' . $testimonial['last_name']); ?></h4>
                                <span>Traveler</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="testimonial-dots">
                <?php for ($i = 0; $i < count($testimonials); $i++): ?>
                    <span class="dot <?php echo $i === 0 ? 'active' : ''; ?>" onclick="currentTestimonial(<?php echo $i + 1; ?>)"></span>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-content">
                <div class="newsletter-text">
                    <h2>Stay Updated with Travel Deals</h2>
                    <p>Get exclusive offers, travel tips, and destination guides delivered to your inbox</p>
                </div>
                <form class="newsletter-form" id="newsletterForm">
                    <div class="form-group">
                        <input type="email" placeholder="Enter your email address" required />
                        <button type="submit" class="btn btn-newsletter">
                            <i class="fas fa-paper-plane"></i>
                            Subscribe
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Volunteers & Events Section -->
    <section class="volunteers-events-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Kindora Volunteers & Events (2025)</h2>
                <p class="section-subtitle">Join our community of passionate travelers and volunteers</p>
            </div>
            <div class="volunteers-grid">
                <?php foreach ($volunteers as $volunteer): ?>
                    <div class="volunteer-card">
                        <h3><?php echo htmlspecialchars($volunteer['title']); ?></h3>
                        <p><?php echo htmlspecialchars($volunteer['description']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Reviews Section -->
    <section class="reviews-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Traveler Reviews</h2>
                <p class="section-subtitle">Real experiences from real adventurers</p>
            </div>
            <div class="reviews-grid" id="reviewsList">
                <?php foreach (array_slice($testimonials, 0, 5) as $review): ?>
                    <div class="review-card">
                        <h4><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></h4>
                        <div class="stars">
                            <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                                <i class="fas fa-star"></i>
                            <?php endfor; ?>
                        </div>
                        <p><?php echo htmlspecialchars($review['comment']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="review-form-container">
                <h3>Add Your Review</h3>
                <form id="reviewForm" class="review-form">
                    <input type="text" id="reviewName" placeholder="Your Name" required />
                    <div id="starRating" class="star-rating">
                        <span data-value="1">★</span>
                        <span data-value="2">★</span>
                        <span data-value="3">★</span>
                        <span data-value="4">★</span>
                        <span data-value="5">★</span>
                    </div>
                    <textarea id="reviewComment" rows="4" placeholder="Write your review..." required></textarea>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Counter Section -->
    <section class="counter-section">
        <div class="container">
            <div class="counter-content">
                <h2 class="counter-title">Our Achievements</h2>
                <div class="counter-grid">
                    <div class="counter-box">
                        <h3 class="counter" data-target="5000">0</h3>
                        <p>Happy Travelers</p>
                    </div>
                    <div class="counter-box">
                        <h3 class="counter" data-target="1200">0</h3>
                        <p>Tours Organized</p>
                    </div>
                    <div class="counter-box">
                        <h3 class="counter" data-target="800">0</h3>
                        <p>Reviews</p>
                    </div>
                    <div class="counter-box">
                        <h3 class="counter" data-target="150">0</h3>
                        <p>Destinations</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Frequently Asked Questions</h2>
                <p class="section-subtitle">Everything you need to know about traveling with Kindora</p>
            </div>
            <div class="faq-container">
                <?php foreach ($faqs as $faq): ?>
                    <div class="faq-item">
                        <button class="faq-question">
                            <span><?php echo htmlspecialchars($faq['question']); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="faq-answer">
                            <p><?php echo htmlspecialchars($faq['answer']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <i class="fas fa-globe-americas"></i>
                        <span>Kindora</span>
                    </div>
                    <p>Your gateway to dream destinations around the globe. Explore, travel, and create unforgettable memories with us.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>

                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="pages/explore.php">Places to Go</a></li>
                        <li><a href="pages/things_to_do.php">Things to Do</a></li>
                        <li><a href="pages/booking.php">Plan Your Trip</a></li>
                        <li><a href="pages/aboutus.html">About Us</a></li>
                        <li><a href="pages/contactus.html">Contact</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>Destinations</h3>
                    <ul>
                        <li><a href="pages/asia.html">Asia</a></li>
                        <li><a href="pages/europe.html">Europe</a></li>
                        <li><a href="pages/africa.html">Africa</a></li>
                        <li><a href="pages/north_america.html">North America</a></li>
                        <li><a href="pages/south_america.html">South America</a></li>
                        <li><a href="pages/australia.html">Australia</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>Support</h3>
                    <ul>
                        <li><a href="pages/help.php">Help Center</a></li>
                        <li><a href="pages/insurance.php">Travel Insurance</a></li>
                        <li><a href="pages/terms.php">Terms of Service</a></li>
                        <li><a href="pages/privacy.php">Privacy Policy</a></li>
                        <li><a href="pages/refund.php">Refund Policy</a></li>
                        <li><a href="pages/accessibility.php">Accessibility</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <div class="contact-info">
                        <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                        <p><i class="fas fa-envelope"></i> hello@kindora.com</p>
                        <p><i class="fas fa-map-marker-alt"></i> 123 Travel Street, Adventure City, AC 12345</p>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; 2025 Kindora. All rights reserved.</p>
                    <div class="footer-bottom-links">
                        <a href="pages/privacy.php">Privacy</a>
                        <a href="pages/terms.php">Terms</a>
                        <a href="pages/cookies.php">Cookies</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button class="back-to-top" id="backToTop">
        <i class="fas fa-chevron-up"></i>
    </button>

    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
</body>
</html>