# System Patterns: Dadyabooks

## Architecture Overview

### High-Level Architecture
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Web Browser   │◄──►│   PHP Backend   │◄──►│   MySQL DB      │
│   (Frontend)    │    │   (API + UI)    │    │   (Data Layer)  │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Directory Structure
```
htdocs/
├── index.php              # Main application entry point
├── private/               # Configuration and auth utilities
│   ├── config.php         # Database configuration
│   ├── auth.php           # Authentication helpers
│   └── htaccess           # Security rules
├── admin/                 # Admin-specific pages
│   ├── login.php          # Dedicated login page
│   ├── logout.php         # Session cleanup
│   ├── panel.php          # Admin dashboard
│   └── makehash.php       # Password utility
└── api/                   # RESTful API endpoints
    ├── books.php          # Book listing/search
    ├── books_suggest.php  # Book autocomplete
    ├── book_add.php       # Book creation
    ├── kids.php           # Child listing/search
    ├── my_kids.php        # Parent's children
    ├── kid_books_add.php  # Reading record creation
    ├── kid_books_update.php # Reading record updates
    └── kid_books_delete.php # Reading record deletion
```

## Key Technical Decisions

### Authentication & Authorization
- **Session-based authentication** using PHP sessions
- **Role-based access control** with 'admin' and 'veli' (parent) roles
- **Authorization helpers** in `auth.php` for consistent permission checking
- **Parent-child relationship** enforced through `parent_kids` junction table

### Database Design Patterns

#### Core Entities
```sql
users (id, username, password_hash, role, full_name)
kids (id, name, school_number, group_name)
books (id, title, author, publisher, isbn)
parent_kids (user_id, kid_id) -- Junction table
kid_books (kid_id, book_id, read_date, created_by_user_id, created_at, updated_at)
```

#### Key Relationships
- **Many-to-Many**: Parents ↔ Children (via parent_kids)
- **Many-to-Many**: Children ↔ Books (via kid_books)
- **One-to-Many**: Users → Reading Records (created_by_user_id)

### API Design Patterns

#### Consistent Response Format
```json
{
  "ok": true|false,
  "data": [...],
  "error": "error message",
  "pagination": {
    "page": 1,
    "limit": 20
  }
}
```

#### Error Handling
- **HTTP status codes** for different error types
- **Consistent JSON error responses**
- **Database transaction rollback** for data integrity
- **Input validation** with clear error messages

### Frontend Patterns

#### Progressive Enhancement
- **Server-side rendering** for basic functionality
- **JavaScript enhancement** for dynamic features
- **AJAX for real-time search** without page reloads
- **Bootstrap for responsive design**

#### Search Implementation
- **Debounced search** (300ms delay) to reduce server load
- **Real-time suggestions** for book selection
- **Client-side filtering** for immediate feedback

## Component Relationships

### Authentication Flow
1. User submits credentials → `index.php` or `admin/login.php`
2. Credentials validated against `users` table
3. Session created with user data
4. Redirect to appropriate interface based on role

### Data Access Flow
1. User action triggers AJAX request
2. API endpoint validates authentication and permissions
3. Database query executed with proper parameter binding
4. JSON response returned to frontend
5. UI updated with new data

### Search Flow
1. User types in search box
2. Debounced function triggers after 300ms
3. AJAX request sent to appropriate API endpoint
4. Database query with LIKE pattern matching
5. Results returned and displayed in real-time

## Security Patterns

### Input Validation
- **Server-side validation** for all inputs
- **SQL injection prevention** using prepared statements
- **XSS prevention** with `htmlspecialchars()`
- **CSRF protection** through session validation

### Access Control
- **Authentication required** for all API endpoints
- **Role-based permissions** enforced at API level
- **Parent-child relationship** validation for parent users
- **Admin-only operations** protected by role checks

### Data Protection
- **Password hashing** using `password_hash()`
- **Database credentials** in separate config file
- **Error logging** without exposing sensitive data
- **Transaction rollback** on errors

## Performance Patterns

### Database Optimization
- **Prepared statements** for query efficiency
- **LIMIT clauses** for pagination
- **Indexed columns** for search performance
- **Transaction batching** for multiple operations

### Frontend Optimization
- **CDN resources** for Bootstrap and icons
- **Debounced search** to reduce API calls
- **Minimal DOM manipulation** for better performance
- **Responsive images** and efficient CSS

## Error Handling Patterns

### Graceful Degradation
- **Database connection failures** show user-friendly messages
- **API errors** return consistent JSON format
- **JavaScript errors** don't break core functionality
- **Network failures** provide retry mechanisms

### Logging Strategy
- **Error logging** for debugging
- **User action logging** for audit trails
- **Database error details** logged securely
- **Performance monitoring** through response times
