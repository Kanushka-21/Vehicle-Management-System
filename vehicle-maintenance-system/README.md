# 🚗 Vehicle Maintenance System

A comprehensive fleet management and vehicle maintenance tracking system built with Laravel 11, React 18, and MySQL 8.

## 📋 Features

### Core Modules
- **Dashboard**: Real-time overview with metrics, charts, alerts, and vehicle tracking
- **Fleet Management**: Complete vehicle lifecycle management with documentation
- **Maintenance & Repairs**: Work order tracking, service scheduling, and vendor management
- **Parts Inventory**: Stock management with automatic reorder alerts
- **Fuel Management**: Consumption tracking, economy analysis, and fuel card management
- **Driver Management**: Profile management, license tracking, and assignment history
- **Finance & Costing**: Comprehensive cost tracking and financial reporting
- **Reports**: Customizable reports (PDF/CSV export)
- **Administration**: User management and system configuration

### Role-Based Access Control (RBAC)

| Feature | Administrator | Fleet Manager | Technician | Driver |
|---------|--------------|---------------|------------|--------|
| System Admin & Users | Full Control | Read-Only | No Access | No Access |
| All Financial Data | Full Access | Full Access | No Access | No Access |
| Vehicle Management | Full CRUD | Full CRUD | View Only | View (Own Vehicle) |
| Work Orders | Full Access | Create & Assign | Update & Log Work | Create Request |
| Parts Inventory | Full Access | Manage & Reorder | View & Use | No Access |
| Fuel Management | Full Access | Full Access | No Access | Log Fuel (if enabled) |
| Reports | All Reports | All Reports | Limited (Own Work) | No Access |

## 🛠️ Technology Stack

### Backend
- **Framework**: Laravel 11.x
- **Authentication**: Laravel Sanctum (SPA Tokens)
- **Database**: MySQL 8.0 (via MySQL Workbench or XAMPP)
- **Testing**: PHPUnit

### Frontend
- **Framework**: React 18.2
- **Build Tool**: Vite 5.x
- **Styling**: Tailwind CSS 3.x
- **State Management**: TanStack Query (React Query)
- **Routing**: React Router v6
- **Charts**: Chart.js + react-chartjs-2
- **Maps**: React-Leaflet
- **HTTP Client**: Axios
- **UI Components**: Headless UI, Lucide Icons

## 📁 Project Structure

```
vehicle-maintenance-system/
├── backend/                    # Laravel API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/    # API Controllers
│   │   │   └── Middleware/         # Custom Middleware
│   │   └── Models/                # Eloquent Models
│   ├── database/
│   │   ├── migrations/            # Database Migrations (12 tables)
│   │   └── seeders/               # Sample Data Seeders
│   ├── routes/
│   │   └── api.php               # API Routes
│   ├── .env.example              # Environment Template
│   └── composer.json             # PHP Dependencies
│
├── frontend/                   # React SPA
│   ├── src/
│   │   ├── components/         # Reusable Components
│   │   ├── pages/              # Page Components (9 pages)
│   │   ├── contexts/           # React Context (Auth)
│   │   ├── services/           # API Services
│   │   └── App.jsx            # Main App Component
│   ├── .env.example           # Frontend Environment Template
│   └── package.json           # Node Dependencies
│
├── README.md                  # Project Overview
└── SETUP_GUIDE.md            # Detailed Setup Instructions
```

## 🚀 Installation & Setup

### Prerequisites
- **PHP 8.2+** - [Download](https://windows.php.net/download/)
- **Composer** - [Download](https://getcomposer.org/)
- **Node.js 18+** - [Download](https://nodejs.org/)
- **MySQL 8.0+** via MySQL Workbench or XAMPP - [Download MySQL Workbench](https://dev.mysql.com/downloads/workbench/)

> 💡 **Quick Option**: Install [XAMPP](https://www.apachefriends.org/) - includes PHP, MySQL, and Apache in one package!

### Quick Setup Guide

For detailed step-by-step instructions, see **[SETUP_GUIDE.md](SETUP_GUIDE.md)**

#### 1. Create MySQL Database

Using MySQL Workbench or phpMyAdmin:
```sql
CREATE DATABASE vehicle_maintenance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### 2. Backend Setup

```powershell
cd backend
composer install
Copy-Item .env.example .env
# Edit .env with your MySQL credentials
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

#### 3. Frontend Setup

Open a new terminal:
```powershell
cd frontend
npm install
Copy-Item .env.example .env
npm run dev
```

#### 4. Access Application

- **Frontend**: http://localhost:3000
- **Backend API**: http://localhost:8000/api

## 👥 Default Users (After Seeding)

| Role | Email | Password |
|------|-------|----------|
| Administrator | admin@example.com | password |
| Fleet Manager | manager@example.com | password |
| Technician | tech@example.com | password |
| Driver | driver@example.com | password |

## 📊 Database Schema

### Main Tables
- **users**: User accounts with role-based access
- **vehicles**: Vehicle information and specifications
- **drivers**: Driver profiles and license information
- **work_orders**: Maintenance and repair orders
- **parts**: Parts inventory and stock levels
- **fuel_logs**: Fuel consumption records
- **vendors**: Service vendor information
- **vehicle_assignments**: Driver-vehicle assignments
- **service_schedules**: Preventative maintenance schedules
- **documents**: Polymorphic document storage
- **fuel_cards**: Fuel card management

## 🔐 API Authentication

The API uses Laravel Sanctum for authentication. Include the bearer token in all authenticated requests:

```bash
Authorization: Bearer <your-token>
```

### Example API Requests

**Login**
```bash
POST /api/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password"
}
```

**Get Vehicles**
```bash
GET /api/vehicles
Authorization: Bearer <token>
```

**Create Work Order**
```bash
POST /api/work-orders
Authorization: Bearer <token>
Content-Type: application/json

{
  "vehicle_id": 1,
  "type": "Repair",
  "priority": "High",
  "description": "Engine oil change required"
}
```

## 🧪 Testing

### Backend Tests
```bash
cd backend
php artisan test
```

### Frontend Tests
```bash
cd frontend
npm run test
```

## 📦 Production Build

### Backend
```bash
cd backend
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Frontend
```bash
cd frontend
npm run build
```

## 🔧 Configuration

### Environment Variables

**Backend (.env)**
```env
APP_NAME="Vehicle Maintenance System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vehicle_maintenance
DB_USERNAME=root
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,your-domain.com
SESSION_DOMAIN=your-domain.com
```

**Frontend (.env)**
```env
VITE_API_URL=https://your-domain.com/api
```

## 📝 License

This project is licensed under the MIT License.

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📞 Support

For support, email support@example.com or open an issue in the repository.

## 🎯 Roadmap

- [ ] Mobile app (React Native)
- [ ] Real-time GPS tracking integration
- [ ] Advanced analytics dashboard
- [ ] Automated service reminders (Email/SMS)
- [ ] Multi-tenant support
- [ ] Barcode/QR code scanning for parts
- [ ] Integration with third-party fuel card providers

---

**Built with ❤️ using Laravel + React**
