<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EcoBites Canteen</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-emerald-50 h-screen flex items-center justify-center font-sans">

    <div class="bg-white p-8 rounded-2xl shadow-xl max-w-md w-full border border-emerald-100">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-emerald-600 mb-2">EcoBites 🌿</h1>
            <p class="text-gray-500 text-sm">Masuk ke Kantin Digital Sekolah</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm font-semibold text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                <input type="email" name="email" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="admin@kantin.com">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="password">
            </div>

            <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 px-4 rounded-lg transition duration-200 shadow-md">
                Masuk Sekarang
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-gray-400">
            <p>Admin: admin@kantin.com | pass: password</p>
            <p>Siswa: budi@sekolah.com | pass: password</p>
        </div>
    </div>

</body>
</html>
