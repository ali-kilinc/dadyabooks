# Project Brief: Dadyabooks - Çocuk Kitap Takip Sistemi

## Project Overview
Dadyabooks is a Turkish children's book tracking system designed for schools and parents to monitor which books children have read. The system serves as a digital reading log that helps educators and parents track reading progress and maintain records of completed books.

## Core Requirements

### Primary Goals
1. **Book Tracking**: Track which books each child has read with reading dates
2. **Multi-User System**: Support both admin and parent user roles
3. **Child Management**: Manage children's profiles with school numbers and group assignments
4. **Book Database**: Maintain a comprehensive book catalog with metadata
5. **Search & Filter**: Enable searching for children and books by various criteria

### User Roles
- **Admin**: Full system access, can manage all children and books
- **Parent (Veli)**: Limited access to their own children's reading records

### Key Features
- User authentication with role-based access control
- Child profile management (name, school number, group)
- Book catalog management (title, author, publisher, ISBN)
- Reading record tracking (which child read which book and when)
- Search functionality for both children and books
- Responsive web interface optimized for mobile and desktop

### Technical Requirements
- PHP-based web application
- MySQL database for data persistence
- Session-based authentication
- RESTful API endpoints for AJAX operations
- Bootstrap-based responsive UI
- Turkish language support throughout

## Success Criteria
- Parents can easily track their children's reading progress
- Administrators can manage the entire system efficiently
- System handles multiple children per parent
- Search functionality is fast and intuitive
- Interface is mobile-friendly for parent access
- Data integrity is maintained through proper validation

## Constraints
- Must work on shared hosting (InfinityFree)
- Database credentials are hardcoded in config
- No external dependencies beyond CDN resources
- Turkish language interface required
- Simple deployment without complex build processes
