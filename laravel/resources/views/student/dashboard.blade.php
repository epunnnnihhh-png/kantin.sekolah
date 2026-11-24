<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - EcoBites Canteen</title>
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
<body class="bg-gray-50 font-sans">
    <!-- Preloader Overlay -->
    <div id="page-preloader" class="fixed inset-0 z-50 bg-slate-950 flex flex-col items-center justify-center text-white transition-opacity duration-300">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 p-0.5 shadow-xl shadow-emerald-500/30 flex items-center justify-center animate-bounce mb-4">
            <span class="text-3xl">🎒</span>
        </div>
        <p class="font-extrabold text-lg text-emerald-400">Portal Siswa EcoBites</p>
        <p class="text-slate-400 text-xs mt-1 font-medium">Menyiapkan menu & tiket antrean...</p>
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
    <nav class="bg-emerald-600 shadow-md p-4">
        <div class="max-w-6xl mx-auto flex justify-between items-center text-white">
            <h1 class="text-2xl font-bold">EcoBites Canteen 🌿</h1>
            <div class="flex items-center space-x-4">
                <span>Halo, {{ session('user_name') }} (Student)</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-md transition font-semibold">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto p-4 mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Menu List -->
        <div class="md:col-span-2">
            <h2 class="text-2xl font-bold mb-4 text-emerald-800">Menu Hari Ini</h2>
            @if(session('success'))
                <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
            @endif

            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4">
                @foreach($menus as $menu)
                <div class="bg-white p-2.5 sm:p-3 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition flex flex-col justify-between group">
                    <div>
                        <div class="w-full aspect-[4/3] bg-gray-50 rounded-xl mb-2 flex items-center justify-center p-1.5 overflow-hidden border border-gray-100 relative">
                            <span class="absolute top-2 left-2 bg-white/95 backdrop-blur-sm text-emerald-700 text-[10px] sm:text-xs px-2 py-0.5 rounded-full font-bold shadow-xs z-10 border border-emerald-100">{{ $menu->category }}</span>
                            @if($menu->image)
                                <img src="{{ asset('storage/' . $menu->image) }}" alt="{{ $menu->name }}" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-xs text-gray-400 font-medium">No Image</div>
                            @endif
                        </div>
                        <div class="text-[11px] text-gray-400 font-medium mb-1">Stok: {{ $menu->stock }}</div>
                        <h3 class="font-bold text-sm sm:text-base text-gray-800 line-clamp-2 leading-snug mb-2" title="{{ $menu->name }}">{{ $menu->name }}</h3>
                    </div>
                    <div class="pt-2 border-t border-gray-50 flex items-center justify-between gap-1">
                        <p class="text-emerald-600 font-extrabold text-sm sm:text-base">Rp {{ number_format($menu->price, 0, ',', '.') }}</p>
                        <button onclick="addToCart({{ $menu->id }}, '{{ $menu->name }}', {{ $menu->price }}, {{ $menu->stock }})" class="bg-emerald-500 hover:bg-emerald-600 text-white text-xs sm:text-sm py-1 px-3 rounded-lg font-bold transition shrink-0 shadow-xs">
                            + Tambah
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Riwayat Pesanan -->
            <h2 class="text-2xl font-bold mt-10 mb-4 text-emerald-800">Riwayat Pesanan</h2>
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-emerald-100 text-emerald-800">
                        <tr>
                            <th class="p-3">ID</th>
                            <th class="p-3">Total Harga</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $order)
                        <tr class="border-b">
                            <td class="p-3">#{{ $order->id }}</td>
                            <td class="p-3 font-semibold">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                            <td class="p-3">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    {{ $order->status == 'Menunggu Verifikasi' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                    {{ $order->status == 'Diproses' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $order->status == 'Siap Diambil' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                    {{ $order->status == 'Selesai' ? 'bg-gray-100 text-gray-700' : '' }}">
                                    {{ $order->status }}
                                </span>
                            </td>
                            <td class="p-3 text-sm text-gray-600">{{ $order->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="p-4 text-center text-gray-500">Belum ada pesanan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sidebar Keranjang -->
        <div>
            <div class="bg-white p-5 rounded-xl shadow sticky top-6">
                <h2 class="text-xl font-bold mb-4 flex items-center gap-2 text-emerald-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Keranjang
                </h2>
                
                <div id="cart-items" class="space-y-3 mb-4 max-h-60 overflow-y-auto">
                    <!-- Item render dari JS -->
                </div>
                
                <div class="border-t pt-4">
                    <div class="flex justify-between font-bold text-lg mb-4">
                        <span>Total:</span>
                        <span id="cart-total" class="text-emerald-600">Rp 0</span>
                    </div>
                    <button id="btn-pay" onclick="openModal()" disabled class="w-full bg-orange-500 hover:bg-orange-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-white py-3 rounded-lg font-bold transition">
                        Bayar Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pembayaran -->
    <div id="payment-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-2xl shadow-xl w-full max-w-md">
            <h2 class="text-2xl font-bold mb-4 text-center text-gray-800">Pembayaran QRIS</h2>
            <div class="text-center mb-6">
                <img src="{{ asset('storage/qris/qris_utama.png') }}" alt="QRIS" class="mx-auto border p-2 rounded-lg w-48 h-48 object-contain bg-gray-50 mb-2" onerror="this.src='https://via.placeholder.com/200?text=QRIS+Belum+Diatur'">
                <p class="text-sm text-gray-500">Scan QRIS di atas untuk membayar</p>
                <p class="text-xl font-bold text-emerald-600 mt-2" id="modal-total">Rp 0</p>
            </div>
            
            <form action="{{ route('student.checkout') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="total_price" id="input-total-price">
                <input type="hidden" name="items" id="input-cart-items">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload Bukti Transfer</label>
                    <input type="file" name="payment_proof" required class="w-full border p-2 rounded-md text-sm">
                </div>
                
                <div class="flex space-x-3">
                    <button type="button" onclick="closeModal()" class="w-1/2 bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 rounded-lg font-semibold transition">Batal</button>
                    <button type="submit" class="w-1/2 bg-emerald-500 hover:bg-emerald-600 text-white py-2 rounded-lg font-semibold transition">Konfirmasi</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let cart = [];

        function addToCart(id, name, price, stock) {
            let item = cart.find(i => i.id === id);
            if (item) {
                if(item.quantity < stock) item.quantity++;
                else alert('Stok tidak mencukupi!');
            } else {
                cart.push({ id, name, price, quantity: 1, stock });
            }
            renderCart();
        }

        function updateQty(id, delta) {
            let item = cart.find(i => i.id === id);
            if (item) {
                item.quantity += delta;
                if (item.quantity > item.stock) {
                    item.quantity = item.stock;
                    alert('Stok maksimal!');
                }
                if (item.quantity <= 0) {
                    cart = cart.filter(i => i.id !== id);
                }
            }
            renderCart();
        }

        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka);
        }

        function renderCart() {
            const container = document.getElementById('cart-items');
            container.innerHTML = '';
            let total = 0;

            if (cart.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-sm italic">Keranjang kosong</p>';
                document.getElementById('btn-pay').disabled = true;
            } else {
                document.getElementById('btn-pay').disabled = false;
                cart.forEach(item => {
                    const subtotal = item.price * item.quantity;
                    total += subtotal;
                    container.innerHTML += `
                        <div class="flex justify-between items-center bg-gray-50 p-2 rounded border">
                            <div class="flex-1">
                                <p class="font-semibold text-sm">${item.name}</p>
                                <p class="text-xs text-emerald-600">Rp ${formatRupiah(item.price)}</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button onclick="updateQty(${item.id}, -1)" class="w-6 h-6 flex items-center justify-center bg-gray-200 rounded-full hover:bg-gray-300">-</button>
                                <span class="text-sm font-semibold w-4 text-center">${item.quantity}</span>
                                <button onclick="updateQty(${item.id}, 1)" class="w-6 h-6 flex items-center justify-center bg-emerald-100 text-emerald-700 rounded-full hover:bg-emerald-200">+</button>
                            </div>
                        </div>
                    `;
                });
            }

            const totalText = `Rp ${formatRupiah(total)}`;
            document.getElementById('cart-total').innerText = totalText;
            document.getElementById('modal-total').innerText = totalText;
            document.getElementById('input-total-price').value = total;
            document.getElementById('input-cart-items').value = JSON.stringify(cart);
        }

        function openModal() {
            document.getElementById('payment-modal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('payment-modal').classList.add('hidden');
        }

        // Init
        renderCart();
    </script>
    </div>
</body>
</html>
