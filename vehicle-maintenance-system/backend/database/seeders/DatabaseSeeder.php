<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Part;
use App\Models\Vendor;
use App\Models\WorkOrder;
use App\Models\FuelLog;
use App\Models\VehicleAssignment;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Users with different roles
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'Administrator',
            'phone' => '+1-555-0101',
            'is_active' => true,
        ]);

        $fleetManager = User::create([
            'name' => 'Fleet Manager',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'role' => 'Fleet Manager',
            'phone' => '+1-555-0102',
            'is_active' => true,
        ]);

        $technician = User::create([
            'name' => 'John Technician',
            'email' => 'tech@example.com',
            'password' => Hash::make('password'),
            'role' => 'Technician',
            'phone' => '+1-555-0103',
            'is_active' => true,
        ]);

        $driver1 = User::create([
            'name' => 'Mike Driver',
            'email' => 'driver@example.com',
            'password' => Hash::make('password'),
            'role' => 'Driver',
            'phone' => '+1-555-0104',
            'is_active' => true,
        ]);

        $driver2 = User::create([
            'name' => 'Sarah Johnson',
            'email' => 'sarah@example.com',
            'password' => Hash::make('password'),
            'role' => 'Driver',
            'phone' => '+1-555-0105',
            'is_active' => true,
        ]);

        // Create Driver Profiles
        Driver::create([
            'user_id' => $driver1->id,
            'license_number' => 'DL123456789',
            'license_class' => 'C',
            'license_expiry' => now()->addMonths(18),
            'emergency_contact_name' => 'Jane Driver',
            'emergency_contact_phone' => '+1-555-0199',
        ]);

        Driver::create([
            'user_id' => $driver2->id,
            'license_number' => 'DL987654321',
            'license_class' => 'C',
            'license_expiry' => now()->addMonths(24),
            'emergency_contact_name' => 'Tom Johnson',
            'emergency_contact_phone' => '+1-555-0198',
        ]);

        // Create Vehicles
        $vehicles = [
            [
                'vin' => '1HGBH41JXMN109186',
                'plate_number' => 'ABC-1234',
                'year' => 2022,
                'make' => 'Toyota',
                'model' => 'Camry',
                'color' => 'Silver',
                'engine_type' => '2.5L 4-Cylinder',
                'fuel_type' => 'Petrol',
                'odometer' => 15000,
                'purchase_price' => 28000,
                'current_value' => 25000,
                'purchase_date' => '2022-01-15',
                'group' => 'Sedans',
                'status' => 'Active',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
            ],
            [
                'vin' => '2T1BU4EE9DC123456',
                'plate_number' => 'XYZ-5678',
                'year' => 2023,
                'make' => 'Ford',
                'model' => 'F-150',
                'color' => 'Blue',
                'engine_type' => '3.5L V6',
                'fuel_type' => 'Petrol',
                'odometer' => 8000,
                'purchase_price' => 45000,
                'current_value' => 42000,
                'purchase_date' => '2023-03-20',
                'group' => 'Trucks',
                'status' => 'Active',
                'latitude' => 40.7580,
                'longitude' => -73.9855,
            ],
            [
                'vin' => '5NPE24AF2FH123789',
                'plate_number' => 'DEF-9012',
                'year' => 2021,
                'make' => 'Honda',
                'model' => 'CR-V',
                'color' => 'White',
                'engine_type' => '1.5L Turbo',
                'fuel_type' => 'Petrol',
                'odometer' => 22000,
                'purchase_price' => 32000,
                'current_value' => 28000,
                'purchase_date' => '2021-06-10',
                'group' => 'SUVs',
                'status' => 'Active',
                'latitude' => 34.0522,
                'longitude' => -118.2437,
            ],
        ];

        foreach ($vehicles as $vehicleData) {
            Vehicle::create($vehicleData);
        }

        // Create Parts
        $parts = [
            ['part_number' => 'OIL-5W30', 'name' => 'Engine Oil 5W-30', 'category' => 'Lubricants', 
             'manufacturer' => 'Mobil', 'quantity_in_stock' => 50, 'minimum_stock_level' => 10, 
             'unit_price' => 8.99, 'location' => 'Shelf A1'],
            ['part_number' => 'FILTER-OIL', 'name' => 'Oil Filter', 'category' => 'Filters', 
             'manufacturer' => 'Fram', 'quantity_in_stock' => 30, 'minimum_stock_level' => 10, 
             'unit_price' => 12.50, 'location' => 'Shelf A2'],
            ['part_number' => 'FILTER-AIR', 'name' => 'Air Filter', 'category' => 'Filters', 
             'manufacturer' => 'K&N', 'quantity_in_stock' => 25, 'minimum_stock_level' => 10, 
             'unit_price' => 24.99, 'location' => 'Shelf A2'],
            ['part_number' => 'BRAKE-PAD', 'name' => 'Brake Pad Set', 'category' => 'Brakes', 
             'manufacturer' => 'Brembo', 'quantity_in_stock' => 8, 'minimum_stock_level' => 5, 
             'unit_price' => 89.99, 'location' => 'Shelf B1'],
            ['part_number' => 'TIRE-205', 'name' => 'Tire 205/60R16', 'category' => 'Tires', 
             'manufacturer' => 'Michelin', 'quantity_in_stock' => 16, 'minimum_stock_level' => 8, 
             'unit_price' => 129.99, 'location' => 'Tire Rack'],
        ];

        foreach ($parts as $partData) {
            Part::create($partData);
        }

        // Create Vendors
        $vendors = [
            ['name' => 'Quick Auto Repair', 'contact_person' => 'Bob Smith', 
             'email' => 'bob@quickauto.com', 'phone' => '+1-555-1001', 
             'service_type' => 'General Repairs', 'average_rating' => 4.5],
            ['name' => 'Brake Specialists Inc', 'contact_person' => 'Mary Johnson', 
             'email' => 'mary@brakespec.com', 'phone' => '+1-555-1002', 
             'service_type' => 'Brake Services', 'average_rating' => 4.8],
            ['name' => 'Premium Tire Center', 'contact_person' => 'David Lee', 
             'email' => 'david@premiumtire.com', 'phone' => '+1-555-1003', 
             'service_type' => 'Tire Services', 'average_rating' => 4.3],
        ];

        foreach ($vendors as $vendorData) {
            Vendor::create($vendorData);
        }

        // Create Work Orders
        WorkOrder::create([
            'vehicle_id' => 1,
            'technician_id' => $technician->id,
            'work_order_number' => 'WO-2024-000001',
            'type' => 'Preventative',
            'status' => 'Completed',
            'priority' => 'Medium',
            'description' => 'Regular oil change and filter replacement',
            'odometer_reading' => 15000,
            'labor_hours' => 1.5,
            'labor_cost' => 75.00,
            'parts_cost' => 45.00,
            'total_cost' => 120.00,
            'scheduled_date' => now()->subDays(5),
            'started_at' => now()->subDays(5),
            'completed_at' => now()->subDays(5)->addHours(2),
            'downtime_hours' => 2,
        ]);

        WorkOrder::create([
            'vehicle_id' => 2,
            'technician_id' => $technician->id,
            'work_order_number' => 'WO-2024-000002',
            'type' => 'Repair',
            'status' => 'In Progress',
            'priority' => 'High',
            'description' => 'Brake pad replacement - squeaking noise',
            'odometer_reading' => 8000,
            'labor_hours' => 2.0,
            'labor_cost' => 100.00,
            'parts_cost' => 180.00,
            'total_cost' => 280.00,
            'scheduled_date' => now(),
            'started_at' => now()->subHours(3),
        ]);

        // Create Fuel Logs
        FuelLog::create([
            'vehicle_id' => 1,
            'driver_id' => $driver1->id,
            'date' => now()->subDays(3),
            'odometer' => 14850,
            'quantity' => 45.5,
            'unit' => 'Liters',
            'cost_per_unit' => 1.45,
            'total_cost' => 65.98,
            'station' => 'Shell Gas Station',
            'tank_filled' => true,
            'fuel_economy' => 12.5,
        ]);

        FuelLog::create([
            'vehicle_id' => 2,
            'driver_id' => $driver2->id,
            'date' => now()->subDays(2),
            'odometer' => 7950,
            'quantity' => 68.0,
            'unit' => 'Liters',
            'cost_per_unit' => 1.42,
            'total_cost' => 96.56,
            'station' => 'BP Gas Station',
            'tank_filled' => true,
            'fuel_economy' => 9.8,
        ]);

        // Create Vehicle Assignments
        VehicleAssignment::create([
            'vehicle_id' => 1,
            'driver_id' => $driver1->id,
            'assigned_at' => now()->subDays(10),
            'start_odometer' => 14500,
            'status' => 'Active',
            'purpose' => 'Daily Route Assignment',
        ]);

        VehicleAssignment::create([
            'vehicle_id' => 2,
            'driver_id' => $driver2->id,
            'assigned_at' => now()->subDays(7),
            'start_odometer' => 7800,
            'status' => 'Active',
            'purpose' => 'Delivery Route',
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('');
        $this->command->info('Login Credentials:');
        $this->command->info('Administrator: admin@example.com / password');
        $this->command->info('Fleet Manager: manager@example.com / password');
        $this->command->info('Technician: tech@example.com / password');
        $this->command->info('Driver: driver@example.com / password');
    }
}
