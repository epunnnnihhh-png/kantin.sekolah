<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Order;

class GpsDetectionController extends Controller
{
    // Koordinat Resmi SMKN 1 Ciomas (Jl. Raya Laladon, Ciomas, Bogor)
    const SCHOOL_NAME = 'SMKN 1 Ciomas';
    const SCHOOL_LAT = -6.586000;
    const SCHOOL_LNG = 106.758500;
    const SCHOOL_ADDRESS = 'Jl. Raya Laladon, Desa Laladon, Kec. Ciomas, Kab. Bogor, Jawa Barat 16610';
    const ALLOWED_RADIUS_METERS = 350; // Radius geofence area sekolah (dalam meter)

    public function __construct()
    {
        $this->ensureSchema();
    }

    /**
     * Memastikan kolom dan tabel untuk GPS tersedia di database tanpa menimbulkan error
     */
    private function ensureSchema()
    {
        try {
            if (!Schema::hasTable('gps_sessions')) {
                Schema::create('gps_sessions', function ($table) {
                    $table->id();
                    $table->string('token')->unique();
                    $table->unsignedBigInteger('user_id')->nullable();
                    $table->string('student_name')->default('Siswa SMKN 1 Ciomas');
                    $table->string('status')->default('pending'); // pending, verified_inside, verified_outside
                    $table->boolean('is_inside_school')->default(false);
                    $table->double('latitude', 10, 7)->nullable();
                    $table->double('longitude', 10, 7)->nullable();
                    $table->float('accuracy')->nullable();
                    $table->float('distance_meters')->nullable();
                    $table->string('device_info')->nullable();
                    $table->string('client_ip')->nullable();
                    $table->timestamp('verified_at')->nullable();
                    $table->timestamps();
                });
            }

            if (!Schema::hasColumn('orders', 'is_at_school')) {
                DB::statement("ALTER TABLE orders ADD COLUMN is_at_school INTEGER DEFAULT 0");
            }
            if (!Schema::hasColumn('orders', 'gps_distance')) {
                DB::statement("ALTER TABLE orders ADD COLUMN gps_distance FLOAT DEFAULT NULL");
            }
            if (!Schema::hasColumn('orders', 'gps_device')) {
                DB::statement("ALTER TABLE orders ADD COLUMN gps_device VARCHAR(255) DEFAULT NULL");
            }

            if (!Schema::hasColumn('users', 'is_present')) {
                DB::statement("ALTER TABLE users ADD COLUMN is_present INTEGER DEFAULT 0");
            }
            if (!Schema::hasColumn('users', 'attendance_date')) {
                DB::statement("ALTER TABLE users ADD COLUMN attendance_date DATETIME DEFAULT NULL");
            }
        } catch (\Throwable $e) {
            // Silently fallback if table already exists or SQLite lock occurs
        }
    }

    /**
     * Menghitung jarak antara 2 titik koordinat menggunakan formula Haversine (hasil dalam meter)
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Radius bumi dalam meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadius * $c, 1);
    }

    /**
     * Mengambil konfigurasi GPS sekolah SMKN 1 Ciomas
     */
    public function getConfig()
    {
        return response()->json([
            'school_name' => self::SCHOOL_NAME,
            'latitude' => self::SCHOOL_LAT,
            'longitude' => self::SCHOOL_LNG,
            'allowed_radius_meters' => self::ALLOWED_RADIUS_METERS,
            'address' => self::SCHOOL_ADDRESS,
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * Mengambil atau menyimpan sesi GPS via fallback file JSON (sangat tahan banting)
     */
    private function getFileStoragePath()
    {
        return storage_path('app/gps_sessions.json');
    }

    private function readSessionsFromFile()
    {
        $path = $this->getFileStoragePath();
        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            return is_array($data) ? $data : [];
        }
        return [];
    }

    private function writeSessionsToFile(array $sessions)
    {
        try {
            $path = $this->getFileStoragePath();
            // Jaga ukuran file agar tetap ringan (maksimal 100 sesi terakhir)
            if (count($sessions) > 100) {
                $sessions = array_slice($sessions, -100, 100, true);
            }
            file_put_contents($path, json_encode($sessions, JSON_PRETTY_PRINT));
        } catch (\Throwable $e) {
            // Abaikan kesalahan penulisan jika permissions terkunci
        }
    }

    /**
     * Membuat sesi verifikasi lokasi baru untuk perangkat berbeda
     */
    public function createSession(Request $request)
    {
        $this->ensureSchema();
        $studentName = trim($request->input('student_name', 'Siswa SMKN 1 Ciomas'));
        $userId = $request->input('user_id');

        // Buat token unik 6 karakter acak yang mudah dibaca (contoh: CIP-7829)
        $randomHex = strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
        $token = 'GPS-' . rand(100, 999) . '-' . $randomHex;

        $sessionData = [
            'token' => $token,
            'user_id' => $userId,
            'student_name' => $studentName ?: 'Siswa SMKN 1 Ciomas',
            'status' => 'pending', // pending, verified_inside, verified_outside
            'is_inside_school' => false,
            'distance_meters' => null,
            'latitude' => null,
            'longitude' => null,
            'accuracy' => null,
            'device_info' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'verified_at' => null
        ];

        // Simpan ke database jika tabel tersedia
        try {
            DB::table('gps_sessions')->insert($sessionData);
        } catch (\Throwable $e) {
            // Fallback ke penyimpanan file jika terjadi kendala DB
        }

        // Selalu catat di penyimpanan file
        $fileSessions = $this->readSessionsFromFile();
        $fileSessions[$token] = $sessionData;
        $this->writeSessionsToFile($fileSessions);

        $baseUrl = url('/');
        $verifyUrl = "{$baseUrl}/gps-verify/{$token}";

        return response()->json([
            'success' => true,
            'token' => $token,
            'verify_url' => $verifyUrl,
            'student_name' => $studentName,
            'school_name' => self::SCHOOL_NAME,
            'created_at' => $sessionData['created_at'],
            'message' => 'Sesi verifikasi GPS perangkat berbeda berhasil dibuat'
        ]);
    }

    /**
     * Mendapatkan status sesi verifikasi GPS terkini (dipakai polling perangkat pertama)
     */
    public function getSession($token)
    {
        $this->ensureSchema();
        $session = null;

        try {
            $session = DB::table('gps_sessions')->where('token', $token)->first();
            if ($session) {
                $session = (array) $session;
            }
        } catch (\Throwable $e) {}

        if (!$session) {
            $fileSessions = $this->readSessionsFromFile();
            $session = $fileSessions[$token] ?? null;
        }

        if (!$session) {
            return response()->json([
                'found' => false,
                'message' => 'Sesi verifikasi tidak ditemukan atau telah kedaluwarsa'
            ], 404);
        }

        return response()->json(array_merge([
            'found' => true,
            'school_name' => self::SCHOOL_NAME,
            'school_latitude' => self::SCHOOL_LAT,
            'school_longitude' => self::SCHOOL_LNG,
            'allowed_radius_meters' => self::ALLOWED_RADIUS_METERS
        ], (array) $session));
    }

    /**
     * Memverifikasi koordinat GPS (dipanggil dari perangkat HP / perangkat berbeda / langsung)
     */
    public function verifyLocation(Request $request)
    {
        $this->ensureSchema();

        $token = $request->input('token');
        $lat = floatval($request->input('latitude'));
        $lng = floatval($request->input('longitude'));
        $accuracy = floatval($request->input('accuracy', 10));
        $deviceInfo = $request->input('device_info', $request->header('User-Agent', 'Perangkat Siswa'));
        $studentName = $request->input('student_name', 'Siswa');
        $userId = $request->input('user_id');

        if (!$lat || !$lng) {
            return response()->json([
                'success' => false,
                'message' => 'Koordinat GPS (latitude dan longitude) wajib disertakan'
            ], 400);
        }

        // Hitung jarak ke SMKN 1 Ciomas
        $distance = self::calculateDistance($lat, $lng, self::SCHOOL_LAT, self::SCHOOL_LNG);
        $isInside = $distance <= self::ALLOWED_RADIUS_METERS;
        $status = $isInside ? 'verified_inside' : 'verified_outside';
        $verifiedAt = date('Y-m-d H:i:s');

        // Update database jika ada sesi token
        if ($token) {
            try {
                DB::table('gps_sessions')->where('token', $token)->update([
                    'status' => $status,
                    'is_inside_school' => $isInside ? 1 : 0,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'accuracy' => $accuracy,
                    'distance_meters' => $distance,
                    'device_info' => $deviceInfo,
                    'client_ip' => $request->ip(),
                    'verified_at' => $verifiedAt,
                    'updated_at' => $verifiedAt
                ]);
            } catch (\Throwable $e) {}

            // Update file storage juga
            $fileSessions = $this->readSessionsFromFile();
            if (isset($fileSessions[$token])) {
                $fileSessions[$token]['status'] = $status;
                $fileSessions[$token]['is_inside_school'] = $isInside;
                $fileSessions[$token]['latitude'] = $lat;
                $fileSessions[$token]['longitude'] = $lng;
                $fileSessions[$token]['accuracy'] = $accuracy;
                $fileSessions[$token]['distance_meters'] = $distance;
                $fileSessions[$token]['device_info'] = $deviceInfo;
                $fileSessions[$token]['verified_at'] = $verifiedAt;
                $this->writeSessionsToFile($fileSessions);
            }
        }

        // Update status kehadiran siswa pada tabel User jika ada id atau nama
        try {
            if ($userId) {
                User::where('id', $userId)->update([
                    'is_present' => $isInside ? 1 : 0,
                    'attendance_date' => $verifiedAt
                ]);
            } elseif ($studentName) {
                User::where('name', $studentName)->update([
                    'is_present' => $isInside ? 1 : 0,
                    'attendance_date' => $verifiedAt
                ]);
            }
        } catch (\Throwable $e) {}

        // Catat ke log histori verifikasi
        $this->appendLog([
            'token' => $token,
            'student_name' => $studentName,
            'user_id' => $userId,
            'distance_meters' => $distance,
            'is_inside' => $isInside,
            'latitude' => $lat,
            'longitude' => $lng,
            'device_info' => $deviceInfo,
            'time' => $verifiedAt
        ]);

        $message = $isInside 
            ? "✅ Sukses Terdeteksi! Siswa berada di area SMKN 1 Ciomas (Jarak: {$distance} m dari titik pusat sekolah)."
            : "⚠️ Di Luar Area Sekolah. Jarak Anda {$distance} m dari SMKN 1 Ciomas (Batas area: " . self::ALLOWED_RADIUS_METERS . " m).";

        return response()->json([
            'success' => true,
            'is_inside_school' => $isInside,
            'status' => $status,
            'distance_meters' => $distance,
            'allowed_radius_meters' => self::ALLOWED_RADIUS_METERS,
            'school_name' => self::SCHOOL_NAME,
            'school_latitude' => self::SCHOOL_LAT,
            'school_longitude' => self::SCHOOL_LNG,
            'student_latitude' => $lat,
            'student_longitude' => $lng,
            'device_info' => $deviceInfo,
            'verified_at' => $verifiedAt,
            'message' => $message
        ]);
    }

    /**
     * Menyimpan catatan log verifikasi terbaru untuk monitoring admin
     */
    private function appendLog(array $entry)
    {
        try {
            $path = storage_path('app/gps_verifications_log.json');
            $logs = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
            if (!is_array($logs)) $logs = [];

            array_unshift($logs, $entry);
            if (count($logs) > 50) {
                $logs = array_slice($logs, 0, 50);
            }
            file_put_contents($path, json_encode($logs, JSON_PRETTY_PRINT));
        } catch (\Throwable $e) {}
    }

    /**
     * Mengambil daftar histori verifikasi untuk Admin Dashboard
     */
    public function getHistory()
    {
        $this->ensureSchema();
        $history = [];

        // Coba dari database
        try {
            $dbHistory = DB::table('gps_sessions')
                ->whereNotNull('verified_at')
                ->orderBy('verified_at', 'desc')
                ->limit(30)
                ->get();
            if ($dbHistory && count($dbHistory) > 0) {
                $history = $dbHistory->toArray();
            }
        } catch (\Throwable $e) {}

        // Fallback dari log file jika db kosong
        if (empty($history)) {
            $path = storage_path('app/gps_verifications_log.json');
            if (file_exists($path)) {
                $history = json_decode(file_get_contents($path), true) ?: [];
            }
        }

        return response()->json([
            'school_name' => self::SCHOOL_NAME,
            'school_coords' => [self::SCHOOL_LAT, self::SCHOOL_LNG],
            'allowed_radius' => self::ALLOWED_RADIUS_METERS,
            'history' => $history
        ]);
    }

    /**
     * Reset sesi verifikasi agar bisa diuji kembali
     */
    public function resetSession($token)
    {
        try {
            DB::table('gps_sessions')->where('token', $token)->update([
                'status' => 'pending',
                'is_inside_school' => 0,
                'distance_meters' => null,
                'verified_at' => null
            ]);
        } catch (\Throwable $e) {}

        $fileSessions = $this->readSessionsFromFile();
        if (isset($fileSessions[$token])) {
            $fileSessions[$token]['status'] = 'pending';
            $fileSessions[$token]['is_inside_school'] = false;
            $fileSessions[$token]['distance_meters'] = null;
            $fileSessions[$token]['verified_at'] = null;
            $this->writeSessionsToFile($fileSessions);
        }

        return response()->json(['success' => true]);
    }
}
