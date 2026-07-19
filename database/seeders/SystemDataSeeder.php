<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Driver;
use App\Models\Employee;
use App\Models\Fleet;
use App\Models\FleetDriverHistory;
use App\Models\FuelBalance;
use App\Models\FuelRequisition;
use App\Models\MaintenanceLog;
use App\Models\Order;
use App\Models\OrderLeg;
use App\Models\OrderStatusHistory;
use App\Models\Trip;
use App\Models\User;
use App\Support\TanzaniaRegions;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SystemDataSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        // Truncate tables to allow fresh re-seeding without duplicate keys
        OrderLeg::query()->truncate();
        OrderStatusHistory::query()->truncate();
        FuelBalance::query()->truncate();
        FuelRequisition::query()->truncate();
        Order::query()->truncate();
        Trip::query()->truncate();
        FleetDriverHistory::query()->truncate();
        Driver::query()->truncate();
        Fleet::query()->truncate();
        Customer::query()->truncate();
        Employee::query()->truncate();
        
        // Remove non-admin users
        User::query()->where('email', '!=', 'mginijaphet10@gmail.com')->delete();

        Schema::enableForeignKeyConstraints();

        $adminUser = User::query()->where('email', 'mginijaphet10@gmail.com')->first();
        if (!$adminUser) {
            $adminUser = User::query()->create([
                'name' => 'Japhet Mgini',
                'email' => 'mginijaphet10@gmail.com',
                'password' => Hash::make('admin@123'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            $adminUser->assignRole('Chief Admin');
        }

        // 1. CREATE 250 EMPLOYEES AND USER ACCOUNTS
        $positions = [
            'Order Creator' => 'Logistics Officer',
            'Fleet Officer' => 'Fleet Supervisor',
            'Fuel Officer' => 'Fuel Accountant',
            'Approver' => 'Operations Manager',
            'Accountant' => 'Finance Controller',
            'HR Officer' => 'HR Specialist'
        ];

        $firstNames = ['Baraka', 'Neema', 'Joseph', 'Aisha', 'Emmanuel', 'Dorice', 'Kelvin', 'Hawa', 'Upendo', 'Peter', 'Ally', 'Hassan', 'Juma', 'Ramadhan', 'Salum', 'John', 'Athumani', 'Bakari', 'Said', 'Khalfan', 'Hamisi', 'Yusuf', 'Ibrahim', 'Kassim', 'Mustafa', 'Omari', 'Mussa', 'Selemani', 'Khalid', 'Said'];
        $lastNames = ['Masanja', 'Kiwanga', 'Mushi', 'Salum', 'Temu', 'Mugyabuso', 'Shayo', 'Kassim', 'Saria', 'Lema', 'Mwangi', 'Kamau', 'Ondieki', 'Obinna', 'Chukwu', 'Karanja', 'Njoroge', 'Moyo', 'Sithole', 'Dube', 'Sibanda', 'Phiri', 'Banda', 'Mulenga', 'Mwape', 'Kunda', 'Tembo', 'Chanda', 'Mumba', 'Kapoor'];

        $users = [$adminUser];
        
        for ($i = 1; $i <= 250; $i++) {
            $first = $firstNames[($i - 1) % count($firstNames)] . $i;
            $last = $lastNames[($i - 1) % count($lastNames)];
            $email = Str::lower($first . '.' . $last . '@nexusflow.co.tz');
            
            $role = array_keys($positions)[$i % count($positions)];
            $position = $positions[$role];

            $employee = Employee::query()->create([
                'employee_number' => 'EMP-' . str_pad((string) ($i + 100), 4, '0', STR_PAD_LEFT),
                'first_name' => $first,
                'middle_name' => 'J.',
                'last_name' => $last,
                'phone_number' => '071' . str_pad((string) rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                'email' => $email,
                'address' => 'Dar es Salaam, Tanzania',
                'gender' => $i % 2 === 0 ? 'male' : 'female',
                'marital_status' => 'married',
                'date_of_birth' => Carbon::now()->subYears(rand(25, 45)),
                'position_title' => $position,
                'date_employed' => Carbon::now()->subMonths(rand(6, 36)),
                'contract_duration_months' => 24,
                'contract_end_date' => Carbon::now()->addMonths(rand(12, 24)),
                'bank_account_name' => $first . ' ' . $last,
                'bank_account_number' => 'ACC-' . rand(100000, 999999),
                'bank_branch' => 'Dar es Salaam',
                'salary_net' => rand(800000, 2500000),
                'employment_status' => 'active',
                'status_effective_date' => now(),
                'created_by' => $adminUser->id,
            ]);

            $user = User::query()->create([
                'name' => $employee->full_name,
                'email' => $employee->email,
                'password' => Hash::make('Password@123'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            $user->assignRole($role);
            $users[] = $user;
        }

        // 2. CREATE 200 CUSTOMERS
        $customerBrands = ['Group', 'Tanzania Ltd', 'Distributors', 'Enterprise', 'Logistics', 'Millers', 'Sugar Ltd', 'Cement Co', 'Beverages'];
        $customers = [];
        for ($i = 1; $i <= 200; $i++) {
            $brand = $customerBrands[$i % count($customerBrands)];
            $name = $firstNames[$i % count($firstNames)] . ' ' . $brand . ' #' . $i;
            $customers[] = Customer::query()->create([
                'name' => $name,
                'contact_person' => 'Representative ' . $i,
                'phone' => '075' . str_pad((string) rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                'email' => 'client_' . $i . '@nexusflow.co.tz',
                'address' => 'Plot ' . rand(10, 500) . ', Bagamoyo Road, Dar es Salaam',
            ]);
        }

        // 3. CREATE 250 FLEETS (with some overdue/approaching service)
        $fleets = [];
        for ($i = 1; $i <= 250; $i++) {
            $currentOdo = rand(40000, 150000);
            $interval = 10000;
            
            // Randomly make 40 fleets OVERDUE, and 40 approaching maintenance
            if ($i <= 40) {
                // Overdue
                $lastOdo = $currentOdo - 11000; // driven 11000km since service
                $nextDue = $lastOdo + $interval; // nextDue is less than currentOdo (overdue)
            } elseif ($i <= 80) {
                // Approaching (remaining distance <= 500km)
                $lastOdo = $currentOdo - 9700; // driven 9700km
                $nextDue = $lastOdo + $interval; // remaining is 300km
            } else {
                // Good health
                $lastOdo = $currentOdo - rand(1000, 5000);
                $nextDue = $lastOdo + $interval;
            }

            $fleets[] = Fleet::query()->create([
                'fleet_code' => 'FLT-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'vehicle_type' => $i % 3 === 0 ? 'Rigid Truck' : 'Semi-Trailer',
                'plate_number' => 'T ' . rand(100, 999) . ' ' . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)),
                'trailer_number' => 'T ' . rand(100, 999) . ' ' . chr(rand(65, 90)) . chr(rand(65, 90)) . 'T',
                'capacity_tons' => rand(15, 35),
                'current_odometer' => $currentOdo,
                'last_service_odometer' => $lastOdo,
                'oil_change_interval_km' => $interval,
                'next_service_due_km' => $nextDue,
                'fuel_consumption_benchmark' => 0.5,
                'status' => $i % 15 === 0 ? 'maintenance' : ($i % 25 === 0 ? 'unavailable' : 'available'),
                'notes' => 'Seeded bulk fleet record.',
            ]);
        }

        // 4. CREATE 250 DRIVERS (with expired / expiring license dates)
        $drivers = [];
        for ($i = 1; $i <= 250; $i++) {
            $name = $firstNames[($i - 1) % count($firstNames)] . ' ' . $lastNames[rand(0, count($lastNames) - 1)] . ' #' . $i;
            
            // 50 drivers with expired license, 30 expiring soon, rest valid
            if ($i <= 50) {
                // Expired
                $expiry = Carbon::now()->subDays(rand(1, 100));
            } elseif ($i <= 80) {
                // Expiring soon (within next 30 days)
                $expiry = Carbon::now()->addDays(rand(1, 29));
            } else {
                // Valid
                $expiry = Carbon::now()->addDays(rand(100, 500));
            }

            $drivers[] = Driver::query()->create([
                'fleet_id' => null,
                'name' => $name,
                'license_number' => 'DL-' . str_pad((string) ($i + 500000), 6, '0', STR_PAD_LEFT),
                'certificate_number' => 'CERT-' . str_pad((string) ($i + 200000), 6, '0', STR_PAD_LEFT),
                'certificate_expiry_date' => $expiry->clone()->addYear(),
                'license_expiry_date' => $expiry,
                'license_renewed_place' => 'Dar es Salaam',
                'mobile_number' => '078' . str_pad((string) rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                'driver_address' => 'Dar es Salaam, Tanzania',
                'contact1_name' => 'Next of Kin #' . $i,
                'contact1_phone' => '071' . str_pad((string) rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                'contact1_address' => 'Dar es Salaam',
                'is_active' => true,
                'created_by' => $adminUser->id,
            ]);
        }

        // Map first 200 drivers to fleets historically
        for ($i = 0; $i < min(count($fleets), count($drivers)); $i++) {
            $fleet = $fleets[$i];
            $driver = $drivers[$i];
            
            $driver->update(['fleet_id' => $fleet->id]);
            
            FleetDriverHistory::query()->create([
                'fleet_id' => $fleet->id,
                'driver_id' => $driver->id,
                'assigned_at' => Carbon::now()->subMonths(rand(1, 5)),
            ]);
        }

        // 5. CREATE 50 TRIPS & 250 ORDERS
        $regions = TanzaniaRegions::names();
        $stations = ["TotalEnergies Shekilango", "Puma Morogoro Rd", "Shell Mwenge", "GBP Kibaha", "Oryx Chalinze"];
        $paymentChannels = ["Fuel Card", "M-Pesa", "Amana Bank Transfer", "CRDB Bank Transfer"];

        // Create 50 trips
        $trips = [];
        for ($t = 1; $t <= 50; $t++) {
            $creator = $users[array_rand($users)];
            $trip = Trip::query()->create([
                'trip_number' => 'TRP-' . Carbon::now()->subDays(60 - $t)->format('Ymd') . '-' . str_pad((string) $t, 3, '0', STR_PAD_LEFT),
                'status' => $t <= 40 ? 'closed' : 'open',
                'created_by' => $creator->id,
                'created_at' => Carbon::now()->subDays(60 - $t),
            ]);

            if ($t <= 40) {
                $trip->update([
                    'closed_by' => $adminUser->id,
                    'closed_at' => Carbon::now()->subDays(60 - $t)->addHours(8),
                ]);
            }
            $trips[] = $trip;
        }

        // Create 250 orders distributed across trips
        $ordersCount = 250;
        for ($o = 1; $o <= $ordersCount; $o++) {
            $trip = $trips[$o % count($trips)];
            $customer = $customers[array_rand($customers)];
            $origin = $regions[array_rand($regions)];
            $dest = $regions[array_rand($regions)];
            while ($dest === $origin) {
                $dest = $regions[array_rand($regions)];
            }

            $distance = rand(100, 800);
            $estimatedFuel = $distance * 0.5;

            if ($trip->status === 'closed') {
                $status = 'completed';
            } else {
                $status = ['created', 'processing', 'assigned', 'transportation', 'completed'][rand(0, 4)];
            }

            $orderCreator = $users[array_rand($users)];
            $order = Order::query()->create([
                'trip_id' => $trip->id,
                'customer_id' => $customer->id,
                'cargo_type' => rand(0, 1) === 0 ? 'General Cargo' : 'Special Delivery',
                'cargo_description' => 'Bulk cargo container transportation',
                'weight_tons' => rand(15, 30),
                'origin_address' => $origin,
                'destination_address' => $dest,
                'expected_loading_date' => Carbon::now()->subDays(60 - ($o % 50)),
                'expected_leaving_date' => Carbon::now()->subDays(60 - ($o % 50))->addDay(),
                'order_number' => 'ORD-' . Carbon::now()->subDays(60 - ($o % 50))->format('Ymd') . '-' . str_pad((string) $o, 4, '0', STR_PAD_LEFT),
                'distance_km' => $distance,
                'estimated_fuel_litres' => $estimatedFuel,
                'status' => $status,
                'created_by' => $orderCreator->id,
                'created_at' => Carbon::now()->subDays(60 - ($o % 50)),
            ]);

            OrderStatusHistory::query()->create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => 'created',
                'changed_by' => $orderCreator->id,
                'remarks' => 'Order created via seeding.',
                'created_at' => $order->created_at,
            ]);

            if ($status === 'created') {
                continue;
            }

            // Processing status
            OrderStatusHistory::query()->create([
                'order_id' => $order->id,
                'from_status' => 'created',
                'to_status' => 'processing',
                'changed_by' => $orderCreator->id,
                'remarks' => 'Order processing started.',
                'created_at' => $order->created_at->addMinutes(30),
            ]);

            // Assign leg
            $fleetIdx = $o % count($fleets);
            $fleet = $fleets[$fleetIdx];
            $driver = $drivers[$fleetIdx];

            $leg = OrderLeg::query()->create([
                'order_id' => $order->id,
                'fleet_id' => $fleet->id,
                'driver_id' => $driver->id,
                'trailer_number' => $fleet->trailer_number,
                'leg_sequence' => 1,
                'origin_address' => $origin,
                'destination_address' => $dest,
                'distance_km' => $distance,
                'status' => $status === 'completed' ? 'completed' : 'active',
                'completed_at' => $status === 'completed' ? Carbon::now()->subDays(60 - ($o % 50))->addDays(2) : null,
                'created_at' => $order->created_at->addHours(1),
            ]);

            OrderStatusHistory::query()->create([
                'order_id' => $order->id,
                'from_status' => 'processing',
                'to_status' => 'assigned',
                'changed_by' => $orderCreator->id,
                'remarks' => 'Fleet and driver assigned.',
                'created_at' => $order->created_at->addHours(1),
            ]);

            if ($status === 'processing' || $status === 'assigned') {
                continue;
            }

            // Fuel Balance (Create 200 fuel balances)
            $balanceAmt = rand(0, 30);
            FuelBalance::query()->create([
                'order_id' => $order->id,
                'fleet_id' => $fleet->id,
                'remaining_litres' => $balanceAmt,
                'remarks' => 'Starting balance checked.',
                'updated_by' => $orderCreator->id,
                'created_at' => $order->created_at->addHours(2),
            ]);

            // Requisitions
            $reqAmt = max($estimatedFuel - $balanceAmt, 0);
            $price = rand(2950, 3100);
            $totalAmt = $reqAmt * $price;

            $reqStatus = 'submitted';
            if ($status === 'transportation') {
                $reqStatus = ['supervisor_approved', 'accountant_approved', 'submitted', 'accountant_rejected'][rand(0, 3)];
            } elseif ($status === 'completed') {
                $reqStatus = 'accountant_approved';
            }

            $requester = $users[array_rand($users)];
            $supervisor = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'Approver'))->first() ?? $adminUser;
            $accountant = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'Accountant'))->first() ?? $adminUser;

            $req = FuelRequisition::query()->create([
                'order_id' => $order->id,
                'requisition_type' => 'order_based',
                'fleet_id' => $fleet->id,
                'requested_by' => $requester->id,
                'fuel_station' => $stations[array_rand($stations)],
                'base_distance_km' => $distance,
                'additional_distance_km' => 0.0,
                'total_distance_km' => $distance,
                'estimated_fuel_litres' => $estimatedFuel,
                'available_balance_litres' => $balanceAmt,
                'additional_litres' => $reqAmt,
                'fuel_price' => $price,
                'total_amount' => $totalAmt,
                'payment_channel' => $paymentChannels[array_rand($paymentChannels)],
                'payment_account' => 'ACC-' . rand(100000, 999999),
                'status' => $reqStatus === 'accountant_rejected' ? 'submitted' : $reqStatus,
                'created_at' => $order->created_at->addHours(2)->addMinutes(30),
            ]);

            if (in_array($reqStatus, ['supervisor_approved', 'accountant_approved', 'accountant_rejected'], true)) {
                $req->update([
                    'supervisor_id' => $supervisor->id,
                    'supervisor_remarks' => 'Verified parameters. Approved.',
                    'supervisor_reviewed_at' => $req->created_at->addHours(1),
                ]);
            }

            if ($reqStatus === 'accountant_approved') {
                $req->update([
                    'status' => 'accountant_approved',
                    'accountant_id' => $accountant->id,
                    'accountant_remarks' => 'Payment approved.',
                    'accountant_reviewed_at' => $req->created_at->addHours(2),
                ]);

                OrderStatusHistory::query()->create([
                    'order_id' => $order->id,
                    'from_status' => 'assigned',
                    'to_status' => 'transportation',
                    'changed_by' => $accountant->id,
                    'remarks' => 'Fuel approved and dispatched.',
                    'created_at' => $req->created_at->addHours(2),
                ]);
            }

            if ($reqStatus === 'accountant_rejected') {
                $req->update([
                    'status' => 'submitted',
                    'accountant_id' => $accountant->id,
                    'accountant_remarks' => 'Returned to manager: mileage mismatch.',
                    'accountant_reviewed_at' => $req->created_at->addHours(2),
                ]);
            }

            if ($status === 'completed') {
                $order->update(['status' => 'completed']);
                
                OrderStatusHistory::query()->create([
                    'order_id' => $order->id,
                    'from_status' => 'transportation',
                    'to_status' => 'completed',
                    'changed_by' => $orderCreator->id,
                    'remarks' => 'Delivery closed.',
                    'created_at' => $order->created_at->addDays(2),
                ]);
            }
        }

        // 6. CREATE 200 MAINTENANCE LOGS
        $maintCategories = ['Engine', 'Brakes', 'Tires', 'Suspension', 'Electrical', 'Transmission', 'Exhaust', 'Bodywork'];
        $maintServices = ['Routine Service', 'Brake Lining Renewal', 'Tyre Rotation & Balancing', 'Leaf Spring Replacement', 'Starter Motor Refurbishing', 'Gearbox Oil Change', 'Exhaust Weld', 'Mudguard Repair'];
        $maintRemarks = ['Completed regular service check', 'Replaced worn out lining', 'Swapped front to rear tires', 'Replaced leaves on rear left', 'Replaced brushes', 'Cleaned components', 'Fixed leaks', 'Repainted side panels'];

        for ($ml = 1; $ml <= 200; $ml++) {
            $fleet = $fleets[$ml % count($fleets)];
            $cost = rand(100, 1500) * 1000;
            $odo = $fleet->current_odometer - rand(1000, 20000);
            
            $log = MaintenanceLog::query()->create([
                'fleet_id' => $fleet->id,
                'service_type' => $maintServices[$ml % count($maintServices)],
                'odometer_reading' => max(0, $odo),
                'cost' => $cost,
                'performed_at' => Carbon::now()->subDays(rand(5, 120)),
                'remarks' => $maintRemarks[$ml % count($maintRemarks)],
                'performed_by' => $users[array_rand($users)]->id,
            ]);

            // Save individual items
            $itemCount = rand(1, 3);
            for ($it = 0; $it < $itemCount; $it++) {
                $category = $maintCategories[($ml + $it) % count($maintCategories)];
                $log->items()->create([
                    'category' => $category,
                    'description' => 'Detail for ' . strtolower($category),
                    'cost' => $cost / $itemCount,
                    'installed_at_km' => $odo,
                    'lifespan_km' => 15000,
                    'next_due_km' => $odo + 15000,
                ]);
            }
        }
    }
}
