# Events E-commerce Admin - Laravel

A Laravel-based events e-commerce admin panel converted from React + TypeScript + Vite.

## Features

- User authentication (Admin/Doctor roles)
- Event management (CRUD operations)
- Doctor registration with specialities
- MySQL database integration
- RESTful API endpoints
- Responsive web interface

## Installation

1. Install dependencies:
```bash
composer install
```

2. Set up environment:
```bash
cp .env.example .env
php artisan key:generate
```

3. Configure database in `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=events_ecommerce
DB_USERNAME=root
DB_PASSWORD=
```

4. Create database and run migrations:
```bash
mysql -u root -p -e "CREATE DATABASE events_ecommerce;"
php artisan migrate
php artisan db:seed
```

5. Start the server:
```bash
php artisan serve
```

## API Endpoints

### Authentication
- `POST /api/login` - User login
- `POST /api/register` - Doctor registration
- `POST /api/logout` - User logout
- `GET /api/user` - Get authenticated user

### Events
- `GET /api/events` - List all events
- `POST /api/events` - Create new event
- `GET /api/events/{id}` - Get event details
- `PUT /api/events/{id}` - Update event
- `DELETE /api/events/{id}` - Delete event
- `POST /api/events/{id}/register` - Register for event

## Database Schema

### Users Table
- id, name, email, phone, role, speciality, sub_speciality, avatar, password

### Events Table
- id, title, type, description, date, time, duration, price, image, speaker, capacity, registered, tags, status

### Subscriptions Table
- id, user_id, plan, status, start_date, end_date, price

## Default Admin Account
- Email: admin@medconf.com
- Password: admin123

## Web Routes
- `/` - Welcome page
- `/login` - Login form
- `/register` - Registration form
- `/admin` - Admin dashboard