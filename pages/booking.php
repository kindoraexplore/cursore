<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

$user = $auth->getCurrentUser();
$packages = $db->fetchAll("SELECT * FROM packages WHERE is_active = 1 ORDER BY is_featured DESC, name ASC");
$destinations = $db->fetchAll("SELECT * FROM destinations WHERE is_active = 1 ORDER BY is_featured DESC, name ASC");

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'book') {
        if (!$user) {
            $error = 'Please login to make a booking';
        } else {
            try {
                // Generate booking reference
                $bookingRef = 'KDR' . date('Ymd') . rand(1000, 9999);
                
                // Calculate total amount
                $package = $db->fetchOne("SELECT * FROM packages WHERE id = ?", [$_POST['package_id']]);
                $totalAmount = $package['price'] * $_POST['participants'];
                
                // Create booking
                $bookingId = $db->insert('bookings', [
                    'user_id' => $user['id'],
                    'package_id' => $_POST['package_id'],
                    'booking_reference' => $bookingRef,
                    'start_date' => $_POST['start_date'],
                    'end_date' => $_POST['end_date'],
                    'participants' => $_POST['participants'],
                    'total_amount' => $totalAmount,
                    'special_requests' => $_POST['special_requests'] ?? ''
                ]);
                
                $success = "Booking successful! Your booking reference is: {$bookingRef}";
                
            } catch (Exception $e) {
                $error = 'Booking failed: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan Your Trip - Kindora</title>
    <link href="../assets/css/styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .booking-hero {
            background: linear-gradient(135deg, #003366 0%, #004080 100%);
            color: white;
            padding: 8rem 0 4rem;
            text-align: center;
        }
        
        .booking-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .booking-form-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            margin: -2rem auto 4rem;
            max-width: 800px;
            position: relative;
            z-index: 10;
        }
        
        .form-section {
            margin-bottom: 2rem;
        }
        
        .form-section h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-primary);
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
        }
        
        .package-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .package-card {
            border: 2px solid #e5e7eb;
            border-radius: 15px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .package-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 51, 102, 0.1);
        }
        
        .package-card.selected {
            border-color: var(--primary-color);
            background: rgba(0, 51, 102, 0.05);
        }
        
        .package-card h4 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .package-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent-color);
            margin: 1rem 0;
        }
        
        .package-features {
            list-style: none;
            padding: 0;
        }
        
        .package-features li {
            padding: 0.25rem 0;
            color: var(--text-secondary);
        }
        
        .package-features li i {
            color: var(--secondary-color);
            margin-right: 0.5rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            font-weight: 500;
        }
        
        .alert-error {
            background: #fee;
            color: #c53030;
            border: 1px solid #feb2b2;
        }
        
        .alert-success {
            background: #f0fff4;
            color: #2f855a;
            border: 1px solid #9ae6b4;
        }
        
        .booking-summary {
            background: var(--bg-secondary);
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .summary-row:last-child {
            border-bottom: none;
            font-weight: 700;
            font-size: 1.125rem;
            color: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .booking-form-container {
                margin: -1rem auto 2rem;
                padding: 2rem 1rem;
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
                <a href="things_to_do.php" class="nav-link">Things to Do</a>
                <a href="booking.php" class="nav-link">Plan Your Trip</a>
                <?php if ($user): ?>
                    <a href="profile.php" class="nav-link">Profile</a>
                    <a href="../includes/logout.php" class="nav-link">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link cta-nav">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Booking Hero -->
    <section class="booking-hero">
        <div class="booking-container">
            <h1>Plan Your Perfect Trip</h1>
            <p>Create unforgettable memories with our carefully crafted travel packages</p>
        </div>
    </section>

    <!-- Booking Form -->
    <div class="booking-container">
        <div class="booking-form-container">
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="bookingForm">
                <input type="hidden" name="action" value="book">
                
                <!-- Package Selection -->
                <div class="form-section">
                    <h3><i class="fas fa-gift"></i> Choose Your Package</h3>
                    <div class="package-grid" id="packageGrid">
                        <?php foreach ($packages as $package): ?>
                            <div class="package-card" data-package-id="<?php echo $package['id']; ?>" data-price="<?php echo $package['price']; ?>">
                                <h4><?php echo htmlspecialchars($package['name']); ?></h4>
                                <p><?php echo htmlspecialchars($package['short_description']); ?></p>
                                <div class="package-price">$<?php echo number_format($package['price']); ?></div>
                                <ul class="package-features">
                                    <li><i class="fas fa-calendar"></i> <?php echo $package['duration_days']; ?> days</li>
                                    <li><i class="fas fa-users"></i> Max <?php echo $package['max_participants']; ?> people</li>
                                    <li><i class="fas fa-star"></i> <?php echo ucfirst($package['difficulty_level']); ?> level</li>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="package_id" id="selectedPackageId" required>
                </div>

                <!-- Travel Details -->
                <div class="form-section">
                    <h3><i class="fas fa-calendar-alt"></i> Travel Details</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" id="start_date" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label for="end_date">End Date</label>
                            <input type="date" id="end_date" name="end_date" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="participants">Number of Participants</label>
                        <input type="number" id="participants" name="participants" min="1" max="20" value="1" required>
                    </div>
                </div>

                <!-- Special Requests -->
                <div class="form-section">
                    <h3><i class="fas fa-comment"></i> Special Requests</h3>
                    <div class="form-group">
                        <label for="special_requests">Any special requirements or requests?</label>
                        <textarea id="special_requests" name="special_requests" rows="4" placeholder="Tell us about any dietary restrictions, accessibility needs, or special occasions..."></textarea>
                    </div>
                </div>

                <!-- Booking Summary -->
                <div class="booking-summary" id="bookingSummary" style="display: none;">
                    <h3><i class="fas fa-receipt"></i> Booking Summary</h3>
                    <div class="summary-row">
                        <span>Package:</span>
                        <span id="summaryPackage">-</span>
                    </div>
                    <div class="summary-row">
                        <span>Duration:</span>
                        <span id="summaryDuration">-</span>
                    </div>
                    <div class="summary-row">
                        <span>Participants:</span>
                        <span id="summaryParticipants">-</span>
                    </div>
                    <div class="summary-row">
                        <span>Price per person:</span>
                        <span id="summaryPricePerPerson">-</span>
                    </div>
                    <div class="summary-row">
                        <span>Total Amount:</span>
                        <span id="summaryTotal">-</span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 2rem;">
                    <i class="fas fa-credit-card"></i> 
                    <?php echo $user ? 'Complete Booking' : 'Login to Book'; ?>
                </button>
            </form>
        </div>
    </div>

    <script>
        // Package selection
        document.querySelectorAll('.package-card').forEach(card => {
            card.addEventListener('click', function() {
                // Remove selected class from all cards
                document.querySelectorAll('.package-card').forEach(c => c.classList.remove('selected'));
                
                // Add selected class to clicked card
                this.classList.add('selected');
                
                // Set package ID
                document.getElementById('selectedPackageId').value = this.dataset.packageId;
                
                // Update summary
                updateBookingSummary();
            });
        });

        // Update booking summary
        function updateBookingSummary() {
            const selectedCard = document.querySelector('.package-card.selected');
            if (!selectedCard) return;

            const packageName = selectedCard.querySelector('h4').textContent;
            const packagePrice = parseFloat(selectedCard.dataset.price);
            const participants = parseInt(document.getElementById('participants').value) || 1;
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            // Calculate duration
            let duration = '-';
            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                duration = diffDays + ' days';
            }

            // Update summary
            document.getElementById('summaryPackage').textContent = packageName;
            document.getElementById('summaryDuration').textContent = duration;
            document.getElementById('summaryParticipants').textContent = participants;
            document.getElementById('summaryPricePerPerson').textContent = '$' + packagePrice.toFixed(2);
            document.getElementById('summaryTotal').textContent = '$' + (packagePrice * participants).toFixed(2);
            
            // Show summary
            document.getElementById('bookingSummary').style.display = 'block';
        }

        // Event listeners for form changes
        document.getElementById('participants').addEventListener('input', updateBookingSummary);
        document.getElementById('start_date').addEventListener('change', updateBookingSummary);
        document.getElementById('end_date').addEventListener('change', updateBookingSummary);

        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('start_date').min = today;
        document.getElementById('end_date').min = today;

        // Update end date when start date changes
        document.getElementById('start_date').addEventListener('change', function() {
            const startDate = new Date(this.value);
            const endDate = new Date(startDate);
            endDate.setDate(endDate.getDate() + 1);
            document.getElementById('end_date').min = endDate.toISOString().split('T')[0];
        });
    </script>
</body>
</html>