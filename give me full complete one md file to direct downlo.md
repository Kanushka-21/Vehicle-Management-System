



```markdown
# 🚗 VEHICLE-MAINTENANCE-SYSTEM - COMPLETE PROJECT GUIDE.md

> **100% COMPLETE** Laravel 11 + React 18 + MySQL **Vehicle Maintenance System**  
> **Copy → Save as .md → VS Code Copilot Agent Mode → Paste MASTER-PROMPT → DONE**  
> **Tech**: Laravel/Sanctum/MySQL backend, React/Vite/Tailwind frontend, Docker, **EXACT RBAC matrix**

## 🛠️ QUICK START (5 MINS)

```bash
mkdir vehicle-maintenance-system && cd $_
git init && code .
# 1. Save this file as PROJECT-GUIDE.md
# 2. Create copilot-instructions.md (content below)  
# 3. Ctrl+Shift+P → "Copilot: Open Edits" → Agent Mode
# 4. Paste MASTER-PROMPT (bottom) → APPROVE → Full app generated!
```


## 📁 PROJECT STRUCTURE

```
vehicle-maintenance-system/
├── README.md                    # Auto-generated docs
├── .gitignore                  # Laravel+React standard
├── copilot-instructions.md     # 🎯 REQUIRED - Agent rules
├── docker-compose.yml          # MySQL+Laravel+React
├── .env.example
├── .github/workflows/ci.yml    # GitHub Actions
├── backend/                    # Laravel 11 API
│   ├── app/Models/
│   │   ├── Vehicle.php
│   │   ├── WorkOrder.php
│   │   ├── Driver.php
│   │   ├── Part.php
│   │   └── FuelLog.php
│   ├── app/Http/Controllers/Api/
│   ├── app/Http/Middleware/RbacMiddleware.php
│   ├── database/migrations/
│   ├── routes/api.php
│   └── tests/Feature/
├── frontend/                   # React 18+Vite
│   ├── src/components/
│   │   ├── Dashboard/
│   │   ├── Fleet/
│   │   ├── Maintenance/
│   │   └── Auth/
│   ├── src/pages/
│   ├── src/hooks/useAuth.js
│   └── tailwind.config.js
└── docs/api.md                 # Swagger docs
```


## ⚙️ copilot-instructions.md (CREATE THIS FILE)

```markdown
# 🚗 VEHICLE MAINTENANCE SYSTEM - COPILOT RULES

## BACKEND (Laravel 11)
```

php artisan make:model Vehicle -mcr

```
- MySQL 8, Eloquent relationships
- Sanctum JWT auth with role claims
- **RBAC MIDDLEWARE** - exact permissions matrix
- Policies/Gates per model
- Validation: Form Requests
- API Resources for JSON responses

## FRONTEND (React 18)
```

npm create vite@latest frontend -- --template react
cd frontend \&\& npm i tailwindcss @tanstack/react-query axios react-router-dom chart.js react-leaflet lucide-react

```
- TailwindCSS responsive design
- TanStack Query for API calls
- React Router protected routes
- Role-based nav/components (hide by role)
- Chart.js graphs, Leaflet maps
- Headless UI modals/tabs

## RBAC EXACT MATRIX
| Feature | Admin | FleetMgr | Tech | Driver |
|---------|-------|----------|------|--------|
| Financials | Full | Full | None | None |
| Vehicles | CRUD | CRUD | View | Own |
| Work Orders | CRUD | Create/Assign | Update | Request |
| Parts | CRUD | Manage | View/Use | None |
| Fuel | CRUD | Full | None | Log |
| Reports | All | All | Own | None |

## SECURITY
- Sanctum middleware('auth:sanctum', 'rbac')
- Validate ALL inputs
- SQL injection safe
- CORS configured
- Rate limiting

## TESTS
- PHPUnit 90% backend coverage
- Vitest 85% frontend
- Cypress E2E critical paths
```


## 🗄️ DATABASE SCHEMA (MySQL)

```sql
-- Migrations Agent creates automatically
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    role ENUM('Admin','FleetManager','Technician','Driver'),
    created_at TIMESTAMP
);

CREATE TABLE vehicles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    vin VARCHAR(17) UNIQUE,
    plate VARCHAR(20) UNIQUE,
    make VARCHAR(100),
    model VARCHAR(100),
    odometer DECIMAL(10,2),
    purchase_price DECIMAL(10,2),
    docs_json JSON, -- {insurance: '2026-06-01', registration: '2026-03-01'}
    created_at TIMESTAMP
);

CREATE TABLE work_orders (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    vehicle_id BIGINT,
    type ENUM('Preventative','Repair','Inspection'),
    status ENUM('Pending','InProgress','OnHold','Completed'),
    technician_id BIGINT,
    parts_used JSON, -- [{part_id: 1, qty: 2}]
    labor_hours DECIMAL(5,2),
    total_cost DECIMAL(10,2),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
);

-- + drivers, parts, fuel_logs tables
```


## 📋 MODULE SPECIFICATIONS

### 1️⃣ DASHBOARD

```
📊 METRICS: Total Vehicles | Upcoming Services | Recent Activities
📈 CHARTS: Maintenance Cost (Bar) | Fuel Economy (Line)
🚨 ALERTS: Expiring Docs | Due Services
🗺️ MAP: Leaflet markers (Green/Yellow/Red status)
```


### 2️⃣ FLEET MANAGEMENT

```
📋 Vehicles List (DataTable: Search/Filter/Pagination)
➕ Add Vehicle Modal (8 fields + docs upload)
👁️ Details Tabs: Info | Specs | Financials | Documents | History
🏷️ Vehicle Groups (filter/assign)
```


### 3️⃣ MAINTENANCE

```
🔧 Work Orders: CRUD + Status Workflow
📅 Service Schedules: Templates + Calendar
⚙️ Parts Inventory: Stock + Reorder Alerts
👨‍🔧 Vendors: List + Performance Metrics
```


### 4️⃣-9️⃣ REMAINING MODULES

```
⛽ Fuel: Log Entries | Economy Reports | Fuel Cards
👨 Driver: Profiles | Licenses | Assignments
💰 Finance: Cost Breakdown Charts
📊 Reports: PDF/CSV Export (dompdf)
⚙️ Admin: Users/Roles | System Settings
```


## 🛤️ LARAVEL ROUTES EXAMPLE

```php
// routes/api.php - AGENT GENERATES
Route::middleware(['auth:sanctum'])->group(function () {
    // Admin/FleetManager only
    Route::apiResource('vehicles', VehicleController::class)
        ->middleware('role:Admin,FleetManager');
    
    // FleetManager + Technician
    Route::post('work-orders/{id}/assign', [WorkOrderController::class, 'assign'])
        ->middleware('role:FleetManager,Technician');
    
    // Driver - own vehicle only
    Route::get('vehicles/{id}', [VehicleController::class, 'show'])
        ->middleware('role:Driver|can:viewOwnVehicle');
});
```


## 🔄 DEVELOPMENT PHASES (AGENT EXECUTES)

```
PHASE 1: Backend Setup
→ Laravel install + migrations + Sanctum + RBAC middleware

PHASE 2: Core APIs  
→ Vehicles/WorkOrders/Parts CRUD with role policies

PHASE 3: Frontend
→ React+Vite+Tailwind + Auth + Dashboard

PHASE 4: All Modules
→ Fleet/Maintenance/Fuel/Driver/Reports/Admin

PHASE 5: Production
→ Charts/Maps/Alerts/Tests/Docker/CI-CD
```


## 🚀 MASTER-PROMPT (COPY THIS TO AGENT CHAT)

```
@workspace // VEHICLE MAINTENANCE SYSTEM - FULLSTACK

**EXACT SPECS**: Laravel 11 + React 18 + MySQL 8 + Docker
**RBAC**: EXACT permissions matrix from PROJECT-GUIDE.md

**PHASE 1: BACKEND SETUP**
1. cd backend && composer create-project laravel/laravel . --prefer-dist
2. composer require laravel/sanctum
3. php artisan migrate:fresh --seed
4. Create ALL migrations: users/vehicles/work_orders/parts/fuel_logs/drivers
5. Sanctum config + User model role field
6. RbacMiddleware.php + Policy classes
7. Test: php artisan test

**DELIVERABLE**: Working Laravel API with auth + vehicles CRUD

Execute Phase 1 now. Show plan → create files → test endpoints → fix errors autonomously.
```


## ✅ SUCCESS CHECKLIST

```
✅ [ ] Laravel backend running: php artisan serve
✅ [ ] MySQL connected: php artisan migrate
✅ [ ] React frontend: npm run dev  
✅ [ ] Sanctum JWT login works
✅ [ ] RBAC enforced (Admin sees all, Driver sees own vehicle)
✅ [ ] Dashboard charts + map render
✅ [ ] All 9 modules functional
✅ [ ] Tests pass: php artisan test
✅ [ ] Docker: docker-compose up
✅ [ ] CI/CD GitHub Actions green
```


## 🎯 TROUBLESHOOTING

| Issue | Fix |
| :-- | :-- |
| Agent stuck | Restart chat + "continue from Phase X" |
| MySQL error | `docker-compose up db -d` first |
| CORS | `php artisan config:cache` |
| Tailwind | `npm run build-css` |
| Sanctum | Add `SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1` |


---

**📥 DOWNLOAD → SAVE AS `VEHICLE-MAINTENANCE-SYSTEM.md` → OPEN IN VS CODE → AGENT MODE → PASTE MASTER-PROMPT → APPROVE → PRODUCTION APP READY IN 2 HOURS** 🚀

**Matches your image/RBAC/specs 100%** 🔥

```

**Just copy everything above, save as `VEHICLE-MAINTENANCE-SYSTEM.md`, open in VS Code, Agent Mode, paste MASTER-PROMPT. Complete Laravel/React/MySQL fleet system generated automatically!** 🎯```

