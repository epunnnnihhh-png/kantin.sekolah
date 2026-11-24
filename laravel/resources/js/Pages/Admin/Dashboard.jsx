import React from 'react';
import { Head, router } from '@inertiajs/react';

export default function AdminDashboard({ menus, orders }) {
    const handleLogout = () => {
        router.post('/logout');
    };

    return (
        <div className="min-h-screen bg-slate-50 font-sans">
            <Head title="Admin Dashboard - EcoBites" />

            {/* Navbar */}
            <nav className="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-20 items-center">
                        <div className="flex items-center space-x-3">
                            <div className="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/30 text-white font-bold text-xl">
                                E
                            </div>
                            <span className="text-2xl font-extrabold text-slate-800 tracking-tight">EcoBites <span className="text-emerald-500 font-medium text-lg ml-1 border-l-2 pl-2 border-slate-300">Admin</span></span>
                        </div>
                        <div className="flex items-center space-x-4">
                            <div className="hidden md:flex items-center space-x-2 text-sm font-semibold text-slate-600 bg-slate-100 px-4 py-2 rounded-full">
                                <img src={`https://ui-avatars.com/api/?name=Admin&background=10b981&color=fff`} className="w-6 h-6 rounded-full" />
                                <span>Halo, Admin</span>
                            </div>
                            <button onClick={handleLogout} className="bg-white border-2 border-rose-100 text-rose-500 hover:bg-rose-500 hover:text-white px-5 py-2.5 rounded-full font-bold transition-all shadow-sm">
                                Logout
                            </button>
                        </div>
                    </div>
                </div>
            </nav>

            <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                
                {/* Stats Row */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                    <div className="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center space-x-4 transform transition hover:-translate-y-1">
                        <div className="w-16 h-16 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center text-3xl">📦</div>
                        <div>
                            <p className="text-slate-500 text-sm font-bold uppercase tracking-wider">Total Pesanan</p>
                            <h3 className="text-3xl font-black text-slate-800">{orders.length}</h3>
                        </div>
                    </div>
                    <div className="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center space-x-4 transform transition hover:-translate-y-1">
                        <div className="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center text-3xl">💰</div>
                        <div>
                            <p className="text-slate-500 text-sm font-bold uppercase tracking-wider">Pendapatan (Demo)</p>
                            <h3 className="text-3xl font-black text-slate-800">Rp {orders.reduce((acc, curr) => acc + curr.total_price, 0).toLocaleString('id-ID')}</h3>
                        </div>
                    </div>
                    <div className="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center space-x-4 transform transition hover:-translate-y-1">
                        <div className="w-16 h-16 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center text-3xl">🍔</div>
                        <div>
                            <p className="text-slate-500 text-sm font-bold uppercase tracking-wider">Menu Aktif</p>
                            <h3 className="text-3xl font-black text-slate-800">{menus.length}</h3>
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    {/* Orders Column */}
                    <div className="lg:col-span-2 space-y-6">
                        <div className="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
                            <div className="flex justify-between items-center mb-6">
                                <h2 className="text-xl font-extrabold text-slate-800">Pesanan Masuk</h2>
                                <span className="bg-rose-100 text-rose-600 px-3 py-1 rounded-full text-xs font-bold animate-pulse">{orders.length} Pesanan Baru</span>
                            </div>
                            
                            <div className="space-y-4">
                                {orders.map(order => (
                                    <div key={order.id} className="p-5 border-2 border-slate-100 rounded-2xl hover:border-emerald-200 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-4">
                                        <div className="flex items-center space-x-4">
                                            <div className="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center text-xl">👤</div>
                                            <div>
                                                <h4 className="font-bold text-slate-800">{order.user.name}</h4>
                                                <p className="text-sm text-slate-500">Order #{order.id} • {new Date(order.created_at).toLocaleTimeString()}</p>
                                            </div>
                                        </div>
                                        <div className="text-left md:text-right">
                                            <div className="font-black text-emerald-600 text-lg">Rp {order.total_price.toLocaleString('id-ID')}</div>
                                            <div className="mt-1">
                                                <span className={`px-3 py-1 rounded-full text-xs font-bold ${order.status === 'Selesai' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'}`}>
                                                    {order.status}
                                                </span>
                                            </div>
                                        </div>
                                        <div>
                                            <button className="w-full md:w-auto bg-slate-800 hover:bg-slate-700 text-white px-5 py-2.5 rounded-xl font-semibold transition-colors text-sm">
                                                Verifikasi
                                            </button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>

                    {/* Sidebar Column */}
                    <div className="space-y-6">
                        
                        <div className="bg-gradient-to-br from-emerald-500 to-teal-600 p-8 rounded-3xl shadow-lg text-white">
                            <h2 className="text-xl font-extrabold mb-2">QRIS Kantin</h2>
                            <p className="text-emerald-100 text-sm mb-6">Scan untuk pembayaran instan</p>
                            <div className="bg-white p-4 rounded-2xl flex justify-center items-center">
                                {/* Fake QR */}
                                <div className="w-40 h-40 bg-[url('https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=EcoBites')] bg-cover opacity-80 mix-blend-multiply"></div>
                            </div>
                            <button className="mt-6 w-full bg-white/20 hover:bg-white/30 text-white font-bold py-3 rounded-xl transition backdrop-blur-sm">
                                Update QRIS
                            </button>
                        </div>

                        <div className="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
                            <h2 className="text-xl font-extrabold text-slate-800 mb-6">Menu Tersedia</h2>
                            <ul className="space-y-4">
                                {menus.map(menu => (
                                    <li key={menu.id} className="flex justify-between items-center p-3 hover:bg-slate-50 rounded-xl transition">
                                        <div className="flex items-center space-x-3">
                                            <div className="w-10 h-10 bg-slate-200 rounded-lg overflow-hidden">
                                                <img src={`https://ui-avatars.com/api/?name=${menu.name.charAt(0)}&background=random`} className="w-full h-full object-cover" />
                                            </div>
                                            <div>
                                                <h4 className="font-bold text-slate-800 text-sm">{menu.name}</h4>
                                                <p className="text-xs text-slate-400">{menu.category}</p>
                                            </div>
                                        </div>
                                        <div className="text-right">
                                            <div className="font-bold text-emerald-600 text-sm">Rp{menu.price.toLocaleString('id-ID')}</div>
                                            <div className="text-xs text-slate-400">Stok: {menu.stock}</div>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                            <button className="mt-6 w-full border-2 border-dashed border-slate-300 hover:border-emerald-500 hover:text-emerald-600 text-slate-500 font-bold py-3 rounded-xl transition">
                                + Tambah Menu Baru
                            </button>
                        </div>

                    </div>

                </div>
            </main>
        </div>
    );
}
