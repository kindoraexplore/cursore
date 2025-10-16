<?php
/**
 * Authentication System
 * Kindora Travel Website
 */

session_start();
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    public function register($userData) {
        try {
            // Validate input
            $this->validateRegistrationData($userData);
            
            // Check if user already exists
            if ($this->userExists($userData['email'], $userData['username'])) {
                throw new Exception('User already exists with this email or username');
            }
            
            // Hash password
            $userData['password_hash'] = password_hash($userData['password'], PASSWORD_DEFAULT);
            unset($userData['password']);
            unset($userData['confirm_password']);
            
            // Insert user
            $userId = $this->db->insert('users', $userData);
            
            // Generate email verification token
            $this->generateEmailVerificationToken($userId);
            
            return [
                'success' => true,
                'message' => 'Registration successful. Please check your email for verification.',
                'user_id' => $userId
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function login($email, $password, $remember = false) {
        try {
            // Get user by email
            $user = $this->db->fetchOne(
                "SELECT * FROM users WHERE email = ? AND is_active = 1",
                [$email]
            );
            
            if (!$user || !password_verify($password, $user['password_hash'])) {
                throw new Exception('Invalid email or password');
            }
            
            if (!$user['email_verified']) {
                throw new Exception('Please verify your email before logging in');
            }
            
            // Create session
            $this->createSession($user);
            
            // Remember me functionality
            if ($remember) {
                $this->createRememberToken($user['id']);
            }
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => $this->sanitizeUserData($user)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function logout() {
        // Destroy session
        session_destroy();
        
        // Clear remember token if exists
        if (isset($_COOKIE['remember_token'])) {
            $this->clearRememberToken($_COOKIE['remember_token']);
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        return true;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return $this->db->fetchOne(
            "SELECT id, username, email, first_name, last_name, phone, profile_image, is_admin FROM users WHERE id = ?",
            [$_SESSION['user_id']]
        );
    }
    
    public function isAdmin() {
        $user = $this->getCurrentUser();
        return $user && $user['is_admin'];
    }
    
    public function forgotPassword($email) {
        try {
            $user = $this->db->fetchOne(
                "SELECT id FROM users WHERE email = ? AND is_active = 1",
                [$email]
            );
            
            if (!$user) {
                throw new Exception('No account found with this email');
            }
            
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $this->db->insert('password_reset_tokens', [
                'user_id' => $user['id'],
                'token' => $token,
                'expires_at' => $expiresAt
            ]);
            
            // Send email (implement email sending)
            $this->sendPasswordResetEmail($email, $token);
            
            return [
                'success' => true,
                'message' => 'Password reset link sent to your email'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function resetPassword($token, $newPassword) {
        try {
            // Validate token
            $resetToken = $this->db->fetchOne(
                "SELECT * FROM password_reset_tokens WHERE token = ? AND expires_at > NOW() AND used_at IS NULL",
                [$token]
            );
            
            if (!$resetToken) {
                throw new Exception('Invalid or expired reset token');
            }
            
            // Update password
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $this->db->update('users', 
                ['password_hash' => $passwordHash], 
                'id = ?', 
                [$resetToken['user_id']]
            );
            
            // Mark token as used
            $this->db->update('password_reset_tokens', 
                ['used_at' => date('Y-m-d H:i:s')], 
                'id = ?', 
                [$resetToken['id']]
            );
            
            return [
                'success' => true,
                'message' => 'Password reset successful'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function verifyEmail($token) {
        try {
            $verificationToken = $this->db->fetchOne(
                "SELECT * FROM email_verification_tokens WHERE token = ? AND expires_at > NOW() AND used_at IS NULL",
                [$token]
            );
            
            if (!$verificationToken) {
                throw new Exception('Invalid or expired verification token');
            }
            
            // Update user email verification status
            $this->db->update('users', 
                ['email_verified' => 1], 
                'id = ?', 
                [$verificationToken['user_id']]
            );
            
            // Mark token as used
            $this->db->update('email_verification_tokens', 
                ['used_at' => date('Y-m-d H:i:s')], 
                'id = ?', 
                [$verificationToken['id']]
            );
            
            return [
                'success' => true,
                'message' => 'Email verified successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    private function validateRegistrationData($data) {
        $required = ['username', 'email', 'password', 'first_name', 'last_name'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field {$field} is required");
            }
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        
        if (strlen($data['password']) < 8) {
            throw new Exception('Password must be at least 8 characters long');
        }
        
        if ($data['password'] !== $data['confirm_password']) {
            throw new Exception('Passwords do not match');
        }
    }
    
    private function userExists($email, $username) {
        $user = $this->db->fetchOne(
            "SELECT id FROM users WHERE email = ? OR username = ?",
            [$email, $username]
        );
        
        return $user !== false;
    }
    
    private function createSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
        
        // Regenerate session ID for security
        session_regenerate_id(true);
    }
    
    private function createRememberToken($userId) {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        $this->db->insert('user_sessions', [
            'id' => $token,
            'user_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'expires_at' => $expiresAt
        ]);
        
        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
    }
    
    private function clearRememberToken($token) {
        $this->db->delete('user_sessions', 'id = ?', [$token]);
    }
    
    private function generateEmailVerificationToken($userId) {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $this->db->insert('email_verification_tokens', [
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => $expiresAt
        ]);
        
        // Send verification email (implement email sending)
        $this->sendVerificationEmail($userId, $token);
    }
    
    private function sendVerificationEmail($userId, $token) {
        // Implement email sending logic here
        // For now, just log the token
        error_log("Verification token for user {$userId}: {$token}");
    }
    
    private function sendPasswordResetEmail($email, $token) {
        // Implement email sending logic here
        // For now, just log the token
        error_log("Password reset token for {$email}: {$token}");
    }
    
    private function sanitizeUserData($user) {
        unset($user['password_hash']);
        return $user;
    }
}

// Initialize auth
$auth = new Auth();

// Auto-login with remember token
if (!$auth->isLoggedIn() && isset($_COOKIE['remember_token'])) {
    $session = $db->fetchOne(
        "SELECT u.* FROM users u 
         JOIN user_sessions s ON u.id = s.user_id 
         WHERE s.id = ? AND s.expires_at > NOW() AND u.is_active = 1",
        [$_COOKIE['remember_token']]
    );
    
    if ($session) {
        $auth->createSession($session);
    }
}
?>