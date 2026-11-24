<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminCanteenController;
use App\Http\Controllers\StudentCanteenController;
use App\Http\Controllers\GpsDetectionController;
use App\Models\Order;
use App\Models\Menu;
use App\Services\CanteenManager;
use Carbon\Carbon;

// SPA Entry Points
Route::get('/', function () {
    return view('spa');
})->name('home');
Route::get('/login', function () {
    return view('spa');
})->name('login');
Route::get('/login/admin', function () {
    return view('spa');
});
Route::get('/admin/dashboard', function () {
    return view('spa');
});
Route::get('/student/dashboard', function () {
    return view('spa');
});
Route::get('/gps-verify/{token?}', function () {
    return view('spa');
});
Route::get('/lokasi-siswa/{token?}', function () {
    return view('spa');
});

// Helper penentuan loket kantin 1 sampai 7
if (!function_exists('getKantinPickupStand')) {
    function getKantinPickupStand(?string $category, ?int $canteenId = null): string {
        if ($canteenId && $canteenId >= 1 && $canteenId <= 7) {
            $info = CanteenManager::getCanteenInfo($canteenId);
            return $info['name'];
        }

        $cat = strtolower(trim($category ?? ''));
        if (str_contains($cat, 'minum') || str_contains($cat, 'jus') || str_contains($cat, 'teh')) return 'Kantin 3';
        if (str_contains($cat, 'sarap') || str_contains($cat, 'roti') || str_contains($cat, 'bubur')) return 'Kantin 2';
        if (str_contains($cat, 'siang') || str_contains($cat, 'geprek') || str_contains($cat, 'pecel')) return 'Kantin 7';
        if (str_contains($cat, 'cemi') || str_contains($cat, 'cami') || str_contains($cat, 'gorengan')) return 'Kantin 4';
        if (str_contains($cat, 'bakso') || str_contains($cat, 'mie') || str_contains($cat, 'soto')) return 'Kantin 5';
        if (str_contains($cat, 'burger') || str_contains($cat, 'kebab') || str_contains($cat, 'bento')) return 'Kantin 6';
        return 'Kantin 1';
    }
}

if (!function_exists('formatOrderPickupGuide')) {
    function formatOrderPickupGuide($order): string {
        if (!$order) {
            return 'Stand Kantin 1 s/d 7 SMKN 1 Ciomas';
        }

        $cId = $order->canteen_id;
        if ($cId && $cId >= 1 && $cId <= 7) {
            $info = CanteenManager::getCanteenInfo($cId);
            return "Ambil pesanan di Loket {$info['name']} ({$info['tagline']})";
        }

        $found = [];
        if ($order->items) {
            foreach ($order->items as $item) {
                $itemCId = $item->menu?->canteen_id ?: 1;
                $stand = getKantinPickupStand($item->menu?->category, $itemCId);
                $found[$stand] = "Ambil di {$stand}";
            }
        }
        if (!empty($found)) {
            return implode(' • ', array_values($found));
        }
        return 'Ambil di Loket Kantin SMKN 1 Ciomas';
    }
}

// API Routes
Route::get('/api/settings', function() {
    CanteenManager::initDatabase();

    $path = storage_path('app/settings.json');
    $settings = file_exists($path) ? json_decode(file_get_contents($path), true) : [
        'promo_image' => '',
        'promo_status' => null,
        'qris_image' => '',
        'bank_account' => 'BCA 1234567890 a.n Kantin Pintar'
    ];

    $canteens = CanteenManager::getCanteens();

    // Summary queue counts per canteen 1 to 7
    $canteenQueues = [];
    foreach ($canteens as $cId => $cData) {
        $readyOrders = Order::where('canteen_id', $cId)->where('status', 'ready')->orderBy('updated_at', 'desc')->get();
        $cookingOrders = Order::where('canteen_id', $cId)->where('status', 'cooking')->orderBy('updated_at', 'desc')->get();
        $pendingOrders = Order::where('canteen_id', $cId)->where('status', 'pending')->orderBy('created_at', 'desc')->get();

        $canteenQueues[] = [
            'canteen_id' => $cId,
            'name' => $cData['name'],
            'title' => $cData['title'],
            'tagline' => $cData['tagline'],
            'icon' => $cData['icon'],
            'code_prefix' => $cData['code_prefix'],
            'ready_count' => $readyOrders->count(),
            'cooking_count' => $cookingOrders->count(),
            'pending_count' => $pendingOrders->count(),
            'total_active' => $readyOrders->count() + $cookingOrders->count() + $pendingOrders->count(),
            'ready_codes' => $readyOrders->pluck('order_code')->filter()->values()->all(),
            'cooking_codes' => $cookingOrders->pluck('order_code')->filter()->values()->all(),
            'latest_ready' => $readyOrders->first()?->order_code,
        ];
    }

    // Top active orders for the live ticker/carousel
    $activeOrders = Order::with('items.menu')
        ->whereIn('status', ['ready', 'cooking', 'pending'])
        ->orderByRaw("CASE WHEN status = 'ready' THEN 1 WHEN status = 'cooking' THEN 2 ELSE 3 END")
        ->orderBy('updated_at', 'desc')
        ->limit(20)
        ->get()
        ->map(function($o) {
            $cId = $o->canteen_id ?: 1;
            $cInfo = CanteenManager::getCanteenInfo($cId);
            $code = $o->order_code ?: ("K{$cId}-" . str_pad($o->id, 3, '0', STR_PAD_LEFT));
            return [
                'id' => $o->id,
                'order_code' => $code,
                'canteen_id' => $cId,
                'canteen_name' => $cInfo['name'],
                'canteen_title' => $cInfo['title'],
                'canteen_icon' => $cInfo['icon'],
                'customer_name' => $o->customer_name ?: 'Siswa',
                'status' => $o->status,
                'status_badge' => $o->status === 'ready' ? 'Siap Diambil' : ($o->status === 'cooking' ? 'Sedang Dimasak' : 'Dalam Antrean'),
                'portions' => $o->items->sum('quantity'),
                'total_amount' => $o->total_amount,
                'updated_time' => $o->updated_at->format('H:i'),
                'instruction' => "Ambil di Loket {$cInfo['name']}"
            ];
        });

    $activeCount = Order::whereIn('status', ['pending', 'cooking', 'ready'])->count();
    $readyCount = Order::where('status', 'ready')->count();

    // Auto status text
    $latestReady = Order::where('status', 'ready')->orderBy('updated_at', 'desc')->first();
    if ($latestReady) {
        $cId = $latestReady->canteen_id ?: 1;
        $cInfo = CanteenManager::getCanteenInfo($cId);
        $code = $latestReady->order_code ?: ("K{$cId}-" . str_pad($latestReady->id, 3, '0', STR_PAD_LEFT));
        $autoStatus = "Antrean {$code} Siap Diambil di Loket {$cInfo['name']}!";
        $autoSubtext = "Atas nama " . ($latestReady->customer_name ?: 'Siswa') . " • Ambil di Loket {$cInfo['name']}";
        $badge = "Siap Diambil";
        $type = "ready";
        $queueNumber = $code;
    } else {
        $autoStatus = "Loket Kantin 1 s/d 7 Buka • Siap Melayani Pesanan";
        $autoSubtext = "Pesan praktis tanpa desak-desakan, ambil langsung di loket kantin favoritmu";
        $badge = "Loket Buka";
        $type = "idle";
        $queueNumber = null;
    }

    $settings['auto_status'] = $autoStatus;
    $settings['auto_subtext'] = $autoSubtext;
    $settings['status_badge'] = $badge;
    $settings['status_type'] = $type;
    $settings['queue_number'] = $queueNumber;
    $settings['active_queue_count'] = $activeCount;
    $settings['ready_queue_count'] = $readyCount;
    $settings['canteen_queues'] = $canteenQueues;
    $settings['active_orders'] = $activeOrders;
    $settings['canteens_list'] = array_values($canteens);
    $settings['pickup_stations'] = array_map(function($c) {
        return [
            'category' => $c['category_default'],
            'stand' => $c['name'],
            'title' => $c['title'],
            'icon' => $c['icon'],
            'instruction' => "Ambil di {$c['name']} ({$c['tagline']})"
        ];
    }, array_values($canteens));

    if (empty($settings['promo_status'])) {
        $settings['promo_status'] = $autoStatus;
    }

    return response()->json($settings);
});

// Format detail pesanan untuk Cek Status Pesanan
if (!function_exists('formatOrderResponsePayload')) {
    function formatOrderResponsePayload($order) {
        $cId = $order->canteen_id ?: 1;
        $cInfo = CanteenManager::getCanteenInfo($cId);
        $q = $order->order_code ?: ("K{$cId}-" . str_pad($order->id, 3, '0', STR_PAD_LEFT));

        $step = 1;
        if ($order->status === 'cooking') $step = 2;
        elseif ($order->status === 'ready') $step = 3;
        elseif ($order->status === 'completed') $step = 4;

        $pickupGuide = formatOrderPickupGuide($order);

        $statusMap = [
            'pending' => [
                'status_text' => "Antrean {$q} ({$cInfo['name']}) Menunggu Diproses",
                'subtext' => "Pesanan menunggu antrean di {$cInfo['name']}",
                'badge' => 'Dalam Antrean',
                'type' => 'pending',
                'instruction' => "Pesanan sedang dalam giliran. Lokasi pengambilan: {$cInfo['name']} ({$cInfo['tagline']})"
            ],
            'cooking' => [
                'status_text' => "Antrean {$q} ({$cInfo['name']}) Sedang Disiapkan! 🍳",
                'subtext' => "Staff {$cInfo['name']} sedang memasak / menyiapkan pesananmu",
                'badge' => 'Sedang Dimasak',
                'type' => 'cooking',
                'instruction' => "Makanan sedang diolah. Harap bersiap menuju: {$cInfo['name']}"
            ],
            'ready' => [
                'status_text' => "Antrean {$q} ({$cInfo['name']}) SIAP DIAMBIL! 🔔",
                'subtext' => "Silakan menuju Loket {$cInfo['name']} sekarang",
                'badge' => 'Siap Diambil',
                'type' => 'ready',
                'instruction' => "Segera tunjukkan kode {$q} di Loket {$cInfo['name']}"
            ],
            'completed' => [
                'status_text' => "Antrean {$q} ({$cInfo['name']}) Selesai Diambil ✅",
                'subtext' => "Pesanan telah diserahkan di Loket {$cInfo['name']}",
                'badge' => 'Selesai',
                'type' => 'completed',
                'instruction' => "Terima kasih telah berbelanja di {$cInfo['name']} SMKN 1 Ciomas!"
            ]
        ];

        $info = $statusMap[$order->status] ?? [
            'status_text' => "Antrean {$q} status: {$order->status}",
            'subtext' => $pickupGuide,
            'badge' => ucfirst($order->status),
            'type' => 'idle',
            'instruction' => 'Silakan konfirmasi ke staff: ' . $pickupGuide
        ];

        $items = $order->items->map(function($item) use ($cInfo, $cId) {
            $cat = $item->menu ? $item->menu->category : 'Umum';
            return [
                'id' => $item->id,
                'name' => $item->menu ? $item->menu->name : 'Menu Kantin',
                'category' => $cat,
                'canteen_id' => $cId,
                'pickup_stand' => $cInfo['name'],
                'pickup_location' => $cInfo['name'],
                'quantity' => $item->quantity,
                'price' => $item->price,
                'subtotal' => $item->quantity * $item->price
            ];
        });

        // Check if there are sister orders in the same checkout session (group_code)
        $relatedOrders = [];
        if (!empty($order->group_code)) {
            $relatedOrders = Order::with('items.menu')
                ->where('group_code', $order->group_code)
                ->where('id', '!=', $order->id)
                ->get()
                ->map(function($ro) {
                    $rCId = $ro->canteen_id ?: 1;
                    $rCInfo = CanteenManager::getCanteenInfo($rCId);
                    $rCode = $ro->order_code ?: ("K{$rCId}-" . str_pad($ro->id, 3, '0', STR_PAD_LEFT));
                    return [
                        'order_id' => $ro->id,
                        'order_code' => $rCode,
                        'canteen_id' => $rCId,
                        'canteen_name' => $rCInfo['name'],
                        'canteen_icon' => $rCInfo['icon'],
                        'status' => $ro->status,
                        'total_amount' => $ro->total_amount,
                        'items_summary' => $ro->items->map(fn($it) => ($it->menu?->name ?: 'Item') . " x{$it->quantity}")->implode(', ')
                    ];
                });
        }

        return array_merge([
            'found' => true,
            'order_id' => $order->id,
            'order_code' => $q,
            'queue' => $q,
            'canteen_id' => $cId,
            'canteen_name' => $cInfo['name'],
            'canteen_icon' => $cInfo['icon'],
            'canteen_info' => $cInfo,
            'group_code' => $order->group_code,
            'customer_name' => $order->customer_name ?: 'Siswa',
            'status' => $order->status,
            'step' => $step,
            'payment_method' => $order->payment_method ?: 'Cash',
            'total_amount' => $order->total_amount,
            'created_at' => $order->created_at->format('d M Y, H:i'),
            'pickup_guide' => $pickupGuide,
            'items' => $items,
            'related_orders' => $relatedOrders,
        ], $info);
    }
}

Route::get('/api/order-status/{id}', function($id) {
    CanteenManager::initDatabase();
    $order = null;

    if (is_numeric($id)) {
        $order = Order::with('items.menu')->find((int)$id);
    }
    if (!$order) {
        $order = Order::with('items.menu')->where('order_code', strtoupper(trim($id)))->first();
    }
    if (!$order) {
        return response()->json(['found' => false]);
    }
    return response()->json(formatOrderResponsePayload($order));
});

Route::get('/api/search-order', function(\Illuminate\Http\Request $request) {
    CanteenManager::initDatabase();
    $qInput = trim($request->input('q', ''));
    if (!$qInput) {
        return response()->json(['found' => false, 'message' => 'Silakan masukkan kode antrean (misal: K1-001) atau nama pemesan.']);
    }

    $upperInput = strtoupper($qInput);
    $order = null;

    // 1. Search by exact order_code (e.g. K1-001, K3-002, K7-005)
    $order = Order::with('items.menu')->where('order_code', $upperInput)->first();

    // 2. Search by order_code like (e.g. K1-1, K7)
    if (!$order && preg_match('/^K[1-7]/', $upperInput)) {
        $order = Order::with('items.menu')
            ->where('order_code', 'like', "{$upperInput}%")
            ->orderBy('id', 'desc')
            ->first();
    }

    // 3. Search by numeric ID
    if (!$order) {
        $cleanNumber = preg_replace('/[^0-9]/', '', $qInput);
        if (!empty($cleanNumber)) {
            $order = Order::with('items.menu')->find((int)$cleanNumber);
            if (!$order) {
                $order = Order::with('items.menu')
                    ->where('order_code', 'like', "%{$cleanNumber}")
                    ->orderBy('id', 'desc')
                    ->first();
            }
        }
    }

    // 4. Search by customer_name
    if (!$order) {
        $order = Order::with('items.menu')
            ->where('customer_name', 'like', "%{$qInput}%")
            ->orderBy('id', 'desc')
            ->first();
    }

    if (!$order) {
        return response()->json([
            'found' => false, 
            'message' => 'Pesanan dengan kode antrean atau nama "' . e($qInput) . '" tidak ditemukan. Silakan periksa kembali kode pesanan Anda (contoh: K1-001 s/d K7-005).'
        ]);
    }

    return response()->json(formatOrderResponsePayload($order));
});

// Live Queue Board API (Anti-Crash, Optimized for high order volume)
Route::get('/api/canteen-queues', function(\Illuminate\Http\Request $request) {
    CanteenManager::initDatabase();

    $canteenId = $request->filled('canteen_id') ? (int)$request->input('canteen_id') : null;

    $baseQuery = Order::with('items.menu');
    if ($canteenId && $canteenId >= 1 && $canteenId <= 7) {
        $baseQuery->where('canteen_id', $canteenId);
    }

    $ready = (clone $baseQuery)->where('status', 'ready')->orderBy('updated_at', 'desc')->limit(40)->get();
    $cooking = (clone $baseQuery)->where('status', 'cooking')->orderBy('updated_at', 'desc')->limit(40)->get();
    $pending = (clone $baseQuery)->where('status', 'pending')->orderBy('created_at', 'desc')->limit(40)->get();

    $formatQueueItem = function($o) {
        $cId = $o->canteen_id ?: 1;
        $cInfo = CanteenManager::getCanteenInfo($cId);
        $code = $o->order_code ?: ("K{$cId}-" . str_pad($o->id, 3, '0', STR_PAD_LEFT));
        return [
            'id' => $o->id,
            'order_code' => $code,
            'canteen_id' => $cId,
            'canteen_name' => $cInfo['name'],
            'canteen_icon' => $cInfo['icon'],
            'customer_name' => $o->customer_name ?: 'Siswa',
            'status' => $o->status,
            'portions' => $o->items->sum('quantity'),
            'total_amount' => $o->total_amount,
            'updated_time' => $o->updated_at->format('H:i'),
            'created_time' => $o->created_at->format('H:i'),
        ];
    };

    return response()->json([
        'canteen_id' => $canteenId,
        'ready' => $ready->map($formatQueueItem),
        'cooking' => $cooking->map($formatQueueItem),
        'pending' => $pending->map($formatQueueItem),
        'counts' => [
            'ready' => $ready->count(),
            'cooking' => $cooking->count(),
            'pending' => $pending->count(),
            'total' => $ready->count() + $cooking->count() + $pending->count(),
        ]
    ]);
});

// Auth Routes
Route::post('/api/login', [AuthController::class, 'loginApi']);
Route::post('/api/register', [AuthController::class, 'registerApi']);
Route::post('/api/logout', [AuthController::class, 'logoutApi']);

// Admin & Staff Canteen Routes
Route::get('/api/admin/data', [AdminCanteenController::class, 'apiData']);
Route::post('/api/admin/menu', [AdminCanteenController::class, 'addMenu']);
Route::post('/api/admin/menu/{id}', [AdminCanteenController::class, 'updateMenu']);
Route::delete('/api/admin/menu/{id}', [AdminCanteenController::class, 'deleteMenu']);
Route::post('/api/admin/order/{id}', [AdminCanteenController::class, 'updateOrder']);
Route::post('/api/admin/settings', [AdminCanteenController::class, 'updateSettings']);
Route::get('/api/admin/sales-recap', [AdminCanteenController::class, 'salesRecap']);
Route::get('/api/admin/export-sales', [AdminCanteenController::class, 'exportSales']);

// Student Routes
Route::get('/api/student/data', [StudentCanteenController::class, 'apiData']);
Route::post('/api/student/order', [StudentCanteenController::class, 'createOrder']);

// GPS Geofencing SMKN 1 Ciomas & Cross-Device Detection API
Route::get('/api/gps/config', [GpsDetectionController::class, 'getConfig']);
Route::post('/api/gps/create-session', [GpsDetectionController::class, 'createSession']);
Route::get('/api/gps/session/{token}', [GpsDetectionController::class, 'getSession']);
Route::post('/api/gps/verify', [GpsDetectionController::class, 'verifyLocation']);
Route::get('/api/gps/history', [GpsDetectionController::class, 'getHistory']);
Route::post('/api/gps/reset-session/{token}', [GpsDetectionController::class, 'resetSession']);
