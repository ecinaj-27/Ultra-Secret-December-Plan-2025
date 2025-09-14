# Our Secret Place - Relationship Website

A beautiful, comprehensive website built for couples to manage their relationship, store memories, and support academic studies. This project combines romantic features with practical tools for medical students.

## Features

### 🏠 **Landing Page**
- Centered logo with beautiful animations
- Login/Register system with passcode hints
- Feature showcase

### 👤 **User Management**
- Secure authentication system
- Profile management with photo uploads
- Customizable passcode hints

### 🏡 **Home Dashboard**
- Anniversary countdown timer
- Live time display
- Random motivational quotes
- Interactive calendar
- Quick to-do list overview

### 💕 **How I See Her**
- Personal notes and love letters
- Masonry grid layout (Pinterest-style)
- Random compliment generator
- Different post types (notes, letters, compliments)

### 🎬 **Movies & Songs**
- Polaroid-style media cards
- Movies, series, and music playlists
- Rating system
- External links integration

### 👫 **Us Page**
- Interactive relationship timeline
- Clickable map of visited places
- Memory statistics
- Lightbox for timeline images

### ℹ️ **About Page**
- Website information
- Relationship story timeline
- Heartfelt anniversary letter
- Sub-navigation system

### 🛠️ **Tools & Utilities**
- **Admin Tools** (admin only):
  - Love letter scheduler
  - Pre-written letters for special dates
- **To-Do List**: Categorized task management
- **Wish Jar**: Future date ideas and travel plans
- **Art Wall**: Digital gallery for creative expressions
- **MedBio Study Tools**:
  - Study hour tracker
  - Flashcard generator
  - Lab notebook
  - Resource vault
  - Progress statistics

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6)
- **Libraries**: 
  - Leaflet.js (interactive maps)
  - Masonry.js (grid layouts)
  - Font Awesome (icons)
  - Google Fonts (typography)

## Installation

### Prerequisites
- XAMPP/WAMP/LAMP server
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser with JavaScript enabled

### Setup Instructions

1. **Clone/Download the project**
   ```bash
   git clone [repository-url]
   # or download and extract the ZIP file
   ```

2. **Place in web server directory**
   - Copy the project folder to your XAMPP `htdocs` directory
   - Or your web server's document root

3. **Database Setup**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the `database.sql` file to create the database and tables
   - The database will be named `secret_plan_db`

4. **Configure Database Connection**
   - Edit `config/database.php` if needed
   - Default settings work with XAMPP:
     - Host: localhost
     - Database: secret_plan_db
     - Username: root
     - Password: (empty)

5. **Set Permissions**
   - Ensure the `uploads/` directory is writable
   - Create subdirectories: `uploads/profiles/`, `uploads/art/`, `uploads/resources/`

6. **Access the Website**
   - Open your browser
   - Navigate to `http://localhost/Ultra-Secret-December-Plan-2025/`
   - Register a new account or use the default admin account:
     - Username: `admin`
     - Passcode: `password`

## Default Admin Account

- **Username**: admin
- **Passcode**: password
- **Note**: Change the passcode after first login for security

## File Structure

```
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet
│   ├── js/
│   │   ├── main.js           # Core JavaScript
│   │   └── map.js            # Map functionality
│   └── images/
│       └── default-avatar.png
├── config/
│   └── database.php          # Database configuration
├── includes/
│   ├── functions.php         # Common functions
│   └── navbar.php            # Navigation component
├── uploads/                  # File uploads directory
├── *.php                     # Main application files
├── database.sql              # Database schema
└── README.md                 # This file
```

## Customization

### Adding New Features
1. Create new PHP files in the root directory
2. Add corresponding CSS styles in `assets/css/style.css`
3. Include JavaScript functionality in `assets/js/main.js`
4. Update the navigation in `includes/navbar.php`

### Database Modifications
1. Modify `database.sql` for schema changes
2. Update `config/database.php` for connection settings
3. Add new functions in `includes/functions.php`

### Styling
- Main color scheme: Pink/Red (#ff6b6b)
- Font: Poppins (Google Fonts)
- Responsive design for mobile devices
- Modern card-based layout

## Security Features

- Passcode hashing using PHP's `password_hash()`
- SQL injection prevention with prepared statements
- Input sanitization and validation
- Session management
- File upload restrictions

## Browser Support

- Chrome 70+
- Firefox 65+
- Safari 12+
- Edge 79+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Contributing

This is a personal project, but suggestions and improvements are welcome!

## License

This project is for personal use. Please respect the privacy and personal nature of this relationship website.

## Support

For technical issues or questions about the code, please refer to the documentation or create an issue in the repository.

---

**Made with ❤️ for a special someone**