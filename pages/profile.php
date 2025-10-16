<?php
require_once '../includes/auth.php';

$user = $auth->getCurrentUser();

if (!$user) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_profile':
                try {
                    $updateData = [
                        'first_name' => $_POST['first_name'],
                        'last_name' => $_POST['last_name'],
                        'phone' => $_POST['phone'],
                        'email' => $_POST['email']
                    ];
                    
                    $db->update('users', $updateData, 'id = ?', [$user['id']]);
                    $success = 'Profile updated successfully';
                    $user = $auth->getCurrentUser(); // Refresh user data
                } catch (Exception $e) {
                    $error = 'Failed to update profile: ' . $e->getMessage();
                }
                break;
                
            case 'change_password':
                if (password_verify($_POST['current_password'], $user['password_hash'])) {
                    if ($_POST['new_password'] === $_POST['confirm_password']) {
                        $newPasswordHash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                        $db->update('users', ['password_hash' => $newPasswordHash], 'id = ?', [$user['id']]);
                        $success = 'Password changed successfully';
                    } else {
                        $error = 'New passwords do not match';
                    }
                } else {
                    $error = 'Current password is incorrect';
                }
                break;
        }
    }
}

// Get user bookings
$bookings = $db->fetchAll(
    "SELECT b.*, p.name as package_name, p.image_url 
     FROM bookings b 
     JOIN packages p ON b.package_id = p.id 
     WHERE b.user_id = ? 
     ORDER BY b.created_at DESC",
    [$user['id']]
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Kindora</title>
    <link href="../assets/css/styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .profile-hero {
            background: linear-gradient(135deg, #003366 0%, #004080 100%);
            color: white;
            padding: 8rem 0 4rem;
            text-align: center;
        }
        
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .profile-content {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 3rem;
            margin-top: -2rem;
            position: relative;
            z-index: 10;
        }
        
        .profile-sidebar {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            height: fit-content;
        }
        
        .profile-avatar {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 2rem;
            font-weight: 700;
        }
        
        .profile-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .profile-email {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        
        .profile-nav {
            list-style: none;
            padding: 0;
        }
        
        .profile-nav li {
            margin-bottom: 0.5rem;
        }
        
        .profile-nav a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .profile-nav a:hover,
        .profile-nav a.active {
            background: var(--bg-secondary);
            color: var(--primary-color);
        }
        
        .profile-main {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .profile-section {
            display: none;
        }
        
        .profile-section.active {
            display: block;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 2rem;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-primary);
        }
        
        .form-group input {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
        }
        
        .booking-card {
            background: var(--bg-secondary);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .booking-image {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            overflow: hidden;
            flex-shrink: 0;
        }
        
        .booking-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .booking-info {
            flex: 1;
        }
        
        .booking-info h4 {
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }
        
        .booking-details {
            display: flex;
            gap: 1rem;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
        .booking-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-completed {
            background: #d1ecf1;
            color: #0c5460;
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
        
        @media (max-width: 768px) {
            .profile-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .booking-card {
                flex-direction: column;
                text-align: center;
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
                <a href="profile.php" class="nav-link active">Profile</a>
                <a href="../includes/logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Profile Hero -->
    <section class="profile-hero">
        <div class="profile-container">
            <h1>Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
            <p>Manage your profile and view your travel history</p>
        </div>
    </section>

    <!-- Profile Content -->
    <div class="profile-container">
        <div class="profile-content">
            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <div class="avatar">
                        <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                    </div>
                    <div class="profile-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                    <div class="profile-email"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
                
                <ul class="profile-nav">
                    <li><a href="#profile" class="nav-link active" onclick="showSection('profile')">
                        <i class="fas fa-user"></i> Profile
                    </a></li>
                    <li><a href="#bookings" class="nav-link" onclick="showSection('bookings')">
                        <i class="fas fa-calendar-check"></i> My Bookings
                    </a></li>
                    <li><a href="#password" class="nav-link" onclick="showSection('password')">
                        <i class="fas fa-lock"></i> Change Password
                    </a></li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="profile-main">
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

                <!-- Profile Section -->
                <div class="profile-section active" id="profile-section">
                    <h2 class="section-title">Profile Information</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>

                <!-- Bookings Section -->
                <div class="profile-section" id="bookings-section">
                    <h2 class="section-title">My Bookings</h2>
                    <?php if (empty($bookings)): ?>
                        <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                            <i class="fas fa-calendar-times" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <h3>No bookings yet</h3>
                            <p>Start planning your next adventure!</p>
                            <a href="booking.php" class="btn btn-primary">Plan Your Trip</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($bookings as $booking): ?>
                            <div class="booking-card">
                                <div class="booking-image">
                                    <img src="<?php echo htmlspecialchars($booking['image_url']); ?>" alt="<?php echo htmlspecialchars($booking['package_name']); ?>" onerror="this.src='../assets/images/placeholder.jpg'">
                                </div>
                                <div class="booking-info">
                                    <h4><?php echo htmlspecialchars($booking['package_name']); ?></h4>
                                    <div class="booking-details">
                                        <span><i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($booking['start_date'])); ?></span>
                                        <span><i class="fas fa-users"></i> <?php echo $booking['participants']; ?> person<?php echo $booking['participants'] > 1 ? 's' : ''; ?></span>
                                        <span><i class="fas fa-dollar-sign"></i> $<?php echo number_format($booking['total_amount']); ?></span>
                                    </div>
                                    <div>
                                        <span class="booking-status status-<?php echo $booking['status']; ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                        <span style="margin-left: 1rem; color: var(--text-secondary); font-size: 0.875rem;">
                                            Ref: <?php echo htmlspecialchars($booking['booking_reference']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Password Section -->
                <div class="profile-section" id="password-section">
                    <h2 class="section-title">Change Password</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showSection(sectionName) {
            // Hide all sections
            document.querySelectorAll('.profile-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Remove active class from all nav links
            document.querySelectorAll('.profile-nav a').forEach(link => {
                link.classList.remove('active');
            });
            
            // Show selected section
            document.getElementById(sectionName + '-section').classList.add('active');
            
            // Add active class to clicked link
            event.target.classList.add('active');
        }
        
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>