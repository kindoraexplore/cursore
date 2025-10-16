<?php
require_once '../config/database.php';

// Get filter parameters
$category = $_GET['category'] ?? '';
$budget = $_GET['budget'] ?? '';
$search = $_GET['search'] ?? '';
$continent = $_GET['continent'] ?? '';

// Build query
$whereConditions = ['d.is_active = 1'];
$params = [];

if ($category) {
    $whereConditions[] = 'd.category = ?';
    $params[] = $category;
}

if ($budget) {
    $whereConditions[] = 'd.budget_level = ?';
    $params[] = $budget;
}

if ($search) {
    $whereConditions[] = '(d.name LIKE ? OR d.country LIKE ? OR d.description LIKE ?)';
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($continent) {
    $whereConditions[] = 'd.continent = ?';
    $params[] = $continent;
}

$whereClause = implode(' AND ', $whereConditions);

$destinations = $db->fetchAll(
    "SELECT d.*, 
            (SELECT COUNT(*) FROM reviews r WHERE r.destination_id = d.id AND r.is_approved = 1) as review_count
     FROM destinations d 
     WHERE {$whereClause} 
     ORDER BY d.is_featured DESC, d.rating DESC, d.name ASC",
    $params
);

// Get unique continents for filter
$continents = $db->fetchAll("SELECT DISTINCT continent FROM destinations WHERE is_active = 1 ORDER BY continent");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Destinations - Kindora</title>
    <link href="../assets/css/styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .explore-hero {
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
        
        .filter-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .results-count {
            color: var(--text-secondary);
            font-size: 1.125rem;
        }
        
        .sort-options {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .sort-options select {
            padding: 0.5rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.875rem;
        }
        
        .destinations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }
        
        .destination-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .destination-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .card-image {
            position: relative;
            height: 250px;
            overflow: hidden;
        }
        
        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .destination-card:hover .card-image img {
            transform: scale(1.1);
        }
        
        .card-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.3));
            opacity: 0;
            transition: opacity 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 1.5rem;
        }
        
        .destination-card:hover .card-overlay {
            opacity: 1;
        }
        
        .card-rating {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            color: var(--secondary-color);
            font-weight: 600;
            background: rgba(255, 255, 255, 0.9);
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }
        
        .card-price {
            color: white;
            font-weight: 700;
            font-size: 1.25rem;
            background: var(--gradient-primary);
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }
        
        .card-content {
            padding: 2rem;
        }
        
        .card-content h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }
        
        .card-location {
            color: var(--text-secondary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .card-description {
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        
        .card-tags {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }
        
        .tag {
            background: var(--bg-secondary);
            color: var(--text-secondary);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .card-actions {
            display: flex;
            gap: 1rem;
        }
        
        .btn-card {
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
        
        .btn-primary-card {
            background: var(--gradient-primary);
            color: white;
        }
        
        .btn-primary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 51, 102, 0.3);
        }
        
        .btn-secondary-card {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }
        
        .btn-secondary-card:hover {
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
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 3rem;
        }
        
        .pagination a,
        .pagination span {
            padding: 0.75rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            text-decoration: none;
            color: var(--text-primary);
            transition: all 0.3s ease;
        }
        
        .pagination a:hover,
        .pagination .current {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }
            
            .destinations-grid {
                grid-template-columns: 1fr;
            }
            
            .results-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .sort-options {
                justify-content: center;
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
                <a href="explore.php" class="nav-link active">Places to Go</a>
                <a href="things_to_do.php" class="nav-link">Things to Do</a>
                <a href="booking.php" class="nav-link">Plan Your Trip</a>
                <a href="login.php" class="nav-link cta-nav">Login</a>
            </div>
        </div>
    </nav>

    <!-- Explore Hero -->
    <section class="explore-hero">
        <div class="container">
            <h1>Explore Amazing Destinations</h1>
            <p>Discover the world's most beautiful places and plan your next adventure</p>
        </div>
    </section>

    <!-- Filter Section -->
    <div class="container">
        <div class="filter-section">
            <form method="GET" id="filterForm">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="search">Search Destinations</label>
                        <input type="text" id="search" name="search" placeholder="Search by name, country..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label for="continent">Continent</label>
                        <select id="continent" name="continent">
                            <option value="">All Continents</option>
                            <?php foreach ($continents as $cont): ?>
                                <option value="<?php echo $cont['continent']; ?>" <?php echo $continent === $cont['continent'] ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($cont['continent']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="category">Category</label>
                        <select id="category" name="category">
                            <option value="">All Categories</option>
                            <option value="city" <?php echo $category === 'city' ? 'selected' : ''; ?>>City</option>
                            <option value="beach" <?php echo $category === 'beach' ? 'selected' : ''; ?>>Beach</option>
                            <option value="mountain" <?php echo $category === 'mountain' ? 'selected' : ''; ?>>Mountain</option>
                            <option value="nature" <?php echo $category === 'nature' ? 'selected' : ''; ?>>Nature</option>
                            <option value="culture" <?php echo $category === 'culture' ? 'selected' : ''; ?>>Culture</option>
                            <option value="adventure" <?php echo $category === 'adventure' ? 'selected' : ''; ?>>Adventure</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="budget">Budget Level</label>
                        <select id="budget" name="budget">
                            <option value="">Any Budget</option>
                            <option value="low" <?php echo $budget === 'low' ? 'selected' : ''; ?>>Low ($0 - $500)</option>
                            <option value="medium" <?php echo $budget === 'medium' ? 'selected' : ''; ?>>Medium ($500 - $2000)</option>
                            <option value="high" <?php echo $budget === 'high' ? 'selected' : ''; ?>>High ($2000+)</option>
                        </select>
                    </div>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="explore.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear Filters
                    </a>
                </div>
            </form>
        </div>

        <!-- Results -->
        <div class="results-header">
            <div class="results-count">
                Found <?php echo count($destinations); ?> destination<?php echo count($destinations) !== 1 ? 's' : ''; ?>
            </div>
            <div class="sort-options">
                <label for="sort">Sort by:</label>
                <select id="sort" onchange="sortDestinations(this.value)">
                    <option value="featured">Featured</option>
                    <option value="rating">Rating</option>
                    <option value="name">Name</option>
                    <option value="price_low">Price: Low to High</option>
                    <option value="price_high">Price: High to Low</option>
                </select>
            </div>
        </div>

        <!-- Destinations Grid -->
        <?php if (empty($destinations)): ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h3>No destinations found</h3>
                <p>Try adjusting your search criteria or browse all destinations</p>
                <a href="explore.php" class="btn btn-primary">View All Destinations</a>
            </div>
        <?php else: ?>
            <div class="destinations-grid" id="destinationsGrid">
                <?php foreach ($destinations as $destination): ?>
                    <div class="destination-card" data-rating="<?php echo $destination['rating']; ?>" data-name="<?php echo strtolower($destination['name']); ?>">
                        <div class="card-image">
                            <img src="<?php echo htmlspecialchars($destination['image_url']); ?>" alt="<?php echo htmlspecialchars($destination['name']); ?>" onerror="this.src='../assets/images/placeholder.jpg'">
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
                            <div class="card-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($destination['country']); ?>, <?php echo ucfirst($destination['continent']); ?></span>
                            </div>
                            <p class="card-description"><?php echo htmlspecialchars($destination['short_description']); ?></p>
                            <div class="card-tags">
                                <span class="tag"><?php echo ucfirst($destination['category']); ?></span>
                                <span class="tag"><?php echo ucfirst($destination['budget_level']); ?> Budget</span>
                                <?php if ($destination['is_featured']): ?>
                                    <span class="tag" style="background: var(--secondary-color); color: white;">Featured</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-actions">
                                <a href="destination_detail.php?id=<?php echo $destination['id']; ?>" class="btn-card btn-primary-card">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <a href="booking.php?destination=<?php echo $destination['id']; ?>" class="btn-card btn-secondary-card">
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
        function sortDestinations(sortBy) {
            const grid = document.getElementById('destinationsGrid');
            const cards = Array.from(grid.children);
            
            cards.sort((a, b) => {
                switch (sortBy) {
                    case 'rating':
                        return parseFloat(b.dataset.rating) - parseFloat(a.dataset.rating);
                    case 'name':
                        return a.dataset.name.localeCompare(b.dataset.name);
                    case 'price_low':
                        // This would need price data in the dataset
                        return 0;
                    case 'price_high':
                        // This would need price data in the dataset
                        return 0;
                    default:
                        return 0;
                }
            });
            
            // Clear grid and re-append sorted cards
            grid.innerHTML = '';
            cards.forEach(card => grid.appendChild(card));
        }
        
        // Auto-submit form on filter change
        document.getElementById('filterForm').addEventListener('change', function() {
            this.submit();
        });
    </script>
</body>
</html>