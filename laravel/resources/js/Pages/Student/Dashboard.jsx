import React, { useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';

export default function StudentDashboard({ menus, orders }) {
    const [cart, setCart] = useState([]);
    const { props } = usePage(); // to get session data if needed

    const handleLogout = () => {
        router.post('/logout');
    };

    const addToCart = (menu) => {
        setCart([...cart, menu]);
    };

    const totalCart = cart.reduce((acc, item) => acc + item.price, 0);

    return (
        <div className="min-h-screen bg-slate-50 font-sans pb-20">
            <Head title="Kantin - EcoBites" />

            {/* Navbar */}
            <nav className="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-20 items-center">
                        <div className="flex items-center space-x-3">
                            <div className="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/30 text-white font-bold text-xl">
                                E
                            </div>
                            <span className="text-2xl font-extrabold text-slate-800 tracking-tight">EcoBites <span className="text-emerald-500 font-medium text-lg ml-1 border-l-2 pl-2 border-slate-300">Siswa</span></span>
                        </div>
                        <div className="flex items-center space-x-4">
                            <button onClick={handleLogout} className="bg-white border-2 border-rose-100 text-rose-500 hover:bg-rose-500 hover:text-white px-5 py-2.5 rounded-full font-bold transition-all shadow-sm">
                                Logout
                            </button>
                        </div>
                    </div>
                </div>
            </nav>

            {/* Hero Section */}
            <div className="bg-emerald-600 text-white pt-16 pb-24 px-4 relative overflow-hidden">
                <div className="absolute top-0 right-0 opacity-10">
                    <svg className="w-96 h-96" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                </div>
                <div className="max-w-7xl mx-auto relative z-10">
                    <h1 className="text-4xl md:text-5xl font-black mb-4">Lapar? Pesan Sekarang.</h1>
                    <p className="text-emerald-100 text-lg max-w-xl">Pilih menu favoritmu, bayar pakai QRIS, dan ambil tanpa antre panjang di kantin!</p>
                </div>
            </div>

            <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10">
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    {/* Menu List */}
                    <div className="lg:col-span-2 space-y-6">
                        <div className="flex justify-between items-center bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
                            <h2 className="text-xl font-extrabold text-slate-800 px-2">Katalog Menu</h2>
                            <div className="flex space-x-2">
                                <span className="bg-emerald-100 text-emerald-700 px-4 py-2 rounded-full text-sm font-bold cursor-pointer">Semua</span>
                                <span className="bg-slate-100 text-slate-500 hover:bg-slate-200 px-4 py-2 rounded-full text-sm font-bold cursor-pointer transition">Makanan</span>
                                <span className="bg-slate-100 text-slate-500 hover:bg-slate-200 px-4 py-2 rounded-full text-sm font-bold cursor-pointer transition">Minuman</span>
                            </div>
                        </div>

                        <div className="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4">
                            {menus.map(menu => (
                                <div key={menu.id} className="bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100 hover:shadow-md transition-shadow duration-300 group flex flex-col justify-between p-2.5 sm:p-3">
                                    <div>
                                        <div className="aspect-[4/3] w-full bg-slate-50 border border-slate-100 rounded-xl relative overflow-hidden flex items-center justify-center p-1.5 mb-2.5">
                                            <div className="absolute top-2 left-2 bg-white/95 backdrop-blur-sm px-2 py-0.5 rounded-full text-[10px] sm:text-xs font-bold text-slate-700 shadow-xs z-10 border border-slate-100">
                                                {menu.category}
                                            </div>
                                            <img 
                                                src={menu.image ? `/storage/${menu.image}` : `https://source.unsplash.com/random/400x300/?food,${menu.category}`} 
                                                alt={menu.name}
                                                className="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300" 
                                            />
                                        </div>
                                        <h3 className="text-sm sm:text-base font-bold text-slate-800 line-clamp-2 leading-snug mb-2" title={menu.name}>{menu.name}</h3>
                                    </div>
                                    <div className="flex justify-between items-center pt-2 border-t border-slate-100">
                                        <span className="text-sm sm:text-base font-black text-emerald-600">Rp{menu.price.toLocaleString('id-ID')}</span>
                                        <button 
                                            onClick={() => addToCart(menu)}
                                            className="bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold px-3 py-1 rounded-lg flex items-center justify-center transition-colors shadow-xs"
                                        >
                                            + Tambah
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Cart & History */}
                    <div className="space-y-6">
                        
                        {/* Cart */}
                        <div className="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 sticky top-24">
                            <h2 className="text-xl font-extrabold text-slate-800 mb-6 flex justify-between items-center">
                                <span>Keranjang</span>
                                <span className="bg-emerald-100 text-emerald-700 text-sm px-3 py-1 rounded-full">{cart.length} item</span>
                            </h2>
                            
                            {cart.length === 0 ? (
                                <div className="text-center py-10 opacity-50">
                                    <div className="text-5xl mb-4">🛒</div>
                                    <p className="font-semibold text-slate-500">Keranjang masih kosong</p>
                                </div>
                            ) : (
                                <div className="space-y-4">
                                    <ul className="space-y-3 max-h-60 overflow-y-auto pr-2">
                                        {cart.map((item, idx) => (
                                            <li key={idx} className="flex justify-between items-center text-sm border-b border-slate-50 pb-2">
                                                <span className="font-semibold text-slate-700 truncate pr-2">{item.name}</span>
                                                <span className="font-bold text-slate-900 whitespace-nowrap">Rp{item.price.toLocaleString('id-ID')}</span>
                                            </li>
                                        ))}
                                    </ul>
                                    <div className="pt-4 border-t border-slate-100 flex justify-between items-center">
                                        <span className="text-slate-500 font-bold">Total</span>
                                        <span className="text-2xl font-black text-emerald-600">Rp{totalCart.toLocaleString('id-ID')}</span>
                                    </div>
                                    <button className="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-4 rounded-2xl shadow-lg shadow-emerald-500/30 transition-all transform hover:-translate-y-1">
                                        Bayar Sekarang
                                    </button>
                                </div>
                            )}
                        </div>

                        {/* Recent Orders */}
                        <div className="bg-slate-800 p-6 rounded-3xl shadow-lg text-white">
                            <h2 className="text-lg font-extrabold mb-4">Status Pesanan</h2>
                            <div className="space-y-3">
                                {orders.map(order => (
                                    <div key={order.id} className="bg-slate-700/50 p-4 rounded-2xl border border-slate-600/50 flex justify-between items-center">
                                        <div>
                                            <p className="text-xs text-slate-400 font-mono mb-1">#{order.id}</p>
                                            <p className="font-bold">Rp{order.total_price.toLocaleString('id-ID')}</p>
                                        </div>
                                        <span className="bg-amber-400/20 border border-amber-400/50 text-amber-300 text-xs px-3 py-1 rounded-full font-bold">
                                            {order.status}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>

                    </div>
                </div>
            </main>
        </div>
    );
}
