# Simple News Web Site

## Project Overview

This project is a simple news website that allows users to submit, view, and comment on stories. It implements a multi-tier user system with varying levels of permissions and features a soft deletion mechanism with timed permanent deletion.

## Features

### User Authentication and Authorization
- User registration and secure login system
- Four-tier user hierarchy:
  1. Guest
  2. Normal User
  3. Admin User
  4. King Admin

### Content Management
- Story submission with associated links
- Commenting system
- Edit and delete functionality for user's own content
- Admin ability to moderate all content

### User Management (Admin Features)
- Promote/demote users between Normal and Admin tiers
- Delete users (except King Admin)

### Trash Can Feature
- Soft deletion for stories, comments, and users
- Ability to restore soft-deleted items

### Timed Deletion
- 28-day countdown for permanent deletion of soft-deleted items
- Special login message for soft-deleted user accounts

### Additional Features
- Automatic login upon registration
- Visual indication of previously visited links
- Display of currently logged-in user

## Security Measures
- Protection against SQL injection attacks
- Secure password hashing and salting
- CSRF token implementation
- Server-side precondition checks
- W3C validator compliant
- Input filtering and output escaping

## Setup and Usage

1. Ensure you have a web server with PHP and MySQL support.
2. Clone this repository to your web server's directory.
3. Set up the MySQL database using the provided schema.
4. Configure the database connection in the appropriate PHP files.
5. Access the site through the main page (news.php).

## User Tiers and Permissions

### Guest
- View stories and comments

### Normal User (e.g., username: a, password: a)
- All Guest permissions
- Submit stories and comments
- Edit and delete own content

### Admin User (e.g., username: b, password: b)
- All Normal User permissions
- Delete any user's content
- Promote/demote users between Normal and Admin tiers
- Delete other users (except King Admin)

### King Admin (username: admin, password: q)
- All Admin User permissions
- Cannot be deleted or demoted by other admins
