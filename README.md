# Dadyabooks - Çocuk Kitap Takip Sistemi

A Turkish children's book tracking system designed for schools and parents to monitor which books children have read. The system serves as a digital reading log that helps educators and parents track reading progress and maintain records of completed books.

## Features

### For Parents (Veli)
- Track your children's reading progress
- View reading history with dates
- Search through your child's reading list
- Mobile-friendly interface for easy access

### For Administrators
- Manage all students' reading records
- Add new books to the system
- Create parent accounts and link them to children
- Track reading progress across the school
- Search for children by name or school number

## Technology Stack

- **Backend**: PHP 7.4+ with MySQL database
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **UI Framework**: Bootstrap 5.3.3
- **Database**: MySQL with PDO
- **Authentication**: Session-based with role-based access control

## System Requirements

- PHP 7.4 or higher
- MySQL database
- Web server (Apache/Nginx)
- Modern web browser

## Installation

1. Upload all files to your web server's document root
2. Configure database connection in `htdocs/private/config.php`
3. Import the database schema
4. Set up user accounts through the admin panel

## Project Structure

```
htdocs/
├── index.php              # Main application entry point
├── private/               # Configuration and auth utilities
│   ├── config.php         # Database configuration
│   ├── auth.php           # Authentication helpers
│   └── htaccess           # Security rules
├── admin/                 # Admin-specific pages
│   ├── login.php          # Admin login
│   ├── panel.php          # Admin dashboard
│   └── logout.php         # Session cleanup
├── api/                   # RESTful API endpoints
│   ├── books.php          # Book management
│   ├── kids.php           # Child management
│   └── ...                # Other API endpoints
└── password_change.php    # Password change functionality
```

## Key Features

- **User Authentication**: Secure login system for admin and parent users
- **Child Management**: Complete CRUD operations for child profiles
- **Book Management**: Comprehensive book catalog with search functionality
- **Reading Records**: Track which books children have read with dates
- **Role-Based Access**: Proper permissions for admin vs parent users
- **Real-Time Search**: Debounced search with instant results
- **Responsive Design**: Mobile-friendly interface using Bootstrap 5
- **Turkish Language**: Complete Turkish localization

## Security Features

- Password hashing using PHP's `password_hash()`
- SQL injection prevention with prepared statements
- XSS prevention with `htmlspecialchars()`
- Session-based authentication
- Role-based access control
- CSRF protection through session validation

## API Endpoints

The system provides RESTful API endpoints for:
- Book management (list, search, add)
- Child management (list, search)
- Reading record management (add, update, delete)
- User authentication and authorization

## Contributing

This is a school project designed for tracking children's reading progress. The system is currently in production use and maintained by the development team.

## License

This project is developed for educational purposes and school use.

## Support

For technical support or questions about the system, please contact the development team.

---

**Note**: This system is designed to work on shared hosting environments and follows Turkish educational system requirements for book tracking and reading progress monitoring.