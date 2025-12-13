# Our Secret Place - Relationship Website

A beautiful, comprehensive website built for couples to manage their relationship, store memories, and support academic studies. This project combines romantic features with practical tools for medical students.

## Table of Contents

- [Features Overview](#features-overview)
- [Pages & Functionality](#pages--functionality)
- [API Endpoints](#api-endpoints)
- [User Roles & Permissions](#user-roles--permissions)
- [Technology Stack](#technology-stack)
- [Installation](#installation)
- [Database Schema](#database-schema)
- [File Structure](#file-structure)
- [Security Features](#security-features)
- [Customization Guide](#customization-guide)

---

## Features Overview

### 🏠 **Landing Page (index.php)**
- Beautiful slideshow background with 4 rotating images
- Centered logo with heart icon and tagline "A digital space for us"
- Login and Register buttons with icons
- Responsive design with overlay effects
- Automatic redirect to home if already logged in

### 👤 **User Management**
- **Registration (register.php)**: New user signup with username, name, email, passcode, and customizable password hint
- **Login (login.php)**: Secure passcode-based authentication with password hint display
- **Passcode Login (passcode-login.php)**: Alternative login method
- **Profile Management (profile.php)**:
  - Update name, email, and password hint
  - Change passcode with confirmation
  - Upload and update profile picture
  - View member since date and last update time
  - Profile picture preview before upload
- **Logout (logout.php)**: Secure session termination

### 🏡 **Home Dashboard (home.php)**
- **Personalized Greeting**: Time-based greeting (morning/afternoon/evening) with user's name
- **Live Time Display**: Real-time clock with toggle to show/hide
- **Current Date Display**: Formatted date display
- **Anniversary Countdown**: Days until December 17, 2025
- **Daily Inspiration Card**: Random motivational quotes from database or fallback quotes
- **Interactive Calendar Widget**: Full calendar view with current date highlighting
- **Quick To-Do List**: Shows top 5 pending tasks with category badges
- **Statistics Dashboard**:
  - Total study hours
  - Study sessions count
  - Total flashcards created
  - To-do completion percentage
- **Recent Resources**: Last 5 uploaded study resources with tags

### 💕 **How I See Her (how-i-see-her.php)**
- **Random Compliment Generator**: 
  - "Today's Compliment" card with refresh button
  - Fetches compliments from database via API
  - Smooth animation on refresh
- **Post Types**:
  - Personal Notes
  - Love Letters (with unread badge system)
  - Compliments
- **Masonry Grid Layout**: Pinterest-style responsive grid
- **Post Features**:
  - Expandable posts with lightbox popup
  - Author name and timestamp
  - "Read More" button for full view
  - Unread badge for love letters
  - Automatic mark-as-read when opened
- **Admin Features** (admin only):
  - Add new notes/letters/compliments
  - Edit existing posts
  - Delete posts
  - Modal-based editing interface

### 🎬 **Movies & Songs (media.php)**
- **Three Media Sections**:
  - Movies We Love
  - Series We Binge
  - Our Playlist (Music)
- **Polaroid-Style Cards**: Vintage photo card design with hover effects
- **Rating System**: 1-5 star ratings displayed on each card
- **External Links**: "Watch Now" buttons linking to streaming services
- **Spotify Integration**:
  - Embed code support for songs
  - Full iframe embedding for Spotify tracks
  - Playlist management with cover image
  - Playlist link storage
- **Admin Features** (admin only):
  - Add/edit/delete media items
  - Upload cover images for movies/series
  - Add Spotify embed codes for songs
  - Edit playlist title, description, and cover image
  - Dynamic form fields based on media type

### 👫 **Us Page (us.php)**
- **Our Story Section**: Editable relationship story (admin only)
- **Relationship Timeline**:
  - Interactive horizontal timeline with clickable dots
  - Event dates, titles, descriptions, and captions
  - Image support for each event
  - Polaroid-style popup when clicking events
  - Hover tooltips showing event information
- **Places We've Been**:
  - Grid of location cards with images
  - Clickable cards opening lightbox with full details
  - Visit dates, descriptions, and captions
  - Latitude/longitude support (for future map integration)
- **Memory Statistics**:
  - Special moments count
  - Places visited count
  - Days until anniversary
  - Infinite love symbol
- **Admin Content Management**:
  - Tabbed interface for editing "Our Story" and "Anniversary Letter"
  - Real-time content updates

### 📸 **Photo Booth (photo-booth.php)**
- **Live Camera Capture**:
  - Webcam integration with start/stop controls
  - Real-time video preview
  - Capture button to take photos
  - Retake functionality
  - Save to Photo Booth with optional caption
  - Status messages and error handling
- **3x3 Photo Grid**: Instagram-style grid layout
- **Photo Features**:
  - Hover overlay showing caption and date
  - Click to view in full-screen lightbox
  - Upload date display
- **Admin Features** (admin only):
  - Upload photos via file input
  - Edit photo captions
  - Replace photos
  - Delete photos

### 🎨 **Art Wall (art-wall.php)**
- **Masonry Grid Layout**: Responsive Pinterest-style gallery
- **Artwork Features**:
  - Title, description, and story/message
  - Hover overlay with full details
  - Click to open detailed popup
  - Image zoom and full view
- **Admin Features** (admin only):
  - Upload artwork with title, description, and story
  - Edit existing artwork
  - Replace images
  - Delete artwork

### 🛠️ **Tools & Utilities (tools.php)**
- **Admin Tools Section** (admin only):
  - **Love Letter Scheduler**:
    - Schedule love letters for specific dates
    - Automatic delivery on scheduled date
    - Popup notifications when letters are due
    - Status tracking (pending/sent)
  - **Timeline Events Management**:
    - Add/edit/delete timeline events
    - Upload images for events
    - Set event dates, descriptions, and captions
  - **Locations Management**:
    - Add/edit/delete visited locations
    - Upload location photos
    - Set coordinates (latitude/longitude)
    - Add visit dates and memory captions
  - **Custom Compliments Management**:
    - Add custom compliments
    - Toggle active/inactive status
    - Delete compliments
  - **Daily Inspirations Management**:
    - Add custom daily quotes
    - Toggle active/inactive status
    - Delete inspirations
- **Art Wall Section**: Quick preview with link to full gallery
- **Study Tracker**: 
  - Log study hours by subject
  - Track study sessions
  - View recent study entries
  - Statistics display

### 🎓 **Everything MedBio (medbio.php)**
- **Tool Launcher Grid**: Quick access cards for all study tools
- **Study To-Do List**:
  - Categorized tasks (Lab, School, Personal, Relationship)
  - Due date tracking
  - Completion toggle
  - Delete functionality
  - Shared access (all users can see/edit all todos)
- **Study Tracker**:
  - Log study hours by subject
  - Entry date selection
  - Task description field
  - Total hours and sessions statistics
  - Recent study sessions list
  - Shared access (all users can see/edit all entries)
- **Flashcard Generator**:
  - Create flashcards with front/back text
  - Category organization
  - Difficulty levels (Easy, Medium, Hard)
  - Flip animation on click
  - Edit and delete functionality
  - Shared access (all users can see/edit all flashcards)
- **Lab Notebook**:
  - Digital lab entry system
  - Title, content, and tags
  - Entry date tracking
  - Tag-based organization
  - Shared access (all users can see/edit all entries)
- **Resource Vault**:
  - File upload system (PDF, DOC, PPT, images, etc.)
  - Tag-based organization
  - Search functionality by tags
  - File type icons
  - Download links
  - Delete functionality
  - Shared access (all users can see/edit all resources)
- **Modal Forms**: Quick-add modals for each tool type

### ℹ️ **About Page (about.php)**
- **Website Information Section**:
  - Purpose description
  - Privacy information
  - Accessibility features
  - Study support details
- **Key Features List**: Comprehensive feature overview
- **Sub-navigation System**: Tabbed interface for different sections

### 💌 **Anniversary Letter (anniversary-letter.php)**
- **Time-Locked Content**: Letter unlocks on December 17, 2025
- **Countdown Timer**: Live countdown showing days, hours, and minutes
- **Locked State**: Beautiful lock icon with countdown display
- **Unlocked State**: Full letter content display
- **Editable Content** (admin only): Update letter via Us page admin tools

### 🔐 **Navigation & Access Control**
- **Sidebar Navigation**:
  - Collapsible sidebar with toggle button
  - User profile dropdown
  - Main navigation links
  - Dark mode toggle
  - Logout button
- **More Lock Feature**: 
  - Passcode-protected access to additional pages
  - Unlocks: How I See Her, Movies/Songs, Anniversary Letter, Us, Photo Booth, Art Wall
- **Scheduled Letter Popups**: Automatic popup notifications for scheduled love letters

---

## API Endpoints

### `/api/get-compliments.php`
- **Method**: GET
- **Description**: Returns list of active compliments from database
- **Response**: JSON with `compliments` array

### `/api/get-todos.php`
- **Method**: GET
- **Description**: Returns user's todo items
- **Response**: JSON with todo items array

### `/api/get-users.php`
- **Method**: GET
- **Description**: Returns list of users (admin only)
- **Response**: JSON with users array

### `/api/mark-post-read.php`
- **Method**: POST
- **Description**: Marks a love letter post as read for current user
- **Body**: JSON with `post_id`
- **Response**: JSON with success status

### `/api/photo-booth-capture.php`
- **Method**: POST
- **Description**: Saves captured photo from webcam to Photo Booth
- **Body**: JSON with `image` (base64) and `caption`
- **Response**: JSON with success status and message

### `/api/verify-passcode.php`
- **Method**: POST
- **Description**: Verifies passcode for "More" section unlock
- **Body**: JSON with `passcode`
- **Response**: JSON with success status

---

## User Roles & Permissions

### **User (Level 1)**
- View all public content
- Access MedBio study tools (shared editing)
- View profile and update own profile
- Access unlocked pages via "More" section

### **Moderator (Level 2)**
- All user permissions
- Content moderation capabilities
- View analytics

### **Admin (Level 3)**
- All moderator permissions
- Full content management:
  - Add/edit/delete posts, media, timeline events, locations
  - Manage custom compliments and inspirations
  - Schedule love letters
  - Upload photos and artwork
  - Edit site content (Our Story, Anniversary Letter)
  - Manage playlist settings
- User management
- Role management

---

## Technology Stack

### **Backend**
- PHP 7.4+ with PDO for database access
- MySQL 5.7+ database
- Session-based authentication
- File upload handling

### **Frontend**
- HTML5 semantic markup
- CSS3 with modern features (Grid, Flexbox, Animations)
- JavaScript (ES6+) for interactivity
- Responsive design (mobile-first approach)

### **Libraries & Frameworks**
- **Leaflet.js**: Interactive maps (for future location mapping)
- **Masonry.js**: Pinterest-style grid layouts
- **Font Awesome 6.0**: Icon library
- **Google Fonts (Poppins)**: Typography

### **External Services**
- Spotify Embed API: For music playlist integration

---

## Installation

### Prerequisites
- XAMPP/WAMP/LAMP server
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser with JavaScript enabled
- Modern browser with camera API support (for Photo Booth)

### Setup Instructions

1. **Clone/Download the project**
   ```bash
   git clone [repository-url]
   # or download and extract the ZIP file
   ```

2. **Place in web server directory**
   - Copy the project folder to your XAMPP `htdocs` directory
   - Or your web server's document root
   - Ensure folder is named `Ultra-Secret-December-Plan-2025`

3. **Database Setup**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the `database.sql` file to create the database and tables
   - The database will be automatically created as `secret_plan_db`
   - All tables, relationships, and sample data will be imported

4. **Configure Database Connection**
   - Edit `config/database.php` if needed
   - Default settings work with XAMPP:
     - Host: `localhost`
     - Database: `secret_plan_db`
     - Username: `root`
     - Password: `` (empty)

5. **Set Permissions**
   - Ensure the `uploads/` directory is writable (chmod 755 or 777)
   - Create subdirectories if they don't exist:
     - `uploads/profiles/`
     - `uploads/art_wall/`
     - `uploads/locations/`
     - `uploads/media/`
     - `uploads/photo_booth/`
     - `uploads/resources/`
     - `uploads/timeline/`

6. **Access the Website**
   - Start Apache and MySQL in XAMPP
   - Open your browser
   - Navigate to `http://localhost/Ultra-Secret-December-Plan-2025/`
   - Register a new account or create admin account manually

### Default Admin Account Setup

After database import, manually create an admin user:
1. Register a new account via the registration page
2. In phpMyAdmin, update the user's `role_id` to `3` in the `users` table
3. Or use SQL:
   ```sql
   UPDATE users SET role_id = 3 WHERE username = 'your_username';
   ```

---

## Database Schema

### Core Tables

- **users**: User accounts with profile information
- **roles**: User roles (user, moderator, admin) with permission levels
- **posts**: Notes, love letters, and compliments
- **post_reads**: Tracks which posts users have read
- **timeline_events**: Relationship milestone events
- **locations**: Places visited together
- **media_items**: Movies, series, and songs
- **todo_items**: Task management
- **scheduled_letters**: Pre-scheduled love letters
- **art_items**: Artwork gallery items
- **photo_booth**: Captured and uploaded photos
- **study_entries**: Study hour tracking
- **flashcards**: Study flashcards
- **lab_entries**: Lab notebook entries
- **resources**: Uploaded study files
- **wish_items**: Future plans and wishes
- **custom_compliments**: Custom compliment database
- **custom_inspirations**: Daily inspiration quotes
- **site_content**: Editable site content (Our Story, Anniversary Letter, Playlist)

### Key Relationships

- Users → Roles (many-to-one)
- Users → Posts (one-to-many)
- Users → Todos (one-to-many)
- Users → Study Entries (one-to-many)
- Users → Flashcards (one-to-many)
- Users → Lab Entries (one-to-many)
- Users → Resources (one-to-many)

---

## File Structure

```
Ultra-Secret-December-Plan-2025/
├── api/
│   ├── get-compliments.php          # API: Get compliments
│   ├── get-todos.php                 # API: Get todos
│   ├── get-users.php                 # API: Get users
│   ├── mark-post-read.php            # API: Mark post as read
│   ├── photo-booth-capture.php       # API: Save captured photo
│   └── verify-passcode.php          # API: Verify passcode
├── assets/
│   ├── css/
│   │   ├── passcode.css             # Passcode page styles
│   │   └── style.css                # Main stylesheet
│   ├── js/
│   │   ├── main.js                  # Core JavaScript
│   │   ├── map.js                   # Map functionality
│   │   └── passcode.js              # Passcode page scripts
│   ├── images/
│   │   ├── 2.png                    # Site images
│   │   └── Untitled design.png      # Site images
│   └── slideshow-image/
│       ├── 3cef6d1937a4be979b1de0cc21fc8645.jpg
│       ├── 7ef662f109068f53caedbee3d9fb9ab6.jpg
│       ├── 929bfb9d0b61899357d9c6ad54252afb.jpg
│       └── HOME.jpg
├── config/
│   └── database.php                 # Database configuration
├── includes/
│   ├── functions.php                # Common PHP functions
│   └── navbar.php                   # Navigation component
├── uploads/
│   ├── art_wall/                    # Artwork uploads
│   ├── locations/                   # Location photos
│   ├── media/                       # Media covers
│   ├── photo_booth/                 # Photo booth images
│   ├── profiles/                    # Profile pictures
│   ├── resources/                   # Study resources
│   └── timeline/                    # Timeline event images
├── about.php                        # About page
├── anniversary-letter.php           # Anniversary letter page
├── art-wall.php                     # Art gallery page
├── database.sql                     # Database schema
├── home.php                         # Home dashboard
├── how-i-see-her.php               # Love notes page
├── index.php                        # Landing page
├── login.php                        # Login page
├── logout.php                       # Logout handler
├── media.php                        # Movies & songs page
├── medbio.php                       # Study tools page
├── passcode-login.php              # Alternative login
├── photo-booth.php                  # Photo booth page
├── profile.php                      # User profile page
├── register.php                     # Registration page
├── setup.php                        # Setup utility (if exists)
├── tools.php                        # Admin tools page
├── us.php                           # Us/relationship page
├── migrate_*.sql                    # Database migration files
└── README.md                        # This file
```

---

## Security Features

### Authentication & Authorization
- **Passcode Hashing**: Uses PHP's `password_hash()` with PASSWORD_DEFAULT
- **Session Management**: Secure session handling with session_start()
- **Role-Based Access Control**: Three-tier permission system
- **Login Protection**: Automatic redirect for authenticated users

### Data Protection
- **SQL Injection Prevention**: All queries use PDO prepared statements
- **XSS Protection**: Input sanitization with `htmlspecialchars()` and `sanitize_input()`
- **Input Validation**: Server-side validation for all user inputs
- **File Upload Security**: 
  - File type restrictions
  - Unique filename generation
  - Secure upload directory structure

### Content Security
- **CSRF Protection**: Form-based actions with session validation
- **File Access Control**: Uploaded files stored outside web root when possible
- **Password Hints**: Customizable hints for passcode recovery

---

## Customization Guide

### Adding New Features

1. **Create New Page**:
   - Create new PHP file in root directory
   - Include `config/database.php` and `includes/functions.php`
   - Use `require_login()` for protected pages
   - Follow existing page structure

2. **Add to Navigation**:
   - Edit `includes/navbar.php`
   - Add new nav item to sidebar menu
   - Or add to "More" section if passcode-protected

3. **Database Changes**:
   - Create migration file: `migrate_feature_name.sql`
   - Update `database.sql` for fresh installs
   - Use PDO prepared statements in PHP

4. **Styling**:
   - Add CSS to `assets/css/style.css`
   - Follow existing class naming conventions
   - Ensure responsive design

5. **JavaScript**:
   - Add to `assets/js/main.js` or create new file
   - Use ES6+ syntax
   - Handle errors gracefully

### Modifying Existing Features

1. **Change Anniversary Date**:
   - Edit `includes/functions.php` → `get_anniversary_countdown()`
   - Update date in `anniversary-letter.php`

2. **Customize Colors**:
   - Edit `assets/css/style.css`
   - Main accent: `#87CEEB` (Sky Blue)
   - Secondary: `#ff6b6b` (Coral Red)

3. **Add New Post Types**:
   - Update `posts` table ENUM in database
   - Modify `how-i-see-her.php` form
   - Update display logic

4. **Modify Study Tools**:
   - Edit `medbio.php` for UI changes
   - Update database schema if needed
   - Modify shared access logic if required

### Database Migrations

The system includes migration files for incremental updates:
- `migrate_add_captions.sql`: Adds caption fields
- `migrate_add_custom_content.sql`: Adds custom content tables
- `migrate_add_spotify_embed.sql`: Adds Spotify embed support
- `migrate_password_to_passcode.sql`: Renames password to passcode

Run migrations via phpMyAdmin or command line.

---

## Browser Support

- **Chrome**: 70+
- **Firefox**: 65+
- **Safari**: 12+
- **Edge**: 79+
- **Mobile Browsers**: iOS Safari 12+, Chrome Mobile 70+

### Required Features
- JavaScript enabled
- LocalStorage support (for sidebar state, dark mode)
- SessionStorage support (for letter popup tracking)
- Camera API support (for Photo Booth - Chrome, Firefox, Safari)
- CSS Grid and Flexbox support

---

## Known Features & Behaviors

### Scheduled Letters
- Letters scheduled for today or earlier automatically create posts
- Popup notifications appear once per session per letter
- Letters marked as "sent" after creation
- Unread badges on love letters until opened

### Shared Access (MedBio Tools)
- All users can view and edit all study entries, flashcards, todos, lab entries, and resources
- Designed for collaborative study sessions
- Each entry shows creator name

### Photo Booth Grid
- Fixed 3x3 grid (9 slots)
- Empty slots show placeholder icons
- Newest photos appear first
- Grid fills left-to-right, top-to-bottom

### Dark Mode
- Toggle in sidebar footer
- Preference saved in localStorage
- Applies to all pages

### Responsive Design
- Mobile-first approach
- Sidebar collapses on mobile
- Grid layouts adapt to screen size
- Touch-friendly buttons and interactions

---

## Troubleshooting

### Common Issues

1. **Database Connection Error**:
   - Check MySQL is running
   - Verify credentials in `config/database.php`
   - Ensure database exists

2. **File Upload Fails**:
   - Check `uploads/` directory permissions
   - Verify subdirectories exist
   - Check PHP `upload_max_filesize` setting

3. **Photo Booth Camera Not Working**:
   - Ensure HTTPS or localhost (camera requires secure context)
   - Check browser permissions
   - Verify camera is not in use by another app

4. **Scheduled Letters Not Appearing**:
   - Check scheduled date is today or earlier
   - Verify letter hasn't been marked as sent
   - Clear sessionStorage to reset popup tracking

5. **Sidebar Not Showing**:
   - Check localStorage for `sidebarHidden` setting
   - Click hamburger menu to toggle
   - Clear browser cache if issues persist

---

## Contributing

This is a personal project, but suggestions and improvements are welcome!

### Development Guidelines
- Follow existing code style
- Use prepared statements for all database queries
- Sanitize all user inputs
- Test on multiple browsers
- Ensure mobile responsiveness

---

## License

This project is for personal use. Please respect the privacy and personal nature of this relationship website.

---

## Support

For technical issues or questions about the code:
1. Check this README for common solutions
2. Review the code comments
3. Check database schema in `database.sql`
4. Verify file permissions and server configuration

---

## Version History

- **v1.0**: Initial release with core features
- Features include: User management, Home dashboard, How I See Her, Media, Us page, Photo Booth, Art Wall, Tools, MedBio study tools, About page, Anniversary Letter

---

**Made with ❤️ for a special someone**

*This website represents a digital sanctuary for love, memories, and shared growth. Every feature was designed with care and attention to detail, creating a space that celebrates both the romantic and practical aspects of a relationship.*
