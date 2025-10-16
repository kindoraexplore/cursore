-- Kindora Travel Website Database Schema
-- Created for MySQL/MariaDB

CREATE DATABASE IF NOT EXISTS kindora_travel;
USE kindora_travel;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    date_of_birth DATE,
    profile_image VARCHAR(255),
    is_admin BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    email_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Destinations table
CREATE TABLE destinations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    country VARCHAR(50) NOT NULL,
    continent VARCHAR(20) NOT NULL,
    description TEXT,
    short_description VARCHAR(255),
    image_url VARCHAR(255),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    category ENUM('city', 'beach', 'mountain', 'nature', 'culture', 'adventure') NOT NULL,
    budget_level ENUM('low', 'medium', 'high') NOT NULL,
    rating DECIMAL(3, 2) DEFAULT 0.00,
    review_count INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Travel packages table
CREATE TABLE packages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    short_description VARCHAR(255),
    image_url VARCHAR(255),
    price DECIMAL(10, 2) NOT NULL,
    duration_days INT NOT NULL,
    max_participants INT,
    category ENUM('summer', 'winter', 'monsoon', 'adventure', 'luxury', 'budget') NOT NULL,
    difficulty_level ENUM('easy', 'medium', 'hard') DEFAULT 'easy',
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Package destinations (many-to-many relationship)
CREATE TABLE package_destinations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    package_id INT NOT NULL,
    destination_id INT NOT NULL,
    day_number INT NOT NULL,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_package_destination_day (package_id, destination_id, day_number)
);

-- Bookings table
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    package_id INT NOT NULL,
    booking_reference VARCHAR(20) UNIQUE NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    participants INT DEFAULT 1,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
    special_requests TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
);

-- Reviews table
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    destination_id INT,
    package_id INT,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(100),
    comment TEXT,
    is_verified BOOLEAN DEFAULT FALSE,
    is_approved BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE,
    CHECK ((destination_id IS NOT NULL AND package_id IS NULL) OR (destination_id IS NULL AND package_id IS NOT NULL))
);

-- Newsletter subscriptions
CREATE TABLE newsletter_subscriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at TIMESTAMP NULL
);

-- Contact messages
CREATE TABLE contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied', 'closed') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- FAQ table
CREATE TABLE faqs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(50),
    order_index INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Blog posts
CREATE TABLE blog_posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(250) UNIQUE NOT NULL,
    content TEXT NOT NULL,
    excerpt TEXT,
    featured_image VARCHAR(255),
    author_id INT NOT NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Blog categories
CREATE TABLE blog_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL,
    slug VARCHAR(60) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Blog post categories (many-to-many)
CREATE TABLE blog_post_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    category_id INT NOT NULL,
    FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_post_category (post_id, category_id)
);

-- Gallery images
CREATE TABLE gallery_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100),
    description TEXT,
    image_url VARCHAR(255) NOT NULL,
    destination_id INT,
    package_id INT,
    category VARCHAR(50),
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
);

-- User sessions
CREATE TABLE user_sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Password reset tokens
CREATE TABLE password_reset_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Email verification tokens
CREATE TABLE email_verification_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample data
INSERT INTO destinations (name, country, continent, description, short_description, image_url, latitude, longitude, category, budget_level, rating, review_count, is_featured) VALUES
('Eiffel Tower', 'France', 'Europe', 'The iconic iron lattice tower located on the Champ de Mars in Paris, France.', 'Iconic Parisian landmark and symbol of France', 'assets/images/places/eiffel_tower.jpg', 48.8584, 2.2945, 'city', 'medium', 4.8, 1250, TRUE),
('Statue of Liberty', 'United States', 'North America', 'A neoclassical sculpture on Liberty Island in New York Harbor.', 'Symbol of freedom and democracy', 'assets/images/places/statue_of_liberty.jpg', 40.6892, -74.0445, 'city', 'medium', 4.7, 980, TRUE),
('Sydney Opera House', 'Australia', 'Australia', 'A multi-venue performing arts centre in Sydney, Australia.', 'World-famous performing arts venue', 'assets/images/places/sydney_opera_house.jpg', -33.8568, 151.2153, 'city', 'high', 4.9, 1100, TRUE),
('Mount Fuji', 'Japan', 'Asia', 'An active volcano and the highest mountain in Japan.', 'Sacred mountain and symbol of Japan', 'assets/images/places/mount_fuji.jpg', 35.3606, 138.7274, 'mountain', 'medium', 4.8, 850, TRUE),
('Grand Canyon', 'United States', 'North America', 'A steep-sided canyon carved by the Colorado River in Arizona.', 'Natural wonder and geological marvel', 'assets/images/places/grand_canyon.jpg', 36.1069, -112.1129, 'nature', 'low', 4.9, 1200, TRUE);

INSERT INTO packages (name, description, short_description, image_url, price, duration_days, max_participants, category, difficulty_level, is_featured) VALUES
('Summer Escape', 'Enjoy sunny beaches, tropical islands, and exotic adventures under the warm sun.', 'Beach destinations with water activities and nightlife', 'assets/images/packages/summer.jpg', 599.00, 7, 20, 'summer', 'easy', TRUE),
('Winter Wonderland', 'Experience snowy mountains, skiing adventures, and cozy winter retreats.', 'Skiing, cozy lodges, and snow activities', 'assets/images/packages/winter.jpg', 799.00, 5, 15, 'winter', 'medium', TRUE),
('Monsoon Magic', 'Explore lush greenery, breathtaking waterfalls, and refreshing monsoon experiences.', 'Green landscapes, waterfalls, and eco tourism', 'assets/images/packages/monsoon.jpg', 399.00, 6, 25, 'monsoon', 'easy', TRUE);

INSERT INTO faqs (question, answer, category, order_index) VALUES
('What is Kindora?', 'Kindora is a comprehensive travel platform that helps you explore the world with a focus on authentic experiences, cultural immersion, and sustainable tourism.', 'general', 1),
('Is Kindora free to use?', 'Yes! Our travel guides, destination information, and basic features are completely free. We only charge for actual bookings and premium services.', 'general', 2),
('Can I suggest places to add?', 'Absolutely! We love hearing from our community. You can suggest new destinations, share your travel experiences, and even contribute to our travel guides.', 'general', 3),
('What services does Kindora provide?', 'We offer comprehensive travel planning including destination guides, accommodation booking, activity reservations, transportation options, travel insurance, and 24/7 customer support.', 'services', 1),
('How do I book a trip?', 'Booking is simple! Browse our destinations, select your preferred package or create a custom itinerary, choose your dates, and complete the booking process.', 'booking', 1),
('What if I need to cancel my trip?', 'We understand that plans can change. Our cancellation policy varies by package and timing, but we always try to accommodate our travelers.', 'booking', 2);

INSERT INTO blog_categories (name, slug, description) VALUES
('Travel Tips', 'travel-tips', 'Essential tips and advice for travelers'),
('Destinations', 'destinations', 'Detailed guides about specific destinations'),
('Adventure', 'adventure', 'Adventure travel and outdoor activities'),
('Culture', 'culture', 'Cultural experiences and local traditions'),
('Food & Dining', 'food-dining', 'Culinary experiences around the world');

-- Create indexes for better performance
CREATE INDEX idx_destinations_category ON destinations(category);
CREATE INDEX idx_destinations_budget ON destinations(budget_level);
CREATE INDEX idx_destinations_rating ON destinations(rating);
CREATE INDEX idx_packages_category ON packages(category);
CREATE INDEX idx_bookings_user ON bookings(user_id);
CREATE INDEX idx_bookings_status ON bookings(status);
CREATE INDEX idx_reviews_destination ON reviews(destination_id);
CREATE INDEX idx_reviews_package ON reviews(package_id);
CREATE INDEX idx_blog_posts_status ON blog_posts(status);
CREATE INDEX idx_blog_posts_published ON blog_posts(published_at);