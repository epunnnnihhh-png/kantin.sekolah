<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;

class CanteenManager
{
    private static bool $initialized = false;

    public static function getCanteens(): array
    {
        return [
            1 => [
                'id' => 1,
                'name' => 'Kantin 1',
                'title' => 'Kantin 1 - Aneka Nasi Nusantara',
                'category_default' => 'Makanan',
                'tagline' => 'Nasi Kuning, Nasi Uduk & Masakan Nusantara',
                'icon' => 'K1',
                'code_prefix' => 'K1',
                'color' => 'orange',
            ],
            2 => [
                'id' => 2,
                'name' => 'Kantin 2',
                'title' => 'Kantin 2 - Sarapan Pagi & Roti',
                'category_default' => 'Sarapan',
                'tagline' => 'Sarapan Hangat, Roti Bakar & Bubur',
                'icon' => 'K2',
                'code_prefix' => 'K2',
                'color' => 'amber',
            ],
            3 => [
                'id' => 3,
                'name' => 'Kantin 3',
                'title' => 'Kantin 3 - Aneka Minuman Segar',
                'category_default' => 'Minuman',
                'tagline' => 'Es Teh, Aneka Jus Buah & Minuman Dingin',
                'icon' => 'K3',
                'code_prefix' => 'K3',
                'color' => 'emerald',
            ],
            4 => [
                'id' => 4,
                'name' => 'Kantin 4',
                'title' => 'Kantin 4 - Cemilan & Gorengan',
                'category_default' => 'Cemilan',
                'tagline' => 'Risol Mayo, Pastel, Tahu Bakso & Gorengan',
                'icon' => 'K4',
                'code_prefix' => 'K4',
                'color' => 'yellow',
            ],
            5 => [
                'id' => 5,
                'name' => 'Kantin 5',
                'title' => 'Kantin 5 - Mie, Bakso & Soto',
                'category_default' => 'Mie & Bakso',
                'tagline' => 'Bakso Sapi, Mie Ayam & Soto Ayam Hangat',
                'icon' => 'K5',
                'code_prefix' => 'K5',
                'color' => 'red',
            ],
            6 => [
                'id' => 6,
                'name' => 'Kantin 6',
                'title' => 'Kantin 6 - Western Corner & Bento',
                'category_default' => 'Western',
                'tagline' => 'Burger Crispy, Kebab Mini & Bento Rice Box',
                'icon' => 'K6',
                'code_prefix' => 'K6',
                'color' => 'indigo',
            ],
            7 => [
                'id' => 7,
                'name' => 'Kantin 7',
                'title' => 'Kantin 7 - Makan Siang Spesial',
                'category_default' => 'Makan Siang',
                'tagline' => 'Ayam Geprek Sambal Bawang, Nasi Pecel & Ayam Bakar',
                'icon' => 'K7',
                'code_prefix' => 'K7',
                'color' => 'purple',
            ],
        ];
    }

    public static function getCanteenInfo(?int $canteenId): array
    {
        $canteens = self::getCanteens();
        return $canteens[$canteenId] ?? [
            'id' => $canteenId ?: 1,
            'name' => 'Kantin ' . ($canteenId ?: 1),
            'title' => 'Kantin ' . ($canteenId ?: 1),
            'tagline' => 'Stand Makanan & Minuman Sehat SMKN 1 Ciomas',
            'icon' => 'K' . ($canteenId ?: 1),
            'code_prefix' => 'K' . ($canteenId ?: 1),
            'color' => 'emerald',
        ];
    }

    public static function initDatabase(): void
    {
        if (self::$initialized) {
            return;
        }

        try {
            // 1. Alter users table with all required columns
            if (Schema::hasTable('users')) {
                $userCols = [
                    'canteen_id' => 'INTEGER NULL',
                    'school' => 'VARCHAR(255) NULL',
                    'nisn' => 'VARCHAR(50) NULL',
                    'is_present' => 'INTEGER DEFAULT 0',
                    'attendance_date' => 'DATETIME NULL',
                ];
                foreach ($userCols as $col => $sqlType) {
                    try {
                        if (!Schema::hasColumn('users', $col)) {
                            DB::statement("ALTER TABLE users ADD COLUMN {$col} {$sqlType}");
                        }
                    } catch (\Throwable $eCol) {}
                }
            }

            // 2. Alter menus table
            if (Schema::hasTable('menus')) {
                try {
                    if (!Schema::hasColumn('menus', 'canteen_id')) {
                        DB::statement("ALTER TABLE menus ADD COLUMN canteen_id INTEGER DEFAULT 1");
                    }
                } catch (\Throwable $eCol) {}
            }

            // 3. Alter orders table
            if (Schema::hasTable('orders')) {
                $orderCols = [
                    'canteen_id' => 'INTEGER DEFAULT 1',
                    'order_code' => 'VARCHAR(50) DEFAULT NULL',
                    'group_code' => 'VARCHAR(50) DEFAULT NULL',
                    'customer_name' => 'VARCHAR(255) DEFAULT NULL',
                    'is_at_school' => 'INTEGER DEFAULT 0',
                    'gps_distance' => 'FLOAT DEFAULT NULL',
                    'gps_device' => 'VARCHAR(255) DEFAULT NULL',
                ];
                foreach ($orderCols as $col => $sqlType) {
                    try {
                        if (!Schema::hasColumn('orders', $col)) {
                            DB::statement("ALTER TABLE orders ADD COLUMN {$col} {$sqlType}");
                        }
                    } catch (\Throwable $eCol) {}
                }
            }

            // 4. Seed staff accounts for Kantin 1 to 7
            self::seedStaffAccounts();

            // 5. Seed / update menus with correct canteen_id
            self::seedAndCategorizeMenus();

            // 6. Backfill existing orders with canteen_id and order_code
            self::backfillOrderCodes();

            // 7. Seed sample historical orders so sales recap is rich and ready to export
            self::seedSampleSalesHistory();

            self::$initialized = true;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("CanteenManager::initDatabase error: " . $e->getMessage());
        }
    }

    public static function seedStaffAccounts(): void
    {
        $canteens = self::getCanteens();

        // 1. Ensure Super Admin exists & password is 'password'
        try {
            $admin = User::where('email', 'admin@kantin.com')->orWhere('username', 'admin')->first();
            if (!$admin) {
                $admin = new User();
                $admin->name = 'Super Admin Kantin';
                $admin->username = 'admin';
                $admin->email = 'admin@kantin.com';
                $admin->password = Hash::make('password');
                $admin->role = 'admin';
                if (Schema::hasColumn('users', 'school')) $admin->school = 'SMKN 1 CIOMAS';
                if (Schema::hasColumn('users', 'canteen_id')) $admin->canteen_id = null;
                $admin->save();
            } else {
                $admin->password = Hash::make('password');
                $admin->role = 'admin';
                if (Schema::hasColumn('users', 'canteen_id')) $admin->canteen_id = null;
                $admin->save();
            }
        } catch (\Throwable $eAdmin) {
            \Illuminate\Support\Facades\Log::error("Failed seeding super admin: " . $eAdmin->getMessage());
        }

        // 2. Ensure Kantin 1 to 7 staff accounts exist & passwords are 'password'
        foreach ($canteens as $cId => $cData) {
            try {
                $email = "kantin{$cId}@kantin.com";
                $username = "kantin{$cId}";
                $staffName = "Staff {$cData['name']}";

                $user = User::where('email', $email)->orWhere('username', $username)->first();
                if (!$user) {
                    $user = new User();
                    $user->name = $staffName;
                    $user->username = $username;
                    $user->email = $email;
                    $user->password = Hash::make('password');
                    if (Schema::hasColumn('users', 'school')) $user->school = 'SMKN 1 CIOMAS';
                    if (Schema::hasColumn('users', 'canteen_id')) $user->canteen_id = $cId;
                    
                    // Try role 'canteen_staff', fallback to 'admin' if enum check fails
                    try {
                        $user->role = 'canteen_staff';
                        $user->save();
                    } catch (\Throwable $eRole) {
                        $user->role = 'admin';
                        $user->save();
                    }
                } else {
                    $user->password = Hash::make('password');
                    if (Schema::hasColumn('users', 'canteen_id')) $user->canteen_id = $cId;
                    try {
                        $user->role = 'canteen_staff';
                        $user->save();
                    } catch (\Throwable $eRole) {
                        $user->role = 'admin';
                        $user->save();
                    }
                }
            } catch (\Throwable $eStaff) {
                \Illuminate\Support\Facades\Log::error("Failed seeding Kantin {$cId}: " . $eStaff->getMessage());
            }
        }
    }

    public static function seedAndCategorizeMenus(): void
    {
        // Update existing menus based on their category
        $allMenus = Menu::all();
        foreach ($allMenus as $m) {
            $cat = strtolower($m->category ?? '');
            $targetCanteen = 1;
            if (str_contains($cat, 'minum') || str_contains($cat, 'jus') || str_contains($cat, 'teh')) {
                $targetCanteen = 3;
            } elseif (str_contains($cat, 'sarap') || str_contains($cat, 'roti') || str_contains($cat, 'bubur')) {
                $targetCanteen = 2;
            } elseif (str_contains($cat, 'siang') || str_contains($cat, 'geprek') || str_contains($cat, 'pecel')) {
                $targetCanteen = 7;
            } elseif (str_contains($cat, 'cemi') || str_contains($cat, 'cami') || str_contains($cat, 'gorengan') || str_contains($cat, 'snack')) {
                $targetCanteen = 4;
            } elseif (str_contains($cat, 'bakso') || str_contains($cat, 'mie') || str_contains($cat, 'soto')) {
                $targetCanteen = 5;
            } elseif (str_contains($cat, 'burger') || str_contains($cat, 'kebab') || str_contains($cat, 'western') || str_contains($cat, 'bento')) {
                $targetCanteen = 6;
            } else {
                $targetCanteen = 1;
            }

            if (empty($m->canteen_id) || $m->canteen_id < 1 || $m->canteen_id > 7) {
                $m->canteen_id = $targetCanteen;
                $m->merchant_name = "Kantin {$targetCanteen}";
                $m->save();
            }
        }

        // Default menu items for each canteen 1 to 7 if empty
        $defaultMenus = [
            1 => [
                ['name' => 'Nasi Kuning Komplit', 'category' => 'Makanan', 'price' => 12000, 'stock' => 50, 'calories' => 450, 'is_healthy' => true],
                ['name' => 'Nasi Uduk Spesial Semur', 'category' => 'Makanan', 'price' => 13000, 'stock' => 40, 'calories' => 480, 'is_healthy' => true],
                ['name' => 'Nasi Rames Sayur Lodeh', 'category' => 'Makanan', 'price' => 10000, 'stock' => 35, 'calories' => 380, 'is_healthy' => true],
            ],
            2 => [
                ['name' => 'Bubur Ayam Gurih', 'category' => 'Sarapan', 'price' => 10000, 'stock' => 45, 'calories' => 320, 'is_healthy' => true],
                ['name' => 'Roti Bakar Coklat Keju', 'category' => 'Sarapan', 'price' => 8000, 'stock' => 60, 'calories' => 280, 'is_healthy' => false],
                ['name' => 'Sandwich Telur Keju', 'category' => 'Sarapan', 'price' => 9000, 'stock' => 40, 'calories' => 310, 'is_healthy' => true],
            ],
            3 => [
                ['name' => 'Es Teh Manis Segar', 'category' => 'Minuman', 'price' => 4000, 'stock' => 100, 'calories' => 90, 'is_healthy' => false],
                ['name' => 'Jus Alpukat Kocok', 'category' => 'Minuman', 'price' => 10000, 'stock' => 35, 'calories' => 210, 'is_healthy' => true],
                ['name' => 'Es Jeruk Peras Asli', 'category' => 'Minuman', 'price' => 6000, 'stock' => 80, 'calories' => 110, 'is_healthy' => true],
                ['name' => 'Susu Coklat Dingin', 'category' => 'Minuman', 'price' => 7000, 'stock' => 50, 'calories' => 160, 'is_healthy' => true],
            ],
            4 => [
                ['name' => 'Risol Mayo Keju Lumer (2 pcs)', 'category' => 'Cemilan', 'price' => 6000, 'stock' => 70, 'calories' => 220, 'is_healthy' => false],
                ['name' => 'Pastel Sayur Telur Gurih', 'category' => 'Cemilan', 'price' => 5000, 'stock' => 50, 'calories' => 180, 'is_healthy' => false],
                ['name' => 'Tahu Bakso Goreng Renyah', 'category' => 'Cemilan', 'price' => 7000, 'stock' => 60, 'calories' => 210, 'is_healthy' => false],
                ['name' => 'Kentang Goreng Crispy', 'category' => 'Cemilan', 'price' => 8000, 'stock' => 40, 'calories' => 250, 'is_healthy' => false],
            ],
            5 => [
                ['name' => 'Bakso Sapi Urat Komplit', 'category' => 'Mie & Bakso', 'price' => 15000, 'stock' => 40, 'calories' => 420, 'is_healthy' => true],
                ['name' => 'Mie Ayam Pangsit Basah', 'category' => 'Mie & Bakso', 'price' => 12000, 'stock' => 50, 'calories' => 410, 'is_healthy' => true],
                ['name' => 'Soto Ayam Kuning & Nasi', 'category' => 'Mie & Bakso', 'price' => 14000, 'stock' => 35, 'calories' => 390, 'is_healthy' => true],
            ],
            6 => [
                ['name' => 'Burger Ayam Crispy Mayo', 'category' => 'Western', 'price' => 14000, 'stock' => 40, 'calories' => 440, 'is_healthy' => false],
                ['name' => 'Kebab Daging Sapi Mini', 'category' => 'Western', 'price' => 12000, 'stock' => 45, 'calories' => 360, 'is_healthy' => false],
                ['name' => 'Chicken Bento Rice Box', 'category' => 'Western', 'price' => 16000, 'stock' => 30, 'calories' => 490, 'is_healthy' => true],
            ],
            7 => [
                ['name' => 'Ayam Geprek Sambal Bawang + Nasi', 'category' => 'Makan Siang', 'price' => 15000, 'stock' => 60, 'calories' => 520, 'is_healthy' => true],
                ['name' => 'Ayam Bakar Madu + Nasi', 'category' => 'Makan Siang', 'price' => 16000, 'stock' => 40, 'calories' => 480, 'is_healthy' => true],
                ['name' => 'Nasi Pecel Madiun Telur Ceplok', 'category' => 'Makan Siang', 'price' => 12000, 'stock' => 35, 'calories' => 370, 'is_healthy' => true],
            ],
        ];

        foreach ($defaultMenus as $cId => $items) {
            $count = Menu::where('canteen_id', $cId)->count();
            if ($count === 0) {
                foreach ($items as $it) {
                    Menu::create([
                        'name' => $it['name'],
                        'category' => $it['category'],
                        'price' => $it['price'],
                        'stock' => $it['stock'],
                        'calories' => $it['calories'],
                        'is_healthy' => $it['is_healthy'],
                        'canteen_id' => $cId,
                        'merchant_name' => "Kantin {$cId}",
                    ]);
                }
            }
        }
    }

    public static function backfillOrderCodes(): void
    {
        $orders = Order::whereNull('order_code')->orWhere('order_code', '')->get();
        foreach ($orders as $order) {
            // Determine canteen from items if possible
            $cId = $order->canteen_id;
            if (!$cId || $cId < 1 || $cId > 7) {
                $order->loadMissing('items.menu');
                $firstItem = $order->items->first();
                $cId = $firstItem?->menu?->canteen_id ?: 7;
                $order->canteen_id = $cId;
            }

            $order->order_code = "K{$cId}-" . str_pad($order->id, 3, '0', STR_PAD_LEFT);
            if (empty($order->group_code)) {
                $order->group_code = "TRX-" . $order->created_at->format('Ymd') . "-" . str_pad($order->id, 4, '0', STR_PAD_LEFT);
            }
            $order->save();
        }
    }

    public static function generateNextOrderCode(int $canteenId): string
    {
        $canteenId = ($canteenId >= 1 && $canteenId <= 7) ? $canteenId : 1;
        $today = Carbon::today();
        
        // Count today's orders for this canteen
        $count = Order::where('canteen_id', $canteenId)
            ->whereDate('created_at', $today)
            ->count();

        $seq = $count + 1;
        return "K{$canteenId}-" . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }

    public static function seedSampleSalesHistory(): void
    {
        // Only seed if there are very few completed orders across canteen 1-7
        $completedCount = Order::where('status', 'completed')->count();
        if ($completedCount >= 30) {
            return;
        }

        $student = User::where('role', 'student')->first();
        if (!$student) {
            return;
        }

        $studentNames = ['Budi Santoso', 'Cecep Supriatna', 'Siti Rahmawati', 'Rian Hidayat', 'Ahmad Fauzi', 'Dewi Lestari', 'Fajar Ramadhan', 'Putri Ayu'];
        $canteens = self::getCanteens();

        // Generate orders for today, this week, this month, and past months
        $now = Carbon::now();

        // 1. Generate for today
        for ($cId = 1; $cId <= 7; $cId++) {
            $menus = Menu::where('canteen_id', $cId)->get();
            if ($menus->isEmpty()) continue;

            $numOrdersToday = rand(2, 5);
            for ($i = 1; $i <= $numOrdersToday; $i++) {
                $menu = $menus->random();
                $qty = rand(1, 3);
                $amount = $menu->price * $qty;
                $custName = $studentNames[array_rand($studentNames)];
                $time = $now->copy()->subHours(rand(1, 5))->subMinutes(rand(5, 50));

                $order = Order::create([
                    'user_id' => $student->id,
                    'customer_name' => $custName,
                    'canteen_id' => $cId,
                    'order_code' => "K{$cId}-" . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'group_code' => 'TRX-' . $time->format('Ymd') . '-' . str_pad(rand(100, 999), 4, '0', STR_PAD_LEFT),
                    'total_amount' => $amount,
                    'status' => 'completed',
                    'payment_method' => ['cash', 'qris', 'wallet'][rand(0, 2)],
                    'is_at_school' => 1,
                    'gps_distance' => rand(15, 80),
                    'gps_device' => 'HP Siswa (Android)',
                    'created_at' => $time,
                    'updated_at' => $time->copy()->addMinutes(10),
                ]);

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_id' => $menu->id,
                    'quantity' => $qty,
                    'price' => $menu->price,
                    'created_at' => $time,
                    'updated_at' => $time,
                ]);
            }

            // Also add 1 cooking or ready order for today so live queue has variety
            $menu = $menus->random();
            $qty = rand(1, 2);
            $orderCode = "K{$cId}-" . str_pad($numOrdersToday + 1, 3, '0', STR_PAD_LEFT);
            $pendingOrder = Order::create([
                'user_id' => $student->id,
                'customer_name' => $studentNames[array_rand($studentNames)],
                'canteen_id' => $cId,
                'order_code' => $orderCode,
                'group_code' => 'TRX-' . $now->format('Ymd') . '-' . str_pad(rand(100, 999), 4, '0', STR_PAD_LEFT),
                'total_amount' => $menu->price * $qty,
                'status' => ($cId % 2 === 1) ? 'ready' : 'cooking',
                'payment_method' => 'cash',
                'is_at_school' => 1,
                'gps_distance' => 25,
                'gps_device' => 'HP Siswa',
                'created_at' => $now->copy()->subMinutes(rand(5, 20)),
                'updated_at' => $now->copy()->subMinutes(rand(1, 5)),
            ]);

            OrderItem::create([
                'order_id' => $pendingOrder->id,
                'menu_id' => $menu->id,
                'quantity' => $qty,
                'price' => $menu->price,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 2. Generate historical orders for past 7 days, past 4 weeks, and previous months
        for ($daysAgo = 1; $daysAgo <= 28; $daysAgo++) {
            $pastDate = $now->copy()->subDays($daysAgo);
            // Skip Sunday if school closed
            if ($pastDate->isSunday()) continue;

            for ($cId = 1; $cId <= 7; $cId++) {
                $menus = Menu::where('canteen_id', $cId)->get();
                if ($menus->isEmpty()) continue;

                $numOrders = rand(2, 4);
                for ($k = 1; $k <= $numOrders; $k++) {
                    $menu = $menus->random();
                    $qty = rand(1, 2);
                    $amount = $menu->price * $qty;
                    $custName = $studentNames[array_rand($studentNames)];
                    $oTime = $pastDate->copy()->setHour(rand(8, 14))->setMinute(rand(0, 59));

                    $order = Order::create([
                        'user_id' => $student->id,
                        'customer_name' => $custName,
                        'canteen_id' => $cId,
                        'order_code' => "K{$cId}-" . str_pad($k, 3, '0', STR_PAD_LEFT),
                        'group_code' => 'TRX-' . $oTime->format('Ymd') . '-' . str_pad(rand(100, 999), 4, '0', STR_PAD_LEFT),
                        'total_amount' => $amount,
                        'status' => 'completed',
                        'payment_method' => ['cash', 'qris', 'wallet'][rand(0, 2)],
                        'is_at_school' => 1,
                        'gps_distance' => rand(10, 60),
                        'gps_device' => 'HP Siswa',
                        'created_at' => $oTime,
                        'updated_at' => $oTime->copy()->addMinutes(12),
                    ]);

                    OrderItem::create([
                        'order_id' => $order->id,
                        'menu_id' => $menu->id,
                        'quantity' => $qty,
                        'price' => $menu->price,
                        'created_at' => $oTime,
                        'updated_at' => $oTime,
                    ]);
                }
            }
        }
    }

    public static function getSalesRecap(?int $canteenId = null): array
    {
        self::initDatabase();

        $canteenQuery = function($q) use ($canteenId) {
            if ($canteenId && $canteenId >= 1 && $canteenId <= 7) {
                $q->where('canteen_id', $canteenId);
            }
        };

        $now = Carbon::now();
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();
        $startOfYear = Carbon::now()->startOfYear();

        // 1. KPI Omset Hari Ini
        $todayRevenue = Order::where('status', 'completed')
            ->whereDate('created_at', $today)
            ->where($canteenQuery)
            ->sum('total_amount');
        $todayOrders = Order::where('status', 'completed')
            ->whereDate('created_at', $today)
            ->where($canteenQuery)
            ->count();
        $todayPortions = OrderItem::whereHas('order', function($q) use ($canteenQuery, $today) {
            $q->where('status', 'completed')->whereDate('created_at', $today)->where($canteenQuery);
        })->sum('quantity');

        // 2. KPI Omset Minggu Ini
        $weekRevenue = Order::where('status', 'completed')
            ->where('created_at', '>=', $startOfWeek)
            ->where($canteenQuery)
            ->sum('total_amount');
        $weekOrders = Order::where('status', 'completed')
            ->where('created_at', '>=', $startOfWeek)
            ->where($canteenQuery)
            ->count();
        $weekPortions = OrderItem::whereHas('order', function($q) use ($canteenQuery, $startOfWeek) {
            $q->where('status', 'completed')->where('created_at', '>=', $startOfWeek)->where($canteenQuery);
        })->sum('quantity');

        // 3. KPI Omset Bulan Ini
        $monthRevenue = Order::where('status', 'completed')
            ->where('created_at', '>=', $startOfMonth)
            ->where($canteenQuery)
            ->sum('total_amount');
        $monthOrders = Order::where('status', 'completed')
            ->where('created_at', '>=', $startOfMonth)
            ->where($canteenQuery)
            ->count();
        $monthPortions = OrderItem::whereHas('order', function($q) use ($canteenQuery, $startOfMonth) {
            $q->where('status', 'completed')->where('created_at', '>=', $startOfMonth)->where($canteenQuery);
        })->sum('quantity');

        // 4. KPI Omset Tahun Ini
        $yearRevenue = Order::where('status', 'completed')
            ->where('created_at', '>=', $startOfYear)
            ->where($canteenQuery)
            ->sum('total_amount');
        $yearOrders = Order::where('status', 'completed')
            ->where('created_at', '>=', $startOfYear)
            ->where($canteenQuery)
            ->count();
        $yearPortions = OrderItem::whereHas('order', function($q) use ($canteenQuery, $startOfYear) {
            $q->where('status', 'completed')->where('created_at', '>=', $startOfYear)->where($canteenQuery);
        })->sum('quantity');

        // Rekap Per Hari (30 Hari Terakhir)
        $dailyRecap = [];
        for ($i = 0; $i < 30; $i++) {
            $d = $now->copy()->subDays($i);
            $dStr = $d->toDateString();
            $rev = Order::where('status', 'completed')
                ->whereDate('created_at', $dStr)
                ->where($canteenQuery)
                ->sum('total_amount');
            $cnt = Order::where('status', 'completed')
                ->whereDate('created_at', $dStr)
                ->where($canteenQuery)
                ->count();
            $portions = OrderItem::whereHas('order', function($q) use ($canteenQuery, $dStr) {
                $q->where('status', 'completed')->whereDate('created_at', $dStr)->where($canteenQuery);
            })->sum('quantity');

            if ($cnt > 0 || $i < 7) {
                $dailyRecap[] = [
                    'date' => $dStr,
                    'date_formatted' => $d->isoFormat('D MMMM Y'),
                    'day_name' => $d->isoFormat('dddd'),
                    'orders_count' => (int)$cnt,
                    'portions_count' => (int)$portions,
                    'revenue' => (int)$rev,
                    'avg_order' => $cnt > 0 ? (int)round($rev / $cnt) : 0,
                ];
            }
        }

        // Rekap Per Minggu (12 Minggu Terakhir)
        $weeklyRecap = [];
        for ($w = 0; $w < 12; $w++) {
            $wStart = $now->copy()->subWeeks($w)->startOfWeek();
            $wEnd = $wStart->copy()->endOfWeek();
            $rev = Order::where('status', 'completed')
                ->whereBetween('created_at', [$wStart->toDateTimeString(), $wEnd->toDateTimeString()])
                ->where($canteenQuery)
                ->sum('total_amount');
            $cnt = Order::where('status', 'completed')
                ->whereBetween('created_at', [$wStart->toDateTimeString(), $wEnd->toDateTimeString()])
                ->where($canteenQuery)
                ->count();
            $portions = OrderItem::whereHas('order', function($q) use ($canteenQuery, $wStart, $wEnd) {
                $q->where('status', 'completed')
                  ->whereBetween('created_at', [$wStart->toDateTimeString(), $wEnd->toDateTimeString()])
                  ->where($canteenQuery);
            })->sum('quantity');

            if ($cnt > 0 || $w < 4) {
                $weeklyRecap[] = [
                    'week_num' => $wStart->weekOfYear,
                    'week_label' => "Minggu ke-{$wStart->weekOfYear} ({$wStart->isoFormat('D MMM')} - {$wEnd->isoFormat('D MMM Y')})",
                    'start_date' => $wStart->toDateString(),
                    'end_date' => $wEnd->toDateString(),
                    'orders_count' => (int)$cnt,
                    'portions_count' => (int)$portions,
                    'revenue' => (int)$rev,
                    'avg_order' => $cnt > 0 ? (int)round($rev / $cnt) : 0,
                ];
            }
        }

        // Rekap Per Bulan (Tahun Berjalan & Tahun Lalu)
        $monthlyRecap = [];
        for ($m = 0; $m < 12; $m++) {
            $mStart = $now->copy()->subMonths($m)->startOfMonth();
            $mEnd = $mStart->copy()->endOfMonth();
            $rev = Order::where('status', 'completed')
                ->whereBetween('created_at', [$mStart->toDateTimeString(), $mEnd->toDateTimeString()])
                ->where($canteenQuery)
                ->sum('total_amount');
            $cnt = Order::where('status', 'completed')
                ->whereBetween('created_at', [$mStart->toDateTimeString(), $mEnd->toDateTimeString()])
                ->where($canteenQuery)
                ->count();
            $portions = OrderItem::whereHas('order', function($q) use ($canteenQuery, $mStart, $mEnd) {
                $q->where('status', 'completed')
                  ->whereBetween('created_at', [$mStart->toDateTimeString(), $mEnd->toDateTimeString()])
                  ->where($canteenQuery);
            })->sum('quantity');

            if ($cnt > 0 || $m < 3) {
                $monthlyRecap[] = [
                    'month_label' => $mStart->isoFormat('MMMM Y'),
                    'year' => $mStart->year,
                    'month_number' => $mStart->month,
                    'orders_count' => (int)$cnt,
                    'portions_count' => (int)$portions,
                    'revenue' => (int)$rev,
                    'avg_order' => $cnt > 0 ? (int)round($rev / $cnt) : 0,
                ];
            }
        }

        // Rekap Per Tahun
        $yearlyRecap = [];
        $currentYear = $now->year;
        for ($y = $currentYear; $y >= $currentYear - 2; $y--) {
            $yStart = Carbon::create($y, 1, 1, 0, 0, 0);
            $yEnd = Carbon::create($y, 12, 31, 23, 59, 59);
            $rev = Order::where('status', 'completed')
                ->whereBetween('created_at', [$yStart->toDateTimeString(), $yEnd->toDateTimeString()])
                ->where($canteenQuery)
                ->sum('total_amount');
            $cnt = Order::where('status', 'completed')
                ->whereBetween('created_at', [$yStart->toDateTimeString(), $yEnd->toDateTimeString()])
                ->where($canteenQuery)
                ->count();
            $portions = OrderItem::whereHas('order', function($q) use ($canteenQuery, $yStart, $yEnd) {
                $q->where('status', 'completed')
                  ->whereBetween('created_at', [$yStart->toDateTimeString(), $yEnd->toDateTimeString()])
                  ->where($canteenQuery);
            })->sum('quantity');

            if ($cnt > 0 || $y === $currentYear) {
                $yearlyRecap[] = [
                    'year' => (string)$y,
                    'year_label' => "Tahun {$y}",
                    'orders_count' => (int)$cnt,
                    'portions_count' => (int)$portions,
                    'revenue' => (int)$rev,
                    'avg_order' => $cnt > 0 ? (int)round($rev / $cnt) : 0,
                ];
            }
        }

        // Recent 100 Transactions for Table/Export
        $recentOrders = Order::with(['items.menu', 'user'])
            ->where($canteenQuery)
            ->orderBy('created_at', 'desc')
            ->limit(150)
            ->get()
            ->map(function($o) {
                $itemNames = $o->items->map(function($it) {
                    return ($it->menu ? $it->menu->name : 'Menu') . " (" . $it->quantity . "x)";
                })->implode(', ');

                $totalQty = $o->items->sum('quantity');

                return [
                    'id' => $o->id,
                    'order_code' => $o->order_code ?: ('K' . ($o->canteen_id ?: 1) . '-' . str_pad($o->id, 3, '0', STR_PAD_LEFT)),
                    'canteen_id' => $o->canteen_id ?: 1,
                    'canteen_name' => 'Kantin ' . ($o->canteen_id ?: 1),
                    'customer_name' => $o->customer_name ?: ($o->user?->name ?: 'Siswa'),
                    'total_amount' => $o->total_amount,
                    'portions' => $totalQty,
                    'items_summary' => $itemNames ?: 'Item pesanan',
                    'payment_method' => strtoupper($o->payment_method ?: 'CASH'),
                    'status' => $o->status,
                    'is_at_school' => !empty($o->is_at_school),
                    'created_at' => $o->created_at->format('Y-m-d H:i:s'),
                    'date_formatted' => $o->created_at->format('d/m/Y H:i'),
                ];
            });

        return [
            'canteen_id' => $canteenId,
            'canteen_info' => self::getCanteenInfo($canteenId),
            'summary' => [
                'today' => [
                    'revenue' => (int)$todayRevenue,
                    'orders' => (int)$todayOrders,
                    'portions' => (int)$todayPortions,
                ],
                'week' => [
                    'revenue' => (int)$weekRevenue,
                    'orders' => (int)$weekOrders,
                    'portions' => (int)$weekPortions,
                ],
                'month' => [
                    'revenue' => (int)$monthRevenue,
                    'orders' => (int)$monthOrders,
                    'portions' => (int)$monthPortions,
                ],
                'year' => [
                    'revenue' => (int)$yearRevenue,
                    'orders' => (int)$yearOrders,
                    'portions' => (int)$yearPortions,
                ],
            ],
            'daily' => $dailyRecap,
            'weekly' => $weeklyRecap,
            'monthly' => $monthlyRecap,
            'yearly' => $yearlyRecap,
            'transactions' => $recentOrders,
        ];
    }
}
