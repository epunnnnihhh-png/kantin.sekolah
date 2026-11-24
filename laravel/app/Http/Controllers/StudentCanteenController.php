<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CanteenManager;
use Carbon\Carbon;

class StudentCanteenController extends Controller
{
    public function __construct()
    {
        CanteenManager::initDatabase();
    }

    public function apiData(Request $request)
    {
        CanteenManager::initDatabase();
        $menus = Menu::orderBy('canteen_id')->orderBy('name')->get();

        $settingsPath = storage_path('app/settings.json');
        $settings = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [
            'promo_image' => '',
            'promo_status' => '',
            'qris_image' => '',
            'bank_account' => 'BCA 1234567890 a.n Kantin Pintar'
        ];

        // Ambil user yang sedang aktif
        $user = Auth::user();
        if (!$user && session()->has('user_id')) {
            $user = User::find(session('user_id'));
        }
        if (!$user && $request->has('user_id')) {
            $user = User::find($request->input('user_id'));
        }
        if (!$user) {
            $user = User::where('role', 'student')->first();
            if (!$user) {
                $user = User::firstOrCreate(
                    ['email' => 'siswa@kantin.com'],
                    [
                        'name' => 'Budi Santoso',
                        'username' => 'budi',
                        'password' => bcrypt('password'),
                        'role' => 'student',
                        'class_name' => 'XII RPL 1',
                        'balance' => 50000
                    ]
                );
            }
        }

        $orders = Order::with(['items.menu'])
            ->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('customer_name', $user->name);
            })
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return response()->json([
            'menus' => $menus,
            'orders' => $orders,
            'canteens_list' => array_values(CanteenManager::getCanteens()),
            'user' => $user->name,
            'username' => $user->username ?: $user->name,
            'user_id' => $user->id,
            'class_name' => $user->class_name ?? 'XII RPL 1',
            'balance' => $user->balance ?? 50000,
            'is_present' => !empty($user->is_present),
            'attendance_date' => $user->attendance_date,
            'settings' => $settings
        ]);
    }

    public function createOrder(Request $request)
    {
        CanteenManager::initDatabase();

        $user = Auth::user();
        if (!$user && session()->has('user_id')) {
            $user = User::find(session('user_id'));
        }
        if (!$user && $request->has('user_id')) {
            $user = User::find($request->input('user_id'));
        }
        if (!$user) {
            $customerName = trim($request->input('customer_name', '')) ?: 'Budi Santoso';
            $user = User::firstOrCreate(
                ['name' => $customerName],
                [
                    'email' => 'siswa' . rand(100,999) . '@kantin.com',
                    'username' => 'siswa' . rand(100,999),
                    'password' => bcrypt('password'),
                    'role' => 'student',
                    'class_name' => 'XII RPL 1',
                    'balance' => 50000
                ]
            );
        }

        $items = $request->input('items', []);
        if (empty($items)) {
            return response()->json(['error' => 'Keranjang pesanan masih kosong'], 400);
        }

        // Group items by canteen_id
        $itemsByCanteen = [];
        foreach ($items as $item) {
            $menu = Menu::find($item['id']);
            if (!$menu) continue;

            $cId = $menu->canteen_id ?: 1;
            if ($cId < 1 || $cId > 7) $cId = 1;

            if (!isset($itemsByCanteen[$cId])) {
                $itemsByCanteen[$cId] = [];
            }

            $itemsByCanteen[$cId][] = [
                'menu' => $menu,
                'quantity' => (int)$item['quantity'],
                'price' => $menu->price,
                'subtotal' => $menu->price * (int)$item['quantity']
            ];
        }

        if (empty($itemsByCanteen)) {
            return response()->json(['error' => 'Menu yang dipilih tidak valid'], 400);
        }

        $pm = strtolower($request->input('payment_method', 'cash'));
        if (!in_array($pm, ['cash', 'qris', 'wallet', 'transfer'])) {
            $pm = 'cash';
        }

        $customerName = trim($request->input('customer_name', '')) ?: $user->name;
        $groupCode = 'TRX-' . Carbon::now()->format('Ymd-His') . '-' . rand(100, 999);
        $createdOrders = [];
        $createdCodes = [];

        // For each canteen, create an isolated Order with its unique canteen order_code (K1-001, K3-002, etc.)
        foreach ($itemsByCanteen as $cId => $canteenItems) {
            $subtotal = 0;
            foreach ($canteenItems as $ci) {
                $subtotal += $ci['subtotal'];
            }

            $orderCode = CanteenManager::generateNextOrderCode((int)$cId);

            $orderAttributes = [
                'user_id' => $user->id,
                'customer_name' => $customerName,
                'canteen_id' => (int)$cId,
                'order_code' => $orderCode,
                'group_code' => $groupCode,
                'total_amount' => $subtotal,
                'status' => 'pending',
                'payment_method' => $pm,
                'notes' => $request->input('notes'),
            ];

            if ($request->has('is_at_school')) {
                $orderAttributes['is_at_school'] = $request->input('is_at_school') ? 1 : 0;
            }
            if ($request->has('gps_distance')) {
                $orderAttributes['gps_distance'] = floatval($request->input('gps_distance'));
            }
            if ($request->has('gps_device')) {
                $orderAttributes['gps_device'] = substr($request->input('gps_device'), 0, 250);
            }

            $order = Order::create($orderAttributes);

            foreach ($canteenItems as $ci) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_id' => $ci['menu']->id,
                    'quantity' => $ci['quantity'],
                    'price' => $ci['price']
                ]);

                if ($ci['menu']->stock !== null) {
                    $ci['menu']->stock = max(0, $ci['menu']->stock - $ci['quantity']);
                    $ci['menu']->save();
                }
            }

            $order->loadMissing('items.menu');
            $createdOrders[] = $order;
            $createdCodes[] = "Kantin {$cId}: {$orderCode}";
        }

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibuat! Nomor antrean Anda: ' . implode(', ', $createdCodes),
            'order' => $createdOrders[0] ?? null,
            'orders' => $createdOrders,
            'group_code' => $groupCode,
            'codes_summary' => implode(' • ', $createdCodes)
        ]);
    }
}
