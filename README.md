# 🚗 GarageHub

A full-stack web application that allows users to browse, manage, and save vehicles from both manual entries and a third-party REST API. Built with PHP, MySQL, and Bootstrap, deployed on Heroku.

---

## 🌐 Live Demo

> [GarageHub](https://dg599-it202-007-prod-0134f160fb38.herokuapp.com/project/)

---

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Database Schema](#database-schema)
- [Usage](#usage)
- [API Integration](#api-integration)
- [Security](#security)
- [Project Structure](#project-structure)
- [Screenshots](#screenshots)
- [Author](#author)

---

## 📖 Overview

GarageHub is a full-stack web application developed as part of the IT202 course at NJIT. The application allows users to:

- Register and log in securely
- Browse a database of cars populated from both manual entries and a third-party API
- Save cars to a personal "My Garage" collection
- Admins can manage the entire database, fetch API data, and manage user associations

---

## ✨ Features

### 🔐 Authentication & Authorization
- User registration with three-layer validation (HTML5, JavaScript, PHP)
- Login with email **or** username
- Secure session management
- Role-Based Access Control (RBAC) with Admin and User roles
- BCRYPT password hashing
- Profile editing with password reset functionality

### 🚘 Car Management (CRUD)
- Create manual car entries with full validation
- Browse all cars with filtering, sorting, and pagination
- View detailed car information
- Edit existing car entries
- Delete cars (with permission checks)
- Distinguishes between API-sourced and manually-created cars

### 🔌 API Integration
- Integrates with **RapidAPI Cars Database**
- Server-side data fetching and transformation
- Automatic duplicate detection using unique API identifiers
- Admin-controlled API data fetching

### 🏠 My Garage (User Associations)
- Add/remove cars from personal garage
- Personalized filtering and sorting
- Remove all associations at once
- Displays total count and filtered count statistics

### 👨‍💼 Admin Features
- View all user-car associations system-wide
- Manage unassociated cars
- Bulk assign cars to users (like role assignment)
- Filter associations by username or car make
- Remove associations for specific users

---

## 🛠 Tech Stack

| Category | Technology |
|----------|-----------|
| **Backend** | PHP 8+ |
| **Database** | MySQL |
| **Frontend** | HTML5, CSS3, JavaScript |
| **Framework** | Bootstrap 5 |
| **API** | RapidAPI - Cars Database with Image |
| **HTTP Requests** | cURL |
| **Deployment** | Heroku |
| **Version Control** | Git/GitHub |
| **Authentication** | PHP Sessions + BCRYPT |

---

## 🗄 Database Schema

### Users Table


### Cars Table


### UserCars Table (Association)


### Roles Table


### UserRoles Table


## 🚀 Usage

### Regular User
1. **Register** a new account at `/project/register.php`
2. **Login** with your email or username at `/project/login.php`
3. **Browse Cars** at `/project/list_cars.php`
4. **Add cars** to your garage from the list or detail pages
5. **View your garage** at `/project/my_garage.php`
6. **Edit your profile** at `/project/profile.php`

### Admin User
1. Login with an admin account
2. **Fetch API Cars** at `/project/fetch_cars.php`
3. **Manage all associations** at `/project/admin_all_associations.php`
4. **View unassociated cars** at `/project/admin_unassociated_cars.php`
5. **Assign cars to users** at `/project/admin_assign_cars.php`
6. **Manage roles** at `/project/admin/assign_roles.php`

---

## 🔌 API Integration

This project uses the **Cars Database with Image** API from RapidAPI.

### Endpoint
```
GET https://cars-database-with-image.p.rapidapi.com/api/search?q={query}&page=1
```

### API Response Structure
```
{
  "results": [
    {
      "id": "subaru-wrx-vb-ii-2.4-271hp-awd",
      "title": "Subaru WRX (VB) II",
      "content": "2.4 (271 Hp) AWD SPT",
      "additional": "Sedan, All wheel drive (4x4)",
      "image": "https://www.auto-data.net/images/...",
      "wr": "11.2 l/100 km | 21 US mpg"
    }
  ],
  "page": 1
}
```

### Data Transformation
The API response is transformed to fit the database schema:
- **Make**: Extracted from first word of `title`
- **Model**: Extracted from remaining words of `title`
- **Year**: Parsed using regex from multiple fields
- **Type**: Extracted from `additional` field (Sedan, Hatchback, SUV, etc.)
- **Description**: Built from `content` and `additional` fields
- **api_id**: Stored directly from API's `id` field for duplicate detection

---

## 🔒 Security

| Feature | Implementation |
|---------|---------------|
| **Password Hashing** | BCRYPT via `password_hash()` |
| **SQL Injection Prevention** | PDO Prepared Statements |
| **XSS Prevention** | Input sanitization via `htmlspecialchars()` |
| **Session Security** | Secure, HttpOnly, SameSite cookies |
| **Authentication** | Session-based with `is_logged_in()` checks |
| **Authorization** | Role-based with `has_role()` checks |
| **Input Validation** | Three-layer: HTML5, JavaScript, PHP |
| **Error Handling** | Technical errors logged via `error_log()` |

---

## 📁 Project Structure

```
dg599-IT202-007/
├── partials/
│   ├── nav.php           # Navigation bar (included on every page)
│   └── flash.php         # Flash message display
├── lib/
│   ├── functions.php     # Core helper functions
│   ├── db.php            # Database connection
│   ├── api_helper.php    # API request handler
│   ├── load_api_keys.php # API key loader
│   └── user_helpers.php  # Authentication/authorization helpers
└── public_html/
    └── project/
        ├── register.php              # User registration
        ├── login.php                 # User login
        ├── logout.php                # User logout
        ├── landing.php               # Landing page
        ├── profile.php               # User profile
        ├── list_cars.php             # Browse all cars
        ├── create_car.php            # Create manual car entry
        ├── view_car.php              # View car details
        ├── edit_car.php              # Edit car
        ├── delete_car.php            # Delete car
        ├── my_garage.php             # User's car associations
        ├── toggle_garage.php         # Add/remove from garage
        ├── remove_all_garage.php     # Remove all garage associations
        ├── fetch_cars.php            # Admin: Fetch API data
        ├── admin_all_associations.php    # Admin: All associations
        ├── admin_unassociated_cars.php   # Admin: Unassociated cars
        ├── admin_assign_cars.php         # Admin: Assign cars to users
        ├── admin_remove_association.php  # Admin: Remove association
        ├── styles.css                # Custom styles
        ├── helpers.js                # Client-side helpers
        └── sql/
            ├── init_db.php           # Database initializer
            └── *.sql                 # SQL migration files
```

---

## 📸 Screenshots

| Page | Description |
|------|-------------|
| Landing Page | Welcome page with login/register links |
| Registration | Multi-layer validated registration form |
| Car List | Browsable car database with filters |
| Car Details | Full car information with garage button |
| My Garage | Personal car collection management |
| Admin Dashboard | User association management tools |

---

## 👤 Author

**Dylan Garcia**

- 📧 Email: dg599@njit.edu
- 💼 LinkedIn: [Dylan Garcia](https://www.linkedin.com/in/dylan-garcia-a0790128b/)
- 🐙 GitHub: [dylangarca](https://github.com/dylangarca)
- 🎓 NJIT - B.S. Information Technology (Expected May 2027)

---

## 📄 License

This project was developed as part of IT202 at NJIT. All rights reserved.

---
