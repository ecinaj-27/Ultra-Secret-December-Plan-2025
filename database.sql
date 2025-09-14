-- Ultra Secret December Plan 2025 Database
-- Relationship Website Database Schema

CREATE DATABASE IF NOT EXISTS secret_plan_db;
USE secret_plan_db;

-- Roles table
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    permissions JSON,
    level INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    passcode VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    profile_picture VARCHAR(255),
    password_hint VARCHAR(255) DEFAULT 'Anniversary Date',
    role_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
);

-- Posts table for "How I See Her" content
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255),
    content TEXT NOT NULL,
    type ENUM('love_letter', 'note', 'compliment') DEFAULT 'note',
    is_public BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Timeline events for "Us" page
CREATE TABLE timeline_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    image_path VARCHAR(255),
    caption TEXT,
    position_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Locations for interactive map
CREATE TABLE locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    caption TEXT,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    image_path VARCHAR(255),
    visit_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Movies/Songs
CREATE TABLE media_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    type ENUM('movie', 'song', 'series') NOT NULL,
    description TEXT,
    image_path VARCHAR(255),
    external_link VARCHAR(500),
    rating INT DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- To-do items
CREATE TABLE todo_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category ENUM('Lab', 'School', 'Personal', 'Relationship') DEFAULT 'Personal',
    is_completed BOOLEAN DEFAULT FALSE,
    due_date DATE,
    position_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Love letter scheduler
CREATE TABLE scheduled_letters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    scheduled_date DATE NOT NULL,
    is_sent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Art wall items
CREATE TABLE art_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    story TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Study tracker entries
CREATE TABLE study_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(100) NOT NULL,
    hours_studied DECIMAL(4,2) NOT NULL,
    task_description TEXT,
    entry_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Flashcards
CREATE TABLE flashcards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    front_text TEXT NOT NULL,
    back_text TEXT NOT NULL,
    category VARCHAR(100),
    difficulty ENUM('Easy', 'Medium', 'Hard') DEFAULT 'Medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Lab notebook entries
CREATE TABLE lab_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    tags VARCHAR(500),
    entry_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Resource vault files
CREATE TABLE resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(100),
    file_size INT,
    tags VARCHAR(500),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Wish jar items
CREATE TABLE wish_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category ENUM('Date Idea', 'Travel Plan', 'Gift Idea', 'Other') DEFAULT 'Date Idea',
    is_fulfilled BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default roles
INSERT INTO roles (id, name, display_name, description, level, permissions) VALUES
(1, 'user', 'Regular User', 'Standard user with basic access to personal features', 1, '{"can_view_profile": true, "can_edit_profile": true, "can_add_posts": true, "can_manage_todos": true}'),
(2, 'moderator', 'Moderator', 'Limited admin access for content moderation', 2, '{"can_view_profile": true, "can_edit_profile": true, "can_add_posts": true, "can_manage_todos": true, "can_moderate_content": true, "can_view_analytics": true}'),
(3, 'admin', 'Administrator', 'Full admin access to all features and settings', 3, '{"can_view_profile": true, "can_edit_profile": true, "can_add_posts": true, "can_manage_todos": true, "can_moderate_content": true, "can_view_analytics": true, "can_manage_users": true, "can_manage_roles": true, "can_access_admin_tools": true}');

-- No default admin user created
-- You will manually assign admin privileges after creating users

-- Site content management
CREATE TABLE site_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content_key VARCHAR(100) UNIQUE NOT NULL,
    title VARCHAR(255),
    content TEXT,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Photo booth uploads
CREATE TABLE photo_booth (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    caption TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Love letter scheduler
CREATE TABLE love_letters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    scheduled_date DATE NOT NULL,
    is_sent BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample data
INSERT INTO timeline_events (title, description, event_date, position_order, caption) VALUES
('First Meeting', 'The day we first met and everything changed', '2020-01-15', 1, 'The moment that changed everything'),
('First Date', 'Our magical first date at the coffee shop', '2020-02-14', 2, 'Coffee, conversation, and the beginning of forever'),
('Anniversary', 'The day we became official', '2020-03-20', 3, 'The day we promised to love each other always');

-- Insert default site content
INSERT INTO site_content (content_key, title, content) VALUES
('our_story', 'Our Story', 'Our journey started on a beautiful spring day. What began as a simple conversation quickly turned into something magical. We knew from the start that this was something special. Through the challenges of that year, we found strength in each other. Every video call, every text message, every moment we shared brought us closer together. We supported each other through studies, career changes, and personal growth. Every achievement was celebrated together, every setback was faced as a team. As we continue to grow together, this website serves as a testament to our love and a tool to help us navigate both our relationship and our individual journeys.'),
('anniversary_letter', 'Anniversary Letter', 'My Dearest Love,

As I sit here writing this letter, I am filled with overwhelming gratitude for the incredible journey we have shared together. Five years ago, you walked into my life and changed everything in the most beautiful way possible.

Every day with you is a gift. Your laughter is the melody that brightens my darkest moments, your kindness is the light that guides me through challenges, and your love is the foundation that makes everything possible.

I created this digital space as a tribute to us - to our memories, our dreams, and our future together. It is a place where I can express my love for you in ways that words alone cannot capture. Every feature, every design choice, every line of code was written with you in mind.

As you pursue your medical studies, know that I am here cheering you on every step of the way. Your dedication inspires me, your intelligence amazes me, and your compassion for others fills me with pride.

Here is to many more years of love, laughter, and adventures together. Here is to our past, our present, and all the beautiful tomorrows we will share.

With all my love,
Your devoted partner');

INSERT INTO media_items (title, type, description, rating) VALUES
('Your Favorite Movie', 'movie', 'The movie we always watch together', 5),
('Our Song', 'song', 'The song that reminds us of each other', 5),
('Binge-Worthy Series', 'series', 'The show we marathon together', 4);

INSERT INTO art_items (title, description, story) VALUES
('First Sketch', 'A simple drawing of you', 'This was the first time I tried to capture your beauty on paper'),
('Love Letter Art', 'A heart with our initials', 'I made this while thinking about our future together'),
('Anniversary Drawing', 'A detailed portrait', 'Spent hours perfecting every detail of your smile');

-- Insert sample location data
INSERT INTO locations (name, description, caption, latitude, longitude, visit_date) VALUES
('Central Park', 'Our first walk together in the city', 'Where we held hands for the first time', 40.785091, -73.968285, '2020-02-20'),
('The Coffee Shop', 'Our favorite spot for morning coffee', 'Every conversation here feels like magic', 40.758896, -73.985130, '2020-03-15'),
('Beach House', 'Our weekend getaway spot', 'Sunset walks and endless conversations', 40.678178, -73.944158, '2020-06-10');

