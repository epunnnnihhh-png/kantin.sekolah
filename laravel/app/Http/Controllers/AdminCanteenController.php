<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CanteenManager;
use Carbon\Carbon;

class AdminCanteenController extends Controller
{
    private function getSettings() {
        $path = storage_path('app/settings.json');
        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true);
        }
        return [
            'promo_image' => '',
            'promo_status' => '',
            'qris_image' => '',
            'bank_account' => 'BCA 1234567890 a.n Kantin Pintar'
        ];
    }

    private function getAuthorizedUser() {
        if (!Auth::check()) {
            return null;
        }
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'canteen_staff'])) {
            return null;
        }
        return $user;
    }

    public function apiData(Request $request)
    {
        $user = $this->getAuthorizedUser();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        CanteenManager::initDatabase();

        $isSuperAdmin = ($user->role === 'admin' && empty($user->canteen_id));
        $activeCanteenId = null;

        if (!$isSuperAdmin) {
            $activeCanteenId = $user->canteen_id ?: 1;
        } elseif ($request->filled('canteen_id')) {
            $reqId = (int)$request->input('canteen_id');
            if ($reqId >= 1 && $reqId <= 7) {
                $activeCanteenId = $reqId;
            }
        }

        // Fetch menus
        $menuQuery = Menu::query();
        if ($activeCanteenId) {
            $menuQuery->where('canteen_id', $activeCanteenId);
        }
        $menus = $menuQuery->orderBy('canteen_id')->orderBy('name')->get();

        // Fetch orders
        $orderQuery = Order::with(['user', 'items.menu'])->orderBy('created_at', 'desc');
        if ($activeCanteenId) {
            $orderQuery->where('canteen_id', $activeCanteenId);
        }
        $orders = $orderQuery->limit(200)->get();

        // Calculate today's revenue and portions
        $todayRevQuery = Order::whereDate('created_at', Carbon::today())->where('status', 'completed');
        $portionsQuery = OrderItem::whereHas('order', function($q) {
            $q->whereDate('created_at', Carbon::today())->where('status', 'completed');
        });

        if ($activeCanteenId) {
            $todayRevQuery->where('canteen_id', $activeCanteenId);
            $portionsQuery->whereHas('order', function($q) use ($activeCanteenId) {
                $q->where('canteen_id', $activeCanteenId);
            });
        }

        $todayRevenue = $todayRevQuery->sum('total_amount');
        $portionsSold = $portionsQuery->sum('quantity');

        // Sales recap summary
        $recap = CanteenManager::getSalesRecap($activeCanteenId);

        return response()->json([
            'menus' => $menus,
            'orders' => $orders,
            'user' => $user->name,
            'username' => $user->username ?: $user->name,
            'role' => $user->role,
            'is_super_admin' => $isSuperAdmin,
            'canteen_id' => $activeCanteenId,
            'canteen_info' => CanteenManager::getCanteenInfo($activeCanteenId),
            'canteens_list' => array_values(CanteenManager::getCanteens()),
            'stats' => [
                'revenue' => $todayRevenue,
                'portions' => $portionsSold,
                'active_orders' => $orders->whereIn('status', ['pending', 'cooking', 'ready'])->count(),
                'ready_orders' => $orders->where('status', 'ready')->count(),
                'cooking_orders' => $orders->where('status', 'cooking')->count(),
                'pending_orders' => $orders->where('status', 'pending')->count(),
                'completed_orders' => $orders->where('status', 'completed')->count(),
            ],
            'recap' => $recap,
            'settings' => $this->getSettings()
        ]);
    }

    public function salesRecap(Request $request)
    {
        $user = $this->getAuthorizedUser();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        CanteenManager::initDatabase();

        $canteenId = ($user->role === 'canteen_staff' || !empty($user->canteen_id)) 
            ? ($user->canteen_id ?: 1) 
            : ($request->filled('canteen_id') ? (int)$request->input('canteen_id') : null);

        $recap = CanteenManager::getSalesRecap($canteenId);
        return response()->json([
            'success' => true,
            'data' => $recap
        ]);
    }

    public function exportSales(Request $request)
    {
        $user = $this->getAuthorizedUser();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        CanteenManager::initDatabase();

        $canteenId = ($user->role === 'canteen_staff' || !empty($user->canteen_id)) 
            ? ($user->canteen_id ?: 1) 
            : ($request->filled('canteen_id') ? (int)$request->input('canteen_id') : null);

        $period = $request->input('period', 'all'); // 'all', 'daily', 'weekly', 'monthly', 'yearly', 'transactions'
        $recap = CanteenManager::getSalesRecap($canteenId);

        $canteenName = $canteenId ? "Kantin {$canteenId}" : "Semua Kantin (Kantin 1 - 7)";
        $now = Carbon::now();
        $filename = "Rekap_Penjualan_" . str_replace(' ', '_', $canteenName) . "_" . $now->format('Ymd_His') . ".xls";

        // Build HTML Spreadsheet XML for Excel with high compatibility
        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        $html .= '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
        $html .= '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Rekap Penjualan</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
        $html .= '<style>';
        $html .= 'body { font-family: Arial, sans-serif; }';
        $html .= '.title { font-size: 16pt; font-weight: bold; color: #15803d; text-align: center; }';
        $html .= '.subtitle { font-size: 11pt; color: #475569; text-align: center; }';
        $html .= '.section-header { font-size: 12pt; font-weight: bold; background-color: #f1f5f9; padding: 8px; border: 1px solid #cbd5e1; }';
        $html .= 'table { border-collapse: collapse; width: 100%; margin-bottom: 25px; }';
        $html .= 'th { background-color: #16a34a; color: #ffffff; font-weight: bold; padding: 8px 10px; border: 1px solid #0f766e; text-align: left; }';
        $html .= 'td { padding: 6px 10px; border: 1px solid #e2e8f0; font-size: 10pt; }';
        $html .= '.text-center { text-align: center; }';
        $html .= '.text-right { text-align: right; }';
        $html .= '.font-bold { font-weight: bold; }';
        $html .= '.bg-highlight { background-color: #f0fdf4; }';
        $html .= '.total-row { background-color: #dcfce7; font-weight: bold; }';
        $html .= '</style></head><body>';

        // Title Header
        $html .= '<div style="margin-bottom: 20px;">';
        $html .= '<div class="title">LAPORAN REKAP DATA PENJUALAN E-KANTIN SEKOLAH</div>';
        $html .= '<div class="subtitle">SMKN 1 CIOMAS • Stand: ' . htmlspecialchars($canteenName) . '</div>';
        $html .= '<div class="subtitle">Tanggal Unduh: ' . $now->isoFormat('dddd, D MMMM Y - HH:mm') . ' WIB</div>';
        $html .= '</div>';

        // 1. Ringkasan Omset
        $html .= '<table>';
        $html .= '<tr><th colspan="4" style="background-color: #0f172a;">RINGKASAN OMSET KANTIN</th></tr>';
        $html .= '<tr class="font-bold bg-highlight">';
        $html .= '<td>Omset Hari Ini (' . $now->format('d/m/Y') . '):</td>';
        $html .= '<td class="text-right" style="color: #15803d; font-size: 12pt;">Rp ' . number_format($recap['summary']['today']['revenue'], 0, ',', '.') . ' (' . $recap['summary']['today']['orders'] . ' pesanan, ' . $recap['summary']['today']['portions'] . ' porsi)</td>';
        $html .= '<td>Omset Minggu Ini:</td>';
        $html .= '<td class="text-right" style="color: #1d4ed8; font-size: 12pt;">Rp ' . number_format($recap['summary']['week']['revenue'], 0, ',', '.') . ' (' . $recap['summary']['week']['orders'] . ' pesanan)</td>';
        $html .= '</tr>';
        $html .= '<tr class="font-bold bg-highlight">';
        $html .= '<td>Omset Bulan Ini (' . $now->isoFormat('MMMM Y') . '):</td>';
        $html .= '<td class="text-right" style="color: #7e22ce; font-size: 12pt;">Rp ' . number_format($recap['summary']['month']['revenue'], 0, ',', '.') . ' (' . $recap['summary']['month']['orders'] . ' pesanan)</td>';
        $html .= '<td>Omset Tahun Ini (' . $now->year . '):</td>';
        $html .= '<td class="text-right" style="color: #c2410c; font-size: 12pt;">Rp ' . number_format($recap['summary']['year']['revenue'], 0, ',', '.') . ' (' . $recap['summary']['year']['orders'] . ' pesanan)</td>';
        $html .= '</tr>';
        $html .= '</table>';

        // 2. Rekap Harian
        if (in_array($period, ['all', 'daily'])) {
            $html .= '<table>';
            $html .= '<tr><th colspan="6" style="background-color: #15803d;">REKAP PENJUALAN PER HARI (HARIAN)</th></tr>';
            $html .= '<tr style="background-color: #22c55e; color: white;">';
            $html .= '<th class="text-center">No</th><th>Tanggal</th><th>Hari</th><th class="text-center">Jumlah Transaksi</th><th class="text-center">Porsi Terjual</th><th class="text-right">Total Omset (Rp)</th>';
            $html .= '</tr>';
            $totalDailyRev = 0;
            $totalDailyOrders = 0;
            $totalDailyPortions = 0;
            $no = 1;
            foreach ($recap['daily'] as $d) {
                $totalDailyRev += $d['revenue'];
                $totalDailyOrders += $d['orders_count'];
                $totalDailyPortions += $d['portions_count'];
                $html .= '<tr>';
                $html .= '<td class="text-center">' . $no++ . '</td>';
                $html .= '<td>' . htmlspecialchars($d['date_formatted']) . '</td>';
                $html .= '<td>' . htmlspecialchars($d['day_name']) . '</td>';
                $html .= '<td class="text-center">' . $d['orders_count'] . '</td>';
                $html .= '<td class="text-center">' . $d['portions_count'] . '</td>';
                $html .= '<td class="text-right font-bold">Rp ' . number_format($d['revenue'], 0, ',', '.') . '</td>';
                $html .= '</tr>';
            }
            $html .= '<tr class="total-row">';
            $html .= '<td colspan="3" class="text-center">TOTAL REKAP HARIAN</td>';
            $html .= '<td class="text-center">' . $totalDailyOrders . '</td>';
            $html .= '<td class="text-center">' . $totalDailyPortions . '</td>';
            $html .= '<td class="text-right">Rp ' . number_format($totalDailyRev, 0, ',', '.') . '</td>';
            $html .= '</tr>';
            $html .= '</table>';
        }

        // 3. Rekap Mingguan
        if (in_array($period, ['all', 'weekly'])) {
            $html .= '<table>';
            $html .= '<tr><th colspan="5" style="background-color: #1d4ed8;">REKAP PENJUALAN PER MINGGU (MINGGUAN)</th></tr>';
            $html .= '<tr style="background-color: #3b82f6; color: white;">';
            $html .= '<th class="text-center">No</th><th>Periode Minggu</th><th class="text-center">Jumlah Transaksi</th><th class="text-center">Porsi Terjual</th><th class="text-right">Total Omset (Rp)</th>';
            $html .= '</tr>';
            $totalWRev = 0;
            $totalWOrders = 0;
            $totalWPortions = 0;
            $no = 1;
            foreach ($recap['weekly'] as $w) {
                $totalWRev += $w['revenue'];
                $totalWOrders += $w['orders_count'];
                $totalWPortions += $w['portions_count'];
                $html .= '<tr>';
                $html .= '<td class="text-center">' . $no++ . '</td>';
                $html .= '<td>' . htmlspecialchars($w['week_label']) . '</td>';
                $html .= '<td class="text-center">' . $w['orders_count'] . '</td>';
                $html .= '<td class="text-center">' . $w['portions_count'] . '</td>';
                $html .= '<td class="text-right font-bold">Rp ' . number_format($w['revenue'], 0, ',', '.') . '</td>';
                $html .= '</tr>';
            }
            $html .= '<tr class="total-row">';
            $html .= '<td colspan="2" class="text-center">TOTAL REKAP MINGGUAN</td>';
            $html .= '<td class="text-center">' . $totalWOrders . '</td>';
            $html .= '<td class="text-center">' . $totalWPortions . '</td>';
            $html .= '<td class="text-right">Rp ' . number_format($totalWRev, 0, ',', '.') . '</td>';
            $html .= '</tr>';
            $html .= '</table>';
        }

        // 4. Rekap Bulanan
        if (in_array($period, ['all', 'monthly'])) {
            $html .= '<table>';
            $html .= '<tr><th colspan="5" style="background-color: #7e22ce;">REKAP PENJUALAN PER BULAN (BULANAN)</th></tr>';
            $html .= '<tr style="background-color: #a855f7; color: white;">';
            $html .= '<th class="text-center">No</th><th>Bulan & Tahun</th><th class="text-center">Jumlah Transaksi</th><th class="text-center">Porsi Terjual</th><th class="text-right">Total Omset (Rp)</th>';
            $html .= '</tr>';
            $totalMRev = 0;
            $totalMOrders = 0;
            $totalMPortions = 0;
            $no = 1;
            foreach ($recap['monthly'] as $m) {
                $totalMRev += $m['revenue'];
                $totalMOrders += $m['orders_count'];
                $totalMPortions += $m['portions_count'];
                $html .= '<tr>';
                $html .= '<td class="text-center">' . $no++ . '</td>';
                $html .= '<td>' . htmlspecialchars($m['month_label']) . '</td>';
                $html .= '<td class="text-center">' . $m['orders_count'] . '</td>';
                $html .= '<td class="text-center">' . $m['portions_count'] . '</td>';
                $html .= '<td class="text-right font-bold">Rp ' . number_format($m['revenue'], 0, ',', '.') . '</td>';
                $html .= '</tr>';
            }
            $html .= '<tr class="total-row">';
            $html .= '<td colspan="2" class="text-center">TOTAL REKAP BULANAN</td>';
            $html .= '<td class="text-center">' . $totalMOrders . '</td>';
            $html .= '<td class="text-center">' . $totalMPortions . '</td>';
            $html .= '<td class="text-right">Rp ' . number_format($totalMRev, 0, ',', '.') . '</td>';
            $html .= '</tr>';
            $html .= '</table>';
        }

        // 5. Rekap Tahunan
        if (in_array($period, ['all', 'yearly'])) {
            $html .= '<table>';
            $html .= '<tr><th colspan="5" style="background-color: #c2410c;">REKAP PENJUALAN PER TAHUN (TAHUNAN)</th></tr>';
            $html .= '<tr style="background-color: #f97316; color: white;">';
            $html .= '<th class="text-center">No</th><th>Tahun</th><th class="text-center">Jumlah Transaksi</th><th class="text-center">Porsi Terjual</th><th class="text-right">Total Omset (Rp)</th>';
            $html .= '</tr>';
            $totalYRev = 0;
            $totalYOrders = 0;
            $totalYPortions = 0;
            $no = 1;
            foreach ($recap['yearly'] as $y) {
                $totalYRev += $y['revenue'];
                $totalYOrders += $y['orders_count'];
                $totalYPortions += $y['portions_count'];
                $html .= '<tr>';
                $html .= '<td class="text-center">' . $no++ . '</td>';
                $html .= '<td>' . htmlspecialchars($y['year_label']) . '</td>';
                $html .= '<td class="text-center">' . $y['orders_count'] . '</td>';
                $html .= '<td class="text-center">' . $y['portions_count'] . '</td>';
                $html .= '<td class="text-right font-bold">Rp ' . number_format($y['revenue'], 0, ',', '.') . '</td>';
                $html .= '</tr>';
            }
            $html .= '<tr class="total-row">';
            $html .= '<td colspan="2" class="text-center">TOTAL REKAP TAHUNAN</td>';
            $html .= '<td class="text-center">' . $totalYOrders . '</td>';
            $html .= '<td class="text-center">' . $totalYPortions . '</td>';
            $html .= '<td class="text-right">Rp ' . number_format($totalYRev, 0, ',', '.') . '</td>';
            $html .= '</tr>';
            $html .= '</table>';
        }

        // 6. Rincian Transaksi
        if (in_array($period, ['all', 'transactions'])) {
            $html .= '<table>';
            $html .= '<tr><th colspan="9" style="background-color: #0f172a;">RINCIAN DATA TRANSAKSI TERAKHIR</th></tr>';
            $html .= '<tr style="background-color: #334155; color: white;">';
            $html .= '<th class="text-center">No</th><th>Kode Antrean</th><th>Kantin</th><th>Waktu</th><th>Nama Siswa</th><th>Rincian Menu</th><th class="text-center">Bayar</th><th class="text-center">Status</th><th class="text-right">Total (Rp)</th>';
            $html .= '</tr>';
            $no = 1;
            foreach ($recap['transactions'] as $tx) {
                $html .= '<tr>';
                $html .= '<td class="text-center">' . $no++ . '</td>';
                $html .= '<td class="font-bold">' . htmlspecialchars($tx['order_code']) . '</td>';
                $html .= '<td>' . htmlspecialchars($tx['canteen_name']) . '</td>';
                $html .= '<td>' . htmlspecialchars($tx['date_formatted']) . '</td>';
                $html .= '<td class="font-bold">' . htmlspecialchars($tx['customer_name']) . '</td>';
                $html .= '<td>' . htmlspecialchars($tx['items_summary']) . '</td>';
                $html .= '<td class="text-center">' . htmlspecialchars($tx['payment_method']) . '</td>';
                $html .= '<td class="text-center">' . htmlspecialchars(ucfirst($tx['status'])) . '</td>';
                $html .= '<td class="text-right font-bold">Rp ' . number_format($tx['total_amount'], 0, ',', '.') . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
        }

        $html .= '</body></html>';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function addMenu(Request $request)
    {
        $user = $this->getAuthorizedUser();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'name' => 'required|string',
            'category' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'nullable|image',
            'stock' => 'nullable|numeric',
            'canteen_id' => 'nullable|integer'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('menus', 'public');
        }

        // Determine canteen id
        $canteenId = ($user->role === 'canteen_staff')
            ? ($user->canteen_id ?: 1)
            : ($request->input('canteen_id') ?: 1);

        $menu = Menu::create([
            'name' => $request->name,
            'category' => $request->category,
            'price' => $request->price,
            'image' => $imagePath,
            'stock' => $request->stock ?? 100,
            'canteen_id' => $canteenId,
            'merchant_name' => "Kantin {$canteenId}"
        ]);

        return response()->json(['success' => true, 'menu' => $menu]);
    }

    public function updateMenu(Request $request, $id)
    {
        $user = $this->getAuthorizedUser();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $menu = Menu::findOrFail($id);

        // Staff can only edit their own canteen's menu
        if ($user->role === 'canteen_staff' && $menu->canteen_id !== $user->canteen_id) {
            return response()->json(['error' => 'Anda tidak memiliki hak akses untuk mengedit menu kantin lain.'], 403);
        }

        $request->validate([
            'name' => 'required|string',
            'category' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'nullable|image',
            'stock' => 'nullable|numeric',
            'canteen_id' => 'nullable|integer'
        ]);

        if ($request->hasFile('image')) {
            $menu->image = $request->file('image')->store('menus', 'public');
        }

        $menu->name = $request->name;
        $menu->category = $request->category;
        $menu->price = $request->price;
        if ($request->has('stock') && $request->stock !== null) {
            $menu->stock = $request->stock;
        }

        if ($user->role === 'admin' && $request->filled('canteen_id')) {
            $menu->canteen_id = (int)$request->input('canteen_id');
            $menu->merchant_name = "Kantin " . $menu->canteen_id;
        }

        $menu->save();

        return response()->json(['success' => true, 'menu' => $menu]);
    }

    public function deleteMenu(Request $request, $id)
    {
        $user = $this->getAuthorizedUser();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $menu = Menu::findOrFail($id);

        if ($user->role === 'canteen_staff' && $menu->canteen_id !== $user->canteen_id) {
            return response()->json(['error' => 'Anda tidak memiliki hak akses untuk menghapus menu kantin lain.'], 403);
        }

        $menu->delete();

        return response()->json(['success' => true]);
    }

    public function updateOrder(Request $request, $id)
    {
        $user = $this->getAuthorizedUser();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $order = Order::findOrFail($id);

        if ($user->role === 'canteen_staff' && $order->canteen_id !== $user->canteen_id) {
            return response()->json(['error' => 'Pesanan ini bukan milik kantin Anda.'], 403);
        }
        
        if ($request->filled('status')) {
            $order->status = $request->status;
            $order->save();
        } else {
            $statuses = ['pending', 'cooking', 'ready', 'completed'];
            $currentIndex = array_search($order->status, $statuses);
            
            if ($currentIndex !== false && $currentIndex < count($statuses) - 1) {
                $order->status = $statuses[$currentIndex + 1];
                $order->save();
            }
        }

        return response()->json(['success' => true, 'order' => $order]);
    }

    public function updateSettings(Request $request)
    {
        $user = $this->getAuthorizedUser();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Hanya Super Admin yang dapat mengubah pengaturan global.'], 403);
        }

        $settings = $this->getSettings();
        
        if ($request->has('promo_status')) {
            $settings['promo_status'] = $request->promo_status;
        }
        if ($request->has('bank_account')) {
            $settings['bank_account'] = $request->bank_account;
        }
        
        if ($request->hasFile('promo_image')) {
            $settings['promo_image'] = $request->file('promo_image')->store('settings', 'public');
        }
        if ($request->hasFile('qris_image')) {
            $settings['qris_image'] = $request->file('qris_image')->store('settings', 'public');
        }

        file_put_contents(storage_path('app/settings.json'), json_encode($settings));
        return response()->json(['success' => true, 'settings' => $settings]);
    }
}
