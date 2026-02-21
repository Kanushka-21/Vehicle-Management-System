# Vehicle Maintenance System - Local Setup Guide
## MySQL Workbench + XAMPP/WAMP Setup

---

## 📋 Prerequisites

### Required Software:
1. **PHP 8.2 or higher** - Download from [php.net](https://windows.php.net/download/)
2. **Composer** - Download from [getcomposer.org](https://getcomposer.org/download/)
3. **MySQL 8.0+** via **MySQL Workbench** - Download from [mysql.com](https://dev.mysql.com/downloads/workbench/)
4. **Node.js 18+** - Download from [nodejs.org](https://nodejs.org/)
5. **Git** (optional) - Download from [git-scm.com](https://git-scm.com/)

### Alternative (Easier Option):
- **XAMPP** - Includes PHP, MySQL, Apache in one package: [apachefriends.org](https://www.apachefriends.org/)

---

## 🗄️ Step 1: Setup MySQL Database

### Option A: Using MySQL Workbench

1. **Open MySQL Workbench**
2. **Create a new connection** (if not already exists):
   - Connection Name: `VMS Local`
   - Hostname: `127.0.0.1`
   - Port: `3306`
   - Username: `root`
   - Password: (your MySQL root password)

3. **Connect and create database**:
   ```sql
   CREATE DATABASE vehicle_maintenance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

4. **Create a dedicated user** (recommended):
   ```sql
   CREATE USER 'vms_user'@'localhost' IDENTIFIED BY 'your_password';
   GRANT ALL PRIVILEGES ON vehicle_maintenance.* TO 'vms_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

### Option B: Using XAMPP

1. **Start XAMPP Control Panel**
2. **Start MySQL service**
3. **Click "Admin"** button next to MySQL (opens phpMyAdmin)
4. **Create database**: Click "New" → Database name: `vehicle_maintenance` → Create

---

## ⚙️ Step 2: Setup Laravel Backend

### 1. Navigate to backend directory:
```powershell
cd "D:\new databse work\vehicle-maintenance-system\backend"
```

### 2. Install PHP dependencies:
```powershell
composer install
```

If you get errors, try:
```powershell
composer install --ignore-platform-reqs
```

### 3. Create environment file:
```powershell
Copy-Item .env.example .env
```

### 4. Edit `.env` file with your database credentials:

Open `backend\.env` in any text editor and update:

```env
APP_NAME="Vehicle Maintenance System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vehicle_maintenance
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
```

**Important**: Replace `your_mysql_password` with your actual MySQL password!

### 5. Generate application key:
```powershell
php artisan key:generate
```

### 6. Run database migrations and seeders:
```powershell
php artisan migrate:fresh --seed
```

This will create all tables and insert sample data:
- 5 users (admin, manager, technician, 2 drivers)
- 3 vehicles
- 5 parts
- 3 vendors
- Sample work orders and fuel logs

### 7. Start Laravel development server:
```powershell
php artisan serve
```

**Keep this terminal open!** Backend will run on: `http://localhost:8000`

---

## 🎨 Step 3: Setup React Frontend

### 1. Open a NEW terminal/PowerShell window

### 2. Navigate to frontend directory:
```powershell
cd "D:\new databse work\vehicle-maintenance-system\frontend"
```

### 3. Create environment file:
```powershell
Copy-Item .env.example .env
```

The `.env` should contain:
```env
VITE_API_URL=http://localhost:8000/api
```

### 4. Install Node.js dependencies:
```powershell
npm install
```

### 5. Start development server:
```powershell
npm run dev
```

**Keep this terminal open!** Frontend will run on: `http://localhost:3000`

---

## 🎯 Step 4: Access the Application

1. **Open your browser**
2. **Navigate to**: `http://localhost:3000`
3. **Login with default credentials**:

| Role | Email | Password |
|------|-------|----------|
| **Administrator** | admin@example.com | password |
| Fleet Manager | manager@example.com | password |
| Technician | tech@example.com | password |
| Driver | driver@example.com | password |

---

## 📊 Verify Database in MySQL Workbench

After running migrations, you can check the database:

### 1. Open MySQL Workbench
### 2. Connect to your database
### 3. Run this query to see all tables:
```sql
USE vehicle_maintenance;
SHOW TABLES;
```

You should see 12 tables:
- users
- vehicles
- drivers
- parts
- vendors
- work_orders
- work_order_parts
- fuel_logs
- vehicle_assignments
- documents
- service_schedules
- fuel_cards

### 4. View sample data:
```sql
-- View users
SELECT id, name, email, role FROM users;

-- View vehicles
SELECT id, make, model, year, plate_number, status FROM vehicles;

-- View work orders
SELECT id, work_order_number, type, status, priority FROM work_orders;
```

---

## 🔧 Troubleshooting

### ❌ PHP not found
**Problem**: `php` command not recognized

**Solution**:
1. Add PHP to Windows PATH:
   - Search "Environment Variables" in Windows
   - Edit "Path" in System Variables
   - Add: `C:\php` (or your PHP installation directory)
   - Restart terminal

### ❌ Composer not found
**Problem**: `composer` command not recognized

**Solution**: 
- Download and install Composer from [getcomposer.org](https://getcomposer.org/)
- Restart terminal after installation

### ❌ MySQL connection refused
**Problem**: SQLSTATE[HY000] [2002] Connection refused

**Solutions**:
1. Make sure MySQL is running (check XAMPP or MySQL service)
2. Verify credentials in `.env` file
3. Test connection in MySQL Workbench first
4. Check if port 3306 is correct: `netstat -an | findstr 3306`

### ❌ Migration errors
**Problem**: Migration failed or syntax errors

**Solutions**:
1. Drop database and recreate:
   ```sql
   DROP DATABASE vehicle_maintenance;
   CREATE DATABASE vehicle_maintenance;
   ```
2. Run migrations again:
   ```powershell
   php artisan migrate:fresh --seed
   ```

### ❌ Frontend can't connect to API
**Problem**: Network errors in frontend

**Solutions**:
1. Ensure backend is running on `http://localhost:8000`
2. Check `.env` in frontend folder has correct API URL
3. Clear browser cache
4. Check Laravel CORS settings

### ❌ Port already in use
**Problem**: Port 8000 or 3000 already in use

**Solutions**:
1. For backend (port 8000):
   ```powershell
   php artisan serve --port=8001
   ```
   Then update frontend `.env`: `VITE_API_URL=http://localhost:8001/api`

2. For frontend (port 3000):
   ```powershell
   npm run dev -- --port 3001
   ```

---

## 🚀 Quick Start Commands

Once everything is set up, use these commands to start the application:

### Terminal 1 (Backend):
```powershell
cd "D:\new databse work\vehicle-maintenance-system\backend"
php artisan serve
```

### Terminal 2 (Frontend):
```powershell
cd "D:\new databse work\vehicle-maintenance-system\frontend"
npm run dev
```

Then open: `http://localhost:3000`

---

## 📁 Project Structure

```
vehicle-maintenance-system/
├── backend/                    # Laravel Backend
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/
│   │   │   └── Middleware/
│   │   └── Models/
│   ├── database/
│   │   ├── migrations/        # 12 database tables
│   │   └── seeders/
│   ├── routes/
│   │   └── api.php
│   ├── .env                   # Your configuration
│   └── composer.json
│
├── frontend/                  # React Frontend
│   ├── src/
│   │   ├── components/
│   │   ├── contexts/
│   │   ├── pages/
│   │   └── services/
│   ├── .env                   # API URL config
│   └── package.json
│
├── README.md
└── SETUP_GUIDE.md            # This file
```

---

## 🎓 Database Schema Overview

### Core Tables:

1. **users** - User accounts with roles (Administrator, Fleet Manager, Technician, Driver)
2. **vehicles** - Fleet vehicles with GPS, odometer, status tracking
3. **drivers** - Driver profiles with license information
4. **work_orders** - Maintenance and repair tracking
5. **parts** - Inventory management
6. **fuel_logs** - Fuel consumption tracking
7. **vendors** - Service providers

### Relationship Tables:

8. **work_order_parts** - Links parts to work orders
9. **vehicle_assignments** - Assigns drivers to vehicles
10. **service_schedules** - Preventative maintenance schedules
11. **documents** - File attachments (polymorphic)
12. **fuel_cards** - Fuel card management

---

## 🔐 Role-Based Access Control (RBAC)

The system implements role-based permissions:

| Module | Administrator | Fleet Manager | Technician | Driver |
|--------|--------------|---------------|------------|--------|
| System Admin | ✅ Full | ❌ | ❌ | ❌ |
| Financial Data | ✅ Edit | 👁️ View | ❌ | ❌ |
| Vehicles | ✅ Full | ✅ Full | 👁️ View | 👁️ Assigned |
| Work Orders | ✅ Full | ✅ Create/Edit | ✏️ Update | 👁️ View |
| Parts | ✅ Full | ✅ Full | 👁️ View/Use | ❌ |
| Fuel | 👁️ All | 👁️ All | ❌ | ✏️ Own |
| Reports | ✅ All | 📊 Fleet | 📊 Service | 📊 Own |

---

## 📡 API Endpoints

Base URL: `http://localhost:8000/api`

### Authentication:
- `POST /register` - Register user
- `POST /login` - Login
- `POST /logout` - Logout
- `GET /me` - Current user
- `PUT /profile` - Update profile

### Dashboard:
- `GET /dashboard` - Metrics and charts
- `GET /dashboard/vehicle-locations` - GPS data

### Vehicles:
- `GET /vehicles` - List all
- `POST /vehicles` - Create (Admin/Manager)
- `GET /vehicles/{id}` - View details
- `PUT /vehicles/{id}` - Update (Admin/Manager)
- `DELETE /vehicles/{id}` - Delete (Admin/Manager)

### Work Orders:
- `GET /work-orders` - List all
- `POST /work-orders` - Create (Admin/Manager)
- `PUT /work-orders/{id}` - Update
- `GET /work-orders/statistics` - Stats

### Parts:
- `GET /parts` - List all
- `POST /parts` - Create (Admin/Manager)
- `GET /parts/low-stock` - Low stock alerts
- `POST /parts/{id}/adjust` - Adjust inventory

### Fuel:
- `GET /fuel` - List fuel logs
- `POST /fuel` - Create log
- `GET /fuel/statistics` - Statistics
- `GET /fuel/economy` - Economy report

---

## 🛠️ Development Tips

### Clear Laravel Cache:
```powershell
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Reset Database:
```powershell
php artisan migrate:fresh --seed
```

### Run Tests:
```powershell
php artisan test
```

### Build Frontend for Production:
```powershell
npm run build
```

---

## 📞 Need Help?

1. Check this guide's troubleshooting section
2. Review Laravel docs: https://laravel.com/docs
3. Review React docs: https://react.dev
4. Check MySQL Workbench docs: https://dev.mysql.com/doc/workbench/

---

## ✅ Setup Checklist

- [ ] MySQL installed and running
- [ ] Database `vehicle_maintenance` created
- [ ] PHP 8.2+ installed
- [ ] Composer installed
- [ ] Node.js 18+ installed
- [ ] Backend dependencies installed (`composer install`)
- [ ] Backend `.env` configured with database credentials
- [ ] Backend key generated (`php artisan key:generate`)
- [ ] Database migrated and seeded (`php artisan migrate:fresh --seed`)
- [ ] Backend server running (`php artisan serve`)
- [ ] Frontend dependencies installed (`npm install`)
- [ ] Frontend `.env` configured
- [ ] Frontend server running (`npm run dev`)
- [ ] Can login at `http://localhost:3000`

---

**You're all set! Happy coding! 🚀**
