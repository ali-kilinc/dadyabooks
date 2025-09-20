# Technical Context: Dadyabooks

## Technology Stack

### Backend
- **PHP 7.4+**: Server-side scripting language
- **MySQL**: Relational database management system
- **PDO**: PHP Data Objects for database abstraction
- **Session Management**: Native PHP sessions for authentication

### Frontend
- **HTML5**: Semantic markup structure
- **CSS3**: Styling with Bootstrap framework
- **JavaScript (ES6+)**: Client-side interactivity
- **Bootstrap 5.3.3**: Responsive UI framework
- **Bootstrap Icons**: Icon library for UI elements

### Hosting & Infrastructure
- **InfinityFree**: Shared hosting provider
- **MySQL Database**: Hosted database service
- **CDN Resources**: Bootstrap and icons from jsdelivr.net

## Development Environment

### File Structure
```
/Users/ali.kilinc/Code/dadyabooks/
├── htdocs/                    # Web root directory
│   ├── index.php             # Main application entry
│   ├── private/              # Configuration files
│   │   ├── config.php        # Database configuration
│   │   ├── auth.php          # Authentication utilities
│   │   └── htaccess          # Security rules
│   ├── admin/                # Admin interface
│   │   ├── login.php         # Login page
│   │   ├── logout.php        # Logout handler
│   │   └── panel.php         # Admin dashboard
│   └── api/                  # REST API endpoints
│       ├── books.php         # Book management
│       ├── kids.php          # Child management
│       └── ...               # Other API endpoints
└── memory-bank/              # Project documentation
```

### Database Configuration
- **Host**: sql312.infinityfree.com
- **Database**: if0_39985637_dadyabook
- **User**: if0_39985637
- **Charset**: utf8mb4 with Turkish collation
- **Connection**: PDO with error handling

## Dependencies

### External Dependencies
- **Bootstrap CSS**: https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css
- **Bootstrap JS**: https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js
- **Bootstrap Icons**: https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css

### PHP Extensions Required
- **PDO**: Database abstraction layer
- **PDO_MySQL**: MySQL driver for PDO
- **Session**: Session management
- **JSON**: JSON encoding/decoding
- **Password Hash**: Password hashing functions

## Technical Constraints

### Hosting Limitations
- **Shared hosting environment**: Limited server control
- **PHP version**: Must be compatible with hosting provider
- **Database access**: Only through provided credentials
- **File permissions**: Limited file system access
- **No command line access**: Web-based deployment only

### Security Considerations
- **Database credentials**: Hardcoded in config file
- **No environment variables**: Configuration in PHP files
- **Session security**: Relies on PHP session handling
- **Input validation**: Manual validation in each endpoint
- **No HTTPS enforcement**: Depends on hosting provider

### Performance Constraints
- **Shared resources**: CPU and memory limitations
- **Database connections**: Limited concurrent connections
- **File size limits**: Upload restrictions
- **Execution time**: PHP script timeout limits
- **Memory usage**: PHP memory limit constraints

## Development Workflow

### Local Development
1. **Code editing**: Direct file modification
2. **Testing**: Manual testing through web browser
3. **Database changes**: Direct SQL execution
4. **Deployment**: File upload to hosting provider

### Version Control
- **Git repository**: Local version control
- **No CI/CD**: Manual deployment process
- **No automated testing**: Manual testing only
- **No staging environment**: Direct production deployment

### Debugging & Monitoring
- **Error logging**: PHP error_log() function
- **Console logging**: JavaScript console for frontend
- **Database queries**: Manual query testing
- **No performance monitoring**: Basic error tracking only

## API Design

### Endpoint Structure
- **Base URL**: Root domain + /api/
- **HTTP Methods**: GET for queries, POST for mutations
- **Response Format**: JSON with consistent structure
- **Error Handling**: HTTP status codes + JSON error messages

### Authentication
- **Session-based**: PHP sessions for state management
- **Role-based**: Admin and parent role differentiation
- **Permission checks**: Per-endpoint authorization
- **No API keys**: Session-based authentication only

### Data Validation
- **Input sanitization**: trim() and type casting
- **SQL injection prevention**: Prepared statements
- **XSS prevention**: htmlspecialchars() for output
- **Business logic validation**: Custom validation rules

## Deployment Process

### Production Deployment
1. **Code changes**: Edit files locally
2. **File upload**: Upload to htdocs directory
3. **Database updates**: Manual SQL execution if needed
4. **Testing**: Manual verification of functionality
5. **No rollback strategy**: Manual file restoration

### Configuration Management
- **Database credentials**: Hardcoded in config.php
- **Environment detection**: No environment-specific configs
- **Feature flags**: No feature toggle system
- **Logging configuration**: Basic PHP error logging

## Maintenance Considerations

### Database Maintenance
- **Backup strategy**: Manual database exports
- **Index optimization**: Manual index management
- **Data cleanup**: Manual data purging
- **Performance monitoring**: Basic query analysis

### Code Maintenance
- **No automated testing**: Manual testing required
- **Code review**: Manual code inspection
- **Documentation**: Memory bank system for knowledge
- **Refactoring**: Manual code improvement

### Security Maintenance
- **Password updates**: Manual credential changes
- **Security patches**: Manual PHP/MySQL updates
- **Access monitoring**: Manual log review
- **Vulnerability assessment**: Manual security review
