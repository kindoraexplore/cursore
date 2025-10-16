<?php
require_once '../config/database.php';

// Get activities from database (we'll create this table)
$activities = $db->fetchAll("
    SELECT a.*, d.name as destination_name, d.country, d.continent
    FROM activities a 
    LEFT JOIN destinations d ON a.destination_id = d.id 
    WHERE a.is_active = 1 
    ORDER BY a.is_featured DESC, a.name ASC
");

// If activities table doesn't exist, create sample data
if (empty($activities)) {
    // Create activities table
    $db->query("
        CREATE TABLE IF NOT EXISTS activities (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            short_description VARCHAR(255),
            image_url VARCHAR(255),
            category ENUM('adventure', 'culture', 'nature', 'food', 'entertainment', 'relaxation', 'sports') NOT NULL,
            difficulty_level ENUM('easy', 'medium', 'hard') DEFAULT 'easy',
            duration_hours DECIMAL(4,2),
            price_range ENUM('free', 'low', 'medium', 'high') DEFAULT 'medium',
            destination_id INT,
            is_featured BOOLEAN DEFAULT FALSE,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE SET NULL
        )
    ");
    
    // Insert sample activities
    $sampleActivities = [
        [
            'name' => 'Mountain Hiking Adventure',
            'description' => 'Experience breathtaking views and fresh mountain air on our guided hiking tours.',
            'short_description' => 'Guided hiking tours with stunning mountain views',
            'image_url' => '../assets/images/activities/hiking.jpg',
            'category' => 'adventure',
            'difficulty_level' => 'medium',
            'duration_hours' => 6.0,
            'price_range' => 'medium',
            'is_featured' => true
        ],
        [
            'name' => 'Cultural Food Tour',
            'description' => 'Discover local cuisine and traditions through guided food tours.',
            'short_description' => 'Explore local cuisine and culinary traditions',
            'image_url' => '../assets/images/activities/food_tour.jpg',
            'category' => 'food',
            'difficulty_level' => 'easy',
            'duration_hours' => 3.0,
            'price_range' => 'medium',
            'is_featured' => true
        ],
        [
            'name' => 'Wildlife Safari',
            'description' => 'Get up close with amazing wildlife in their natural habitat.',
            'short_description' => 'Wildlife viewing in natural habitats',
            'image_url' => '../assets/images/activities/safari.jpg',
            'category' => 'nature',
            'difficulty_level' => 'easy',
            'duration_hours' => 8.0,
            'price_range' => 'high',
            'is_featured' => true
        ],
        [
            'name' => 'City Walking Tour',
            'description' => 'Explore historic landmarks and hidden gems with local guides.',
            'short_description' => 'Historic city exploration with local guides',
            'image_url' => '../assets/images/activities/city_tour.jpg',
            'category' => 'culture',
            'difficulty_level' => 'easy',
            'duration_hours' => 2.5,
            'price_range' => 'low',
            'is_featured' => false
        ],
        [
            'name' => 'Beach Yoga Session',
            'description' => 'Relax and rejuvenate with yoga sessions on beautiful beaches.',
            'short_description' => 'Relaxing yoga sessions on scenic beaches',
            'image_url' => '../assets/images/activities/yoga.jpg',
            'category' => 'relaxation',
            'difficulty_level' => 'easy',
            'duration_hours' => 1.5,
            'price_range' => 'low',
            'is_featured' => false
        ],
        [
            'name' => 'Scuba Diving',
            'description' => 'Explore underwater worlds with certified diving instructors.',
            'short_description' => 'Underwater exploration with certified instructors',
            'image_url' => '../assets/images/activities/diving.jpg',
            'category' => 'adventure',
            'difficulty_level' => 'hard',
            'duration_hours' => 4.0,
            'price_range' => 'high',
            'is_featured' => true
        ]
    ];
    
    foreach ($sampleActivities as $activity) {
        $db->insert('activities', $activity);
    }
    
    // Refresh activities
    $activities = $db->fetchAll("
        SELECT a.*, d.name as destination_name, d.country, d.continent
        FROM activities a 
        LEFT JOIN destinations d ON a.destination_id = d.id 
        WHERE a.is_active = 1 
        ORDER BY a.is_featured DESC, a.name ASC
    ");
}

// Get filter parameters
$category = $_GET['category'] ?? '';
$difficulty = $_GET['difficulty'] ?? '';
$price = $_GET['price'] ?? '';
$search = $_GET['search'] ?? '';

// Filter activities
$filteredActivities = $activities;
if ($category) {
    $filteredActivities = array_filter($filteredActivities, function($activity) use ($category) {
        return $activity['category'] === $category;
    });
}
if ($difficulty) {
    $filteredActivities = array_filter($filteredActivities, function($activity) use ($difficulty) {
        return $activity['difficulty_level'] === $difficulty;
    });
}
if ($price) {
    $filteredActivities = array_filter($filteredActivities, function($activity) use ($price) {
        return $activity['price_range'] === $price;
    });
}
if ($search) {
    $filteredActivities = array_filter($filteredActivities, function($activity) use ($search) {
        return stripos($activity['name'], $search) !== false || 
               stripos($activity['description'], $search) !== false;
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Things to Do - Kindora</title>
    <link href="../assets/css/styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .activities-hero {
            background: linear-gradient(135deg, #003366 0%, #004080 100%);
            color: white;
            padding: 8rem 0 4rem;
            text-align: center;
        }
        
        .filter-section {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin: -2rem auto 4rem;
            max-width: 1200px;
            position: relative;
            z-index: 10;
        }
        
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        
        .filter-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }
        
        .filter-group select,
        .filter-group input {
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
        }
        
        .activities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }
        
        .activity-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .activity-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .activity-image {
            position: relative;
            height: 200px;
            overflow: hidden;
        }
        
        .activity-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .activity-card:hover .activity-image img {
            transform: scale(1.1);
        }
        
        .activity-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--gradient-primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .activity-content {
            padding: 2rem;
        }
        
        .activity-category {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }
        
        .activity-content h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }
        
        .activity-description {
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        
        .activity-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        
        .detail-item i {
            color: var(--primary-color);
            width: 16px;
        }
        
        .difficulty-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .difficulty-easy {
            background: #d4edda;
            color: #155724;
        }
        
        .difficulty-medium {
            background: #fff3cd;
            color: #856404;
        }
        
        .difficulty-hard {
            background: #f8d7da;
            color: #721c24;
        }
        
        .price-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-left: 0.5rem;
        }
        
        .price-free {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .price-low {
            background: #d4edda;
            color: #155724;
        }
        
        .price-medium {
            background: #fff3cd;
            color: #856404;
        }
        
        .price-high {
            background: #f8d7da;
            color: #721c24;
        }
        
        .activity-actions {
            display: flex;
            gap: 1rem;
        }
        
        .btn-activity {
            flex: 1;
            padding: 0.75rem 1rem;
            border: none;
            border-radius: 10px;
            font-weight: 500;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .btn-primary-activity {
            background: var(--gradient-primary);
            color: white;
        }
        
        .btn-primary-activity:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 51, 102, 0.3);
        }
        
        .btn-secondary-activity {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }
        
        .btn-secondary-activity:hover {
            background: var(--primary-color);
            color: white;
        }
        
        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-secondary);
        }
        
        .no-results i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .category-highlights {
            margin-bottom: 4rem;
        }
        
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .category-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .category-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .category-card h4 {
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }
        
        .category-count {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        
        @media (max-width: 768px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }
            
            .activities-grid {
                grid-template-columns: 1fr;
            }
            
            .activity-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="../index.php" class="nav-logo">
                <i class="fas fa-globe-americas"></i>
                <span>Kindora</span>
            </a>
            <div class="nav-menu">
                <a href="../index.php" class="nav-link">Home</a>
                <a href="explore.php" class="nav-link">Places to Go</a>
                <a href="things_to_do.php" class="nav-link active">Things to Do</a>
                <a href="booking.php" class="nav-link">Plan Your Trip</a>
                <a href="login.php" class="nav-link cta-nav">Login</a>
            </div>
        </div>
    </nav>

    <!-- Activities Hero -->
    <section class="activities-hero">
        <div class="container">
            <h1>Amazing Things to Do</h1>
            <p>Discover exciting activities and experiences around the world</p>
        </div>
    </section>

    <!-- Category Highlights -->
    <section class="category-highlights">
        <div class="container">
            <h2 class="section-title">Activity Categories</h2>
            <div class="category-grid">
                <div class="category-card" onclick="filterByCategory('adventure')">
                    <div class="category-icon"><i class="fas fa-mountain"></i></div>
                    <h4>Adventure</h4>
                    <div class="category-count">Thrilling experiences</div>
                </div>
                <div class="category-card" onclick="filterByCategory('culture')">
                    <div class="category-icon"><i class="fas fa-landmark"></i></div>
                    <h4>Culture</h4>
                    <div class="category-count">Cultural immersion</div>
                </div>
                <div class="category-card" onclick="filterByCategory('nature')">
                    <div class="category-icon"><i class="fas fa-leaf"></i></div>
                    <h4>Nature</h4>
                    <div class="category-count">Natural wonders</div>
                </div>
                <div class="category-card" onclick="filterByCategory('food')">
                    <div class="category-icon"><i class="fas fa-utensils"></i></div>
                    <h4>Food</h4>
                    <div class="category-count">Culinary delights</div>
                </div>
                <div class="category-card" onclick="filterByCategory('relaxation')">
                    <div class="category-icon"><i class="fas fa-spa"></i></div>
                    <h4>Relaxation</h4>
                    <div class="category-count">Peaceful moments</div>
                </div>
                <div class="category-card" onclick="filterByCategory('sports')">
                    <div class="category-icon"><i class="fas fa-football-ball"></i></div>
                    <h4>Sports</h4>
                    <div class="category-count">Active pursuits</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Filter Section -->
    <div class="container">
        <div class="filter-section">
            <form method="GET" id="filterForm">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="search">Search Activities</label>
                        <input type="text" id="search" name="search" placeholder="Search activities..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label for="category">Category</label>
                        <select id="category" name="category">
                            <option value="">All Categories</option>
                            <option value="adventure" <?php echo $category === 'adventure' ? 'selected' : ''; ?>>Adventure</option>
                            <option value="culture" <?php echo $category === 'culture' ? 'selected' : ''; ?>>Culture</option>
                            <option value="nature" <?php echo $category === 'nature' ? 'selected' : ''; ?>>Nature</option>
                            <option value="food" <?php echo $category === 'food' ? 'selected' : ''; ?>>Food</option>
                            <option value="entertainment" <?php echo $category === 'entertainment' ? 'selected' : ''; ?>>Entertainment</option>
                            <option value="relaxation" <?php echo $category === 'relaxation' ? 'selected' : ''; ?>>Relaxation</option>
                            <option value="sports" <?php echo $category === 'sports' ? 'selected' : ''; ?>>Sports</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="difficulty">Difficulty</label>
                        <select id="difficulty" name="difficulty">
                            <option value="">All Levels</option>
                            <option value="easy" <?php echo $difficulty === 'easy' ? 'selected' : ''; ?>>Easy</option>
                            <option value="medium" <?php echo $difficulty === 'medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="hard" <?php echo $difficulty === 'hard' ? 'selected' : ''; ?>>Hard</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="price">Price Range</label>
                        <select id="price" name="price">
                            <option value="">Any Price</option>
                            <option value="free" <?php echo $price === 'free' ? 'selected' : ''; ?>>Free</option>
                            <option value="low" <?php echo $price === 'low' ? 'selected' : ''; ?>>Low ($0-25)</option>
                            <option value="medium" <?php echo $price === 'medium' ? 'selected' : ''; ?>>Medium ($25-100)</option>
                            <option value="high" <?php echo $price === 'high' ? 'selected' : ''; ?>>High ($100+)</option>
                        </select>
                    </div>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="things_to_do.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear Filters
                    </a>
                </div>
            </form>
        </div>

        <!-- Results -->
        <div class="results-header">
            <div class="results-count">
                Found <?php echo count($filteredActivities); ?> activit<?php echo count($filteredActivities) !== 1 ? 'ies' : 'y'; ?>
            </div>
        </div>

        <!-- Activities Grid -->
        <?php if (empty($filteredActivities)): ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h3>No activities found</h3>
                <p>Try adjusting your search criteria or browse all activities</p>
                <a href="things_to_do.php" class="btn btn-primary">View All Activities</a>
            </div>
        <?php else: ?>
            <div class="activities-grid">
                <?php foreach ($filteredActivities as $activity): ?>
                    <div class="activity-card">
                        <div class="activity-image">
                            <img src="<?php echo htmlspecialchars($activity['image_url']); ?>" alt="<?php echo htmlspecialchars($activity['name']); ?>" onerror="this.src='../assets/images/placeholder.jpg'">
                            <?php if ($activity['is_featured']): ?>
                                <div class="activity-badge">Featured</div>
                            <?php endif; ?>
                        </div>
                        <div class="activity-content">
                            <div class="activity-category"><?php echo ucfirst($activity['category']); ?></div>
                            <h3><?php echo htmlspecialchars($activity['name']); ?></h3>
                            <p class="activity-description"><?php echo htmlspecialchars($activity['short_description']); ?></p>
                            
                            <div class="activity-details">
                                <div class="detail-item">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo $activity['duration_hours']; ?> hours</span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-signal"></i>
                                    <span class="difficulty-badge difficulty-<?php echo $activity['difficulty_level']; ?>">
                                        <?php echo ucfirst($activity['difficulty_level']); ?>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-dollar-sign"></i>
                                    <span class="price-badge price-<?php echo $activity['price_range']; ?>">
                                        <?php echo ucfirst($activity['price_range']); ?>
                                    </span>
                                </div>
                                <?php if ($activity['destination_name']): ?>
                                    <div class="detail-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo htmlspecialchars($activity['destination_name']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="activity-actions">
                                <a href="activity_detail.php?id=<?php echo $activity['id']; ?>" class="btn-activity btn-primary-activity">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <a href="booking.php?activity=<?php echo $activity['id']; ?>" class="btn-activity btn-secondary-activity">
                                    <i class="fas fa-calendar-plus"></i> Book Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function filterByCategory(category) {
            document.getElementById('category').value = category;
            document.getElementById('filterForm').submit();
        }
        
        // Auto-submit form on filter change
        document.getElementById('filterForm').addEventListener('change', function() {
            this.submit();
        });
    </script>
</body>
</html>