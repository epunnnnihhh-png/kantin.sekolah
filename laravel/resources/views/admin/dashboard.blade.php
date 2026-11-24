<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EcoBites Canteen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes pageEnter {
            from { opacity: 0; transform: translateY(12px) scale(0.99); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .animate-page-enter {
            animation: pageEnter 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <!-- Preloader Overlay -->
    <div id="page-preloader" class="fixed inset-0 z-50 bg-slate-950 flex flex-col items-center justify-center text-white transition-opacity duration-300">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-orange-500 to-amber-400 p-0.5 shadow-xl shadow-orange-500/30 flex items-center justify-center animate-bounce mb-4">
            <span class="text-3xl">🔐</span>
        </div>
        <p class="font-extrabold text-lg text-orange-400">Dashboard Admin & Kasir</p>
        <p class="text-slate-400 text-xs mt-1 font-medium">Sinkronisasi data loket & omzet...</p>
    </div>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const preloader = document.getElementById('page-preloader');
            setTimeout(() => {
                if (preloader) {
                    preloader.classList.add('opacity-0', 'pointer-events-none');
                    setTimeout(() => preloader.remove(), 300);
                }
            }, 350);
        });
    </script>
    <div class="animate-page-enter">
    <nav class="bg-orange-600 shadow-md p-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center text-white">
            <h1 class="text-2xl font-bold">Admin Canteen 🍽️</h1>
            <div class="flex items-center space-x-4">
                <span>Halo, {{ session('user_name') ?? 'Admin' }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-md transition font-semibold">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto p-4 mt-6">
        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6 shadow-sm font-semibold border-l-4 border-green-500">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-6 shadow-sm font-semibold border-l-4 border-red-500">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Sidebar: QRIS & Tambah Menu -->
            <div class="space-y-6">
                <!-- QRIS Settings -->
                <div class="bg-white p-6 rounded-2xl shadow">
                    <h2 class="text-xl font-bold mb-4 text-gray-800 border-b pb-2">Pengaturan QRIS</h2>
                    <div class="mb-4 text-center">
                        <img src="{{ asset('storage/qris/qris_utama.png') }}" alt="QRIS Aktif" class="mx-auto w-40 h-40 object-contain border p-2 rounded bg-gray-50 mb-2" onerror="this.src='https://via.placeholder.com/150?text=Belum+Ada+QRIS'">
                        <p class="text-xs text-gray-500">QRIS Aktif Saat Ini</p>
                    </div>
                    <form action="{{ route('admin.qris.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <input type="file" name="qris_image" required class="w-full text-sm border p-2 rounded">
                        </div>
                        <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 rounded-lg transition">Update QRIS</button>
                    </form>
                </div>

                <!-- Tambah Menu -->
                <div class="bg-white p-6 rounded-2xl shadow">
                    <h2 class="text-xl font-bold mb-4 text-gray-800 border-b pb-2">Tambah Menu Baru</h2>
                    <form action="{{ route('admin.menu.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Menu</label>
                                <input type="text" name="name" required class="w-full border p-2 rounded focus:ring-orange-500 focus:border-orange-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori & Stand Kantin</label>
                                <select name="category" class="w-full border p-2 rounded">
                                    <option value="Sarapan">Sarapan (Kantin 2)</option>
                                    <option value="Makan Siang">Makan Siang (Kantin 7)</option>
                                    <option value="Minuman">Minuman (Kantin 3)</option>
                                    <option value="Cemilan">Cemilan (Kantin 4)</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp)</label>
                                    <input type="number" name="price" required min="0" class="w-full border p-2 rounded">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Stok</label>
                                    <input type="number" name="stock" required min="0" class="w-full border p-2 rounded">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Gambar Menu</label>
                                <input type="file" name="image" class="w-full border p-2 rounded text-sm">
                            </div>
                            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 rounded-lg transition">Simpan Menu</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Main Content: Orders & Menu List -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Pesanan Masuk -->
                <div class="bg-white p-6 rounded-2xl shadow">
                    <h2 class="text-xl font-bold mb-4 text-gray-800 border-b pb-2 flex items-center justify-between">
                        Daftar Pesanan Masuk
                        <span class="bg-orange-100 text-orange-700 text-xs px-2 py-1 rounded-full">{{ count($orders) }} Total</span>
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 text-gray-700">
                                <tr>
                                    <th class="p-3">ID/Waktu</th>
                                    <th class="p-3">Siswa</th>
                                    <th class="p-3">Total Harga</th>
                                    <th class="p-3">Status</th>
                                    <th class="p-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="p-3">
                                        <p class="font-bold">#{{ $order->id }}</p>
                                        <p class="text-xs text-gray-500">{{ $order->created_at->format('d M H:i') }}</p>
                                    </td>
                                    <td class="p-3 font-semibold">{{ $order->user->name ?? 'User' }}</td>
                                    <td class="p-3 text-emerald-600 font-bold">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                                    <td class="p-3">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            {{ $order->status == 'Menunggu Verifikasi' ? 'bg-yellow-100 text-yellow-700 border border-yellow-200' : '' }}
                                            {{ $order->status == 'Diproses' ? 'bg-blue-100 text-blue-700 border border-blue-200' : '' }}
                                            {{ $order->status == 'Siap Diambil' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : '' }}
                                            {{ $order->status == 'Selesai' ? 'bg-gray-100 text-gray-700 border border-gray-200' : '' }}">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <div class="flex items-center justify-center space-x-2">
                                            <button onclick="showProof('{{ asset('storage/' . $order->payment_proof) }}')" class="bg-gray-200 hover:bg-gray-300 text-gray-800 text-xs px-3 py-1 rounded font-semibold transition">Lihat Bukti</button>
                                            
                                            <form action="{{ route('admin.order.verify', $order->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <select name="status" onchange="this.form.submit()" class="text-xs border rounded p-1 bg-white cursor-pointer hover:border-orange-400">
                                                    <option value="Menunggu Verifikasi" {{ $order->status == 'Menunggu Verifikasi' ? 'selected' : '' }}>Menunggu</option>
                                                    <option value="Diproses" {{ $order->status == 'Diproses' ? 'selected' : '' }}>Proses</option>
                                                    <option value="Siap Diambil" {{ $order->status == 'Siap Diambil' ? 'selected' : '' }}>Siap Diambil</option>
                                                    <option value="Selesai" {{ $order->status == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                                </select>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="p-4 text-center text-gray-500">Tidak ada pesanan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Daftar Menu -->
                <div class="bg-white p-6 rounded-2xl shadow">
                    <h2 class="text-xl font-bold mb-4 text-gray-800 border-b pb-2">Katalog Menu Tersedia</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($menus as $menu)
                        <div class="border rounded-lg p-3 relative hover:shadow-md transition">
                            <span class="absolute top-2 right-2 bg-white bg-opacity-90 text-xs px-2 py-1 rounded-full font-bold shadow-sm">{{ $menu->category }}</span>
                            @if($menu->image)
                                <img src="{{ asset('storage/' . $menu->image) }}" class="w-full h-24 object-cover rounded mb-2" alt="{{ $menu->name }}">
                            @else
                                <div class="w-full h-24 bg-gray-200 rounded mb-2 flex items-center justify-center text-xs text-gray-500">No Img</div>
                            @endif
                            <h3 class="font-bold text-sm truncate" title="{{ $menu->name }}">{{ $menu->name }}</h3>
                            <div class="flex justify-between items-center mt-1">
                                <span class="text-orange-600 font-bold text-sm">Rp {{ number_format($menu->price, 0, ',', '.') }}</span>
                                <span class="text-xs text-gray-500 font-medium">Stok: {{ $menu->stock }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Bukti Transfer -->
    <div id="proof-modal" class="fixed inset-0 bg-black bg-opacity-75 hidden flex items-center justify-center z-50 p-4" onclick="closeProof(event)">
        <div class="bg-white p-4 rounded-xl max-w-lg w-full" onclick="event.stopPropagation()">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg">Bukti Transfer Siswa</h3>
                <button onclick="closeProof()" class="text-gray-500 hover:text-red-500 font-bold text-xl">&times;</button>
            </div>
            <img id="proof-img" src="" alt="Bukti Transfer" class="w-full h-auto max-h-[70vh] object-contain bg-gray-100 rounded">
            <div class="mt-4 text-right">
                <button onclick="closeProof()" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded font-semibold">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        function showProof(src) {
            document.getElementById('proof-img').src = src;
            document.getElementById('proof-modal').classList.remove('hidden');
        }

        function closeProof(e) {
            if(!e || e.target.id === 'proof-modal' || !e.target.closest('.bg-white')) {
                document.getElementById('proof-modal').classList.add('hidden');
                document.getElementById('proof-img').src = '';
            }
        }
    </script>
    </div>
</body>
</html>
