<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>E-Kantin Sekolah</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: '#16a34a', // green-600
                        secondary: '#2563eb', // blue-600
                        dark: '#0f172a',
                    },
                    boxShadow: {
                        'flat': '4px 4px 0px #cbd5e1',
                        'flat-dark': '4px 4px 0px #0f172a',
                        'flat-primary': '4px 4px 0px #15803d',
                        'flat-secondary': '4px 4px 0px #1d4ed8',
                    }
                }
            }
        }
    </script>

    <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js" crossorigin></script>
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-7px) scale(1.02); }
        }
        @keyframes pulseGlow {
            0%, 100% { opacity: 0.35; transform: scale(0.96); }
            50% { opacity: 0.75; transform: scale(1.08); }
        }
        @keyframes spinSlow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes pageEnter {
            from {
                opacity: 0;
                transform: translateY(12px) scale(0.99);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        @keyframes shimmer {
            0% { transform: translateX(-150%); }
            100% { transform: translateX(250%); }
        }
        @keyframes scaleUp {
            from { opacity: 0; transform: scale(0.94); }
            to { opacity: 1; transform: scale(1); }
        }
        .animate-float {
            animation: float 2.6s ease-in-out infinite;
        }
        .animate-pulse-glow {
            animation: pulseGlow 2.5s ease-in-out infinite;
        }
        .animate-spin-slow {
            animation: spinSlow 14s linear infinite;
        }
        .animate-page-enter {
            animation: pageEnter 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .animate-scale-up {
            animation: scaleUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .animate-shimmer {
            animation: shimmer 1.8s infinite;
        }
    </style>
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800">
    <div id="app"></div>

    <script>
        window.addEventListener('error', function(event) {
            document.body.innerHTML = '<div style="color:red; padding:20px; font-family:monospace; background:white; position:fixed; top:0; left:0; right:0; bottom:0; z-index:9999; overflow:auto;">' +
                '<h1>Error occurred:</h1>' +
                '<p><b>Message:</b> ' + event.message + '</p>' +
                '<p><b>Location:</b> ' + event.filename + ':' + event.lineno + ':' + event.colno + '</p>' +
                '<pre>' + (event.error && event.error.stack ? event.error.stack : '') + '</pre>' +
                '</div>';
        });
    </script>

    <script type="text/babel">
        const { useState, useEffect } = React;

        // --- COMPONENTS --- //
        
        function Button({ children, onClick, variant = 'primary', className = '', type, disabled = false }) {
            const baseClass = "px-6 py-2.5 rounded-full font-bold transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed";
            const variants = {
                primary: "bg-gradient-to-r from-green-500 to-primary text-white shadow-md shadow-green-200 hover:shadow-lg hover:-translate-y-0.5",
                secondary: "bg-gradient-to-r from-blue-500 to-secondary text-white shadow-md shadow-blue-200 hover:shadow-lg hover:-translate-y-0.5",
                outline: "bg-white border border-slate-200 text-slate-700 shadow-sm hover:shadow hover:border-slate-300 hover:bg-slate-50",
                dark: "bg-slate-800 text-white shadow-md hover:shadow-lg hover:-translate-y-0.5 hover:bg-slate-900"
            };
            return (
                <button type={type} onClick={onClick} disabled={disabled} className={`${baseClass} ${variants[variant]} ${className}`}>
                    {children}
                </button>
            );
        }

        function Card({ children, className = '' }) {
            return (
                <div className={`bg-white border border-slate-100 rounded-[1.5rem] shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] p-6 ${className}`}>
                    {children}
                </div>
            );
        }

        function PageLoader({ title = "Sinkronisasi Data...", subtitle = "Menyiapkan sistem dari server kantin...", icon = "K" }) {
            const glowDelay = { animationDelay: '0.6s' };
            return (
                <div className="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950 flex flex-col items-center justify-center p-6 text-white relative overflow-hidden select-none animate-page-enter">
                    <div className="absolute w-80 h-80 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none animate-pulse-glow"></div>
                    <div className="absolute w-72 h-72 bg-blue-500/10 rounded-full blur-3xl pointer-events-none animate-pulse-glow" style={glowDelay}></div>
                    <div className="relative z-10 flex flex-col items-center text-center">
                        <div className="relative mb-5">
                            <div className="w-24 h-24 rounded-full border-2 border-dashed border-emerald-400/30 animate-spin-slow absolute -inset-2"></div>
                            <div className="w-20 h-20 rounded-2xl bg-gradient-to-tr from-emerald-600 to-green-400 p-0.5 shadow-xl shadow-emerald-500/30 flex items-center justify-center relative animate-float">
                                <div className="w-full h-full bg-slate-950/90 rounded-[0.9rem] flex items-center justify-center text-2xl font-black text-emerald-400">
                                    {icon}
                                </div>
                                <span className="absolute -top-1 -right-1 flex h-3.5 w-3.5">
                                    <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span className="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500"></span>
                                </span>
                            </div>
                        </div>
                        <div className="flex items-center gap-2 mb-1.5">
                            <div className="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></div>
                            <span className="font-extrabold text-white text-lg tracking-tight">{title}</span>
                        </div>
                        <p className="text-slate-400 text-xs font-medium max-w-xs">{subtitle}</p>
                        
                        <div className="w-48 h-2 bg-slate-800/90 rounded-full overflow-hidden mt-6 border border-slate-700/60 p-0.5 shadow-inner">
                            <div className="h-full bg-gradient-to-r from-emerald-500 via-green-400 to-emerald-300 rounded-full w-full animate-pulse"></div>
                        </div>
                    </div>
                </div>
            );
        }

        // --- MODAL STATUS PESANAN & PAPAN ANTREAN LOKET KANTIN 1 - 7 --- //
        function CheckOrderModal({ isOpen, onClose }) {
            const [query, setQuery] = useState('');
            const [order, setOrder] = useState(null);
            const [isLoading, setIsLoading] = useState(false);
            const [error, setError] = useState('');
            const [lastSavedOrder, setLastSavedOrder] = useState(null);
            const [activeCanteenTab, setActiveCanteenTab] = useState('all'); // 'all', 'my_order'
            const [queueData, setQueueData] = useState({ ready: [], cooking: [], pending: [], counts: { ready: 0, cooking: 0, pending: 0, total: 0 } });
            const [isQueueLoading, setIsQueueLoading] = useState(false);

            const canteensList = [
                { id: 1, name: 'Kantin 1', title: 'Kantin 1 (Nasi)', icon: 'K1', code_prefix: 'K1' },
                { id: 2, name: 'Kantin 2', title: 'Kantin 2 (Sarapan)', icon: 'K2', code_prefix: 'K2' },
                { id: 3, name: 'Kantin 3', title: 'Kantin 3 (Minuman)', icon: 'K3', code_prefix: 'K3' },
                { id: 4, name: 'Kantin 4', title: 'Kantin 4 (Cemilan)', icon: 'K4', code_prefix: 'K4' },
                { id: 5, name: 'Kantin 5', title: 'Kantin 5 (Bakso & Soto)', icon: 'K5', code_prefix: 'K5' },
                { id: 6, name: 'Kantin 6', title: 'Kantin 6 (Western & Bento)', icon: 'K6', code_prefix: 'K6' },
                { id: 7, name: 'Kantin 7', title: 'Kantin 7 (Makan Siang)', icon: 'K7', code_prefix: 'K7' },
            ];

            const loadQueueBoard = (canteenId = null) => {
                const targetCId = (canteenId !== null && canteenId !== undefined) ? canteenId : (activeCanteenTab === 'all' || activeCanteenTab === 'my_order' ? '' : activeCanteenTab);
                const url = targetCId ? `/api/canteen-queues?canteen_id=${targetCId}` : '/api/canteen-queues';
                setIsQueueLoading(true);
                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        setIsQueueLoading(false);
                        if (data && data.ready) {
                            setQueueData(data);
                        }
                    })
                    .catch(() => {
                        setIsQueueLoading(false);
                    });
            };

            useEffect(() => {
                if (isOpen) {
                    try {
                        const saved = localStorage.getItem('kantin_my_last_order');
                        if (saved) {
                            const parsed = JSON.parse(saved);
                            setLastSavedOrder(parsed);
                            if (parsed && (parsed.order_code || parsed.queue) && !order) {
                                const initialQuery = parsed.order_code || parsed.queue;
                                setQuery(initialQuery);
                                fetchOrder(initialQuery);
                            }
                        }
                    } catch(e) {}
                    loadQueueBoard(activeCanteenTab === 'all' || activeCanteenTab === 'my_order' ? null : activeCanteenTab);
                } else {
                    setError('');
                }
            }, [isOpen, activeCanteenTab]);

            // Auto-refresh queue board every 4 seconds
            useEffect(() => {
                if (!isOpen) return;
                const timer = setInterval(() => {
                    loadQueueBoard(activeCanteenTab === 'all' || activeCanteenTab === 'my_order' ? null : activeCanteenTab);
                    if (order && order.order_id && order.status !== 'completed') {
                        fetch('/api/order-status/' + order.order_id)
                            .then(res => res.json())
                            .then(data => {
                                if (data && data.found) setOrder(data);
                            })
                            .catch(() => {});
                    }
                }, 4000);
                return () => clearInterval(timer);
            }, [isOpen, activeCanteenTab, order]);

            const fetchOrder = (searchKey) => {
                const key = searchKey !== undefined ? searchKey : query;
                if (!key || !key.trim()) {
                    setError('Silakan masukkan nomor / kode antrean (contoh: K1-001, K3-002) atau nama.');
                    return;
                }

                setIsLoading(true);
                setError('');

                fetch('/api/search-order?q=' + encodeURIComponent(key.trim()))
                    .then(res => res.json())
                    .then(data => {
                        setIsLoading(false);
                        if (data.found) {
                            setOrder(data);
                            setError('');
                        } else {
                            setOrder(null);
                            setError(data.message || 'Pesanan tidak ditemukan.');
                        }
                    })
                    .catch(() => {
                        setIsLoading(false);
                        setError('Terjadi kendala saat memeriksa pesanan. Silakan coba lagi.');
                    });
            };

            if (!isOpen) return null;

            // Filter queue items by current search query if typed
            const cleanQ = query.trim().toUpperCase();
            const filterList = (list) => {
                if (!list) return [];
                if (!cleanQ) return list;
                return list.filter(item => 
                    (item.order_code && item.order_code.toUpperCase().includes(cleanQ)) ||
                    (item.customer_name && item.customer_name.toUpperCase().includes(cleanQ)) ||
                    (item.canteen_name && item.canteen_name.toUpperCase().includes(cleanQ))
                );
            };

            const readyFiltered = filterList(queueData.ready || []);
            const cookingFiltered = filterList(queueData.cooking || []);

            return (
                <div className="fixed inset-0 z-[99990] bg-slate-950/75 backdrop-blur-md flex items-start justify-center pt-2 sm:pt-5 pb-6 px-3 sm:px-6 overflow-y-auto animate-page-enter">
                    <div className="bg-white rounded-3xl max-w-4xl w-full shadow-2xl border border-slate-100 overflow-hidden my-auto max-h-[94vh] flex flex-col">
                        
                        {/* Header Modal */}
                        <div className="bg-gradient-to-r from-emerald-600 via-teal-700 to-slate-900 p-4 sm:p-6 text-white relative shrink-0">
                            <button 
                                onClick={onClose} 
                                className="absolute top-4 sm:top-5 right-4 sm:right-5 w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center font-bold transition-all text-sm shadow-sm"
                            >
                                ✕
                            </button>
                            <div className="flex items-center gap-2 flex-wrap mb-2">
                                <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 text-white text-[10px] sm:text-[11px] font-black uppercase tracking-wider">
                                    <span className="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                                    <span>PAPAN ANTREAN LOKET KANTIN 1 - 7</span>
                                </span>
                                <span className="text-[11px] font-bold text-emerald-200 bg-emerald-950/40 px-2.5 py-0.5 rounded-full border border-emerald-400/30">
                                    SMKN 1 Ciomas
                                </span>
                            </div>
                            <h3 className="text-xl sm:text-2xl font-black text-white tracking-tight">
                                Status & Pelacak Pesanan Kantin
                            </h3>
                            <p className="text-emerald-100/90 text-xs mt-1 max-w-xl">
                                Pantau kesiapan makanan di loket Kantin 1 sampai 7 secara real-time. Tunjukkan kode antrean saat mengambil di loket.
                            </p>
                        </div>

                        {/* Tab Filter Kantin */}
                        <div className="bg-slate-100 p-2 sm:p-3 border-b border-slate-200 shrink-0 overflow-x-auto flex gap-1.5 scrollbar-thin items-center justify-between">
                            <div className="flex items-center gap-1.5">
                                <button 
                                    type="button"
                                    onClick={() => { setActiveCanteenTab('all'); }}
                                    className={`px-3 sm:px-4 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap shrink-0 flex items-center gap-1.5 ${activeCanteenTab === 'all' ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-200/80 border border-slate-200'}`}
                                >
                                    Semua Kantin (1-7)
                                </button>
                                {lastSavedOrder && (
                                    <button 
                                        type="button"
                                        onClick={() => { 
                                            setActiveCanteenTab('my_order');
                                            const code = lastSavedOrder.order_code || lastSavedOrder.queue;
                                            if (code) { setQuery(code); fetchOrder(code); }
                                        }}
                                        className={`px-3 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap shrink-0 flex items-center gap-1.5 ${activeCanteenTab === 'my_order' ? 'bg-teal-600 text-white shadow-sm' : 'bg-white text-teal-800 border border-teal-300 hover:bg-teal-50'}`}
                                    >
                                        Pesanan Terakhir Saya
                                    </button>
                                )}
                            </div>
                            <span className="text-[11px] font-bold text-slate-500 hidden sm:inline">
                                Memantau seluruh loket Kantin 1 s/d 7
                            </span>
                        </div>

                        {/* Konten Scrollable */}
                        <div className="p-4 sm:p-6 space-y-5 overflow-y-auto flex-1">
                            
                            {/* Bar Pencarian Cepat */}
                            <form onSubmit={(e) => { e.preventDefault(); fetchOrder(); }} className="flex gap-2">
                                <div className="relative flex-1">
                                    <input 
                                        type="text" 
                                        value={query} 
                                        onChange={(e) => setQuery(e.target.value)} 
                                        placeholder="Cari kode (misal: K1-001, K3-002, K7) atau nama siswa..." 
                                        className="w-full border-2 border-slate-200 rounded-2xl px-4 py-2.5 sm:py-3 font-bold text-slate-800 text-xs sm:text-sm focus:border-emerald-500 focus:outline-none transition-colors"
                                    />
                                    {query && (
                                        <button 
                                            type="button" 
                                            onClick={() => { setQuery(''); setOrder(null); setError(''); }}
                                            className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 font-bold text-sm"
                                        >
                                            ✕
                                        </button>
                                    )}
                                </div>
                                <button 
                                    type="submit" 
                                    disabled={isLoading}
                                    className="px-4 sm:px-6 py-2.5 sm:py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs sm:text-sm rounded-2xl transition-all shadow-md disabled:opacity-50 shrink-0 flex items-center gap-1.5"
                                >
                                    {isLoading ? 'Mencari...' : 'Periksa'}
                                </button>
                            </form>

                            {error && (
                                <div className="p-3 bg-red-50 border border-red-200 rounded-2xl text-red-700 text-xs font-bold flex items-center gap-2">
                                    <span>[Perhatian]</span> {error}
                                </div>
                            )}

                            {/* HASIL PENCARIAN DETAIL PESANAN */}
                            {order && (
                                <div className="p-4 sm:p-5 rounded-2xl border-2 border-emerald-400 bg-emerald-50/70 space-y-4 animate-scale-up">
                                    <div className="flex items-center justify-between gap-3 flex-wrap">
                                        <div className="flex items-center gap-3">
                                            <div className={`w-12 h-12 rounded-2xl flex items-center justify-center text-xs font-black shadow-md shrink-0 text-white ${
                                                order.status === 'ready' ? 'bg-gradient-to-br from-emerald-500 to-green-600' :
                                                order.status === 'cooking' ? 'bg-gradient-to-br from-amber-500 to-orange-500' :
                                                order.status === 'completed' ? 'bg-slate-700' : 'bg-blue-600'
                                            }`}>
                                                {order.status === 'ready' ? 'SIAP' : order.status === 'cooking' ? 'MASAK' : order.status === 'completed' ? 'OK' : 'ANTRE'}
                                            </div>
                                            <div>
                                                <div className="flex items-center gap-2">
                                                    <span className="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-emerald-600 text-white">
                                                        {order.badge || order.status}
                                                    </span>
                                                    <span className="text-xs font-bold text-slate-500">
                                                        Loket: <b className="text-slate-800">{order.canteen_name || 'Kantin'}</b>
                                                    </span>
                                                </div>
                                                <h4 className="font-black text-slate-900 text-base sm:text-lg mt-0.5">
                                                    {order.status_text}
                                                </h4>
                                                <p className="text-xs text-slate-600 font-medium">
                                                    Atas Nama: <b className="text-slate-800">{order.customer_name}</b> • {order.created_at}
                                                </p>
                                            </div>
                                        </div>
                                        <div className="text-right shrink-0">
                                            <p className="text-[10px] font-black uppercase tracking-widest text-slate-400">Nomor Antrean</p>
                                            <p className="text-3xl font-black text-emerald-800">{order.order_code || order.queue}</p>
                                        </div>
                                    </div>

                                    {/* Petunjuk Loket Pengambilan */}
                                    <div className="p-3 bg-slate-900 text-white rounded-xl text-xs flex items-center justify-between gap-2">
                                        <div className="flex items-center gap-2">
                                            <span className="font-bold text-slate-200">{order.instruction}</span>
                                        </div>
                                        <span className="text-[10px] font-black text-emerald-400 bg-emerald-950 px-2 py-0.5 rounded-full border border-emerald-800 shrink-0">
                                            Loket Fast Track
                                        </span>
                                    </div>

                                    {/* Tahapan Pemrosesan Stepper */}
                                    <div className="bg-white p-3 rounded-xl border border-slate-200">
                                        <p className="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2">Status Alur Pesanan:</p>
                                        <div className="grid grid-cols-4 gap-1 text-center text-xs">
                                            {[
                                                { s: 1, label: 'Diterima', icon: '1' },
                                                { s: 2, label: 'Dimasak', icon: '2' },
                                                { s: 3, label: 'Siap Diambil', icon: '3' },
                                                { s: 4, label: 'Selesai', icon: '4' }
                                            ].map(stepItem => (
                                                <div key={stepItem.s} className="flex flex-col items-center">
                                                    <div className={`w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold mb-1 ${
                                                        order.step >= stepItem.s ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-400'
                                                    }`}>
                                                        {stepItem.icon}
                                                    </div>
                                                    <span className={`text-[10px] font-extrabold ${order.step >= stepItem.s ? 'text-emerald-800' : 'text-slate-400'}`}>
                                                        {stepItem.label}
                                                    </span>
                                                </div>
                                            ))}
                                        </div>
                                    </div>

                                    {/* Sister orders jika dipesan bersamaan di kantin lain */}
                                    {order.related_orders && order.related_orders.length > 0 && (
                                        <div className="p-3 bg-amber-50 border border-amber-200 rounded-xl space-y-2">
                                            <p className="text-[10px] font-black uppercase text-amber-900 tracking-wider">
                                                Tiket Kantin Lain Dalam Pesanan Yang Sama:
                                            </p>
                                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                {order.related_orders.map((ro, rIdx) => (
                                                    <div 
                                                        key={rIdx}
                                                        onClick={() => { setQuery(ro.order_code); fetchOrder(ro.order_code); }}
                                                        className="p-2 bg-white rounded-lg border border-amber-100 flex items-center justify-between cursor-pointer hover:border-amber-400 transition-all text-xs"
                                                    >
                                                        <div className="min-w-0">
                                                            <span className="font-extrabold text-amber-950">
                                                                {ro.canteen_name}
                                                            </span>
                                                            <p className="text-[11px] text-slate-500 truncate">{ro.items_summary}</p>
                                                        </div>
                                                        <div className="text-right shrink-0 pl-2">
                                                            <span className="font-black text-slate-900 block">{ro.order_code}</span>
                                                            <span className={`text-[9px] font-bold px-1.5 py-0.2 rounded ${ro.status === 'ready' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'}`}>
                                                                {ro.status === 'ready' ? 'Siap' : ro.status}
                                                            </span>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    )}

                                    {/* Rincian Menu */}
                                    <div className="bg-white p-3 rounded-xl border border-slate-200 text-xs">
                                        <div className="flex justify-between items-center border-b pb-2 mb-2 font-bold text-slate-700">
                                            <span>Rincian Menu ({((order.items && order.items.length) || 0)} Item)</span>
                                            <span className="font-black text-emerald-700">Rp {Number(order.total_amount).toLocaleString('id-ID')}</span>
                                        </div>
                                        <div className="space-y-1.5 max-h-32 overflow-y-auto">
                                            {(order.items || []).map((it, idx) => (
                                                <div key={idx} className="flex justify-between items-center text-slate-600">
                                                    <span>{it.name} <b className="text-slate-800">x{it.quantity}</b> ({it.pickup_stand})</span>
                                                    <span className="font-bold text-slate-800">Rp {Number(it.subtotal).toLocaleString('id-ID')}</span>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* PAPAN ANTREAN LIVE (AIRPORT / FAST-FOOD DUAL BOARD) */}
                            <div className="space-y-3 pt-2">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <h4 className="font-black text-slate-900 text-sm sm:text-base">
                                            Live Board Antrean: {activeCanteenTab === 'my_order' ? 'Pesanan Terakhir Saya' : 'Semua Kantin (1 s/d 7)'}
                                        </h4>
                                        {isQueueLoading && <span className="text-[10px] text-slate-400 animate-pulse">Memperbarui...</span>}
                                    </div>
                                    <span className="text-xs font-bold text-slate-500 bg-slate-100 px-2.5 py-0.5 rounded-full">
                                        Total: {queueData.counts.total} antrean aktif
                                    </span>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    
                                    {/* KOLOM 1: SIAP DIAMBIL (READY) */}
                                    <div className="bg-emerald-50/80 border-2 border-emerald-300 rounded-2xl p-4 shadow-sm flex flex-col">
                                        <div className="flex items-center justify-between pb-3 border-b border-emerald-200 mb-3">
                                            <div>
                                                <h5 className="font-black text-emerald-950 text-sm">SIAP DIAMBIL DI LOKET</h5>
                                                <p className="text-[10px] text-emerald-700 font-semibold">Tunjukkan kode ke staff loket</p>
                                            </div>
                                            <span className="bg-emerald-600 text-white text-xs font-black px-2.5 py-0.5 rounded-full shadow-xs">
                                                {readyFiltered.length} Siap
                                            </span>
                                        </div>

                                        <div className="space-y-2 overflow-y-auto max-h-72 pr-1">
                                            {readyFiltered.length === 0 ? (
                                                <div className="text-center py-8 text-emerald-600/70 text-xs font-bold italic">
                                                    Tidak ada antrean yang sedang siap diambil di loket ini.
                                                </div>
                                            ) : (
                                                readyFiltered.map(item => (
                                                    <div 
                                                        key={item.id}
                                                        onClick={() => { setQuery(item.order_code); fetchOrder(item.order_code); }}
                                                        className="bg-white p-3 rounded-xl border border-emerald-200 hover:border-emerald-500 shadow-2xs hover:shadow-sm cursor-pointer transition-all flex items-center justify-between group"
                                                    >
                                                        <div className="flex items-center gap-2.5 min-w-0">
                                                            <div className="min-w-0">
                                                                <div className="flex items-center gap-1.5">
                                                                    <span className="font-black text-base text-emerald-900 group-hover:text-emerald-600 transition-colors">
                                                                        {item.order_code}
                                                                    </span>
                                                                    <span className="text-[9px] font-black uppercase px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800">
                                                                        {item.canteen_name}
                                                                    </span>
                                                                </div>
                                                                <p className="text-xs text-slate-500 font-medium truncate">
                                                                    {item.customer_name} • {item.portions} porsi • {item.updated_time}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <button 
                                                            type="button" 
                                                            className="text-emerald-700 bg-emerald-50 group-hover:bg-emerald-600 group-hover:text-white px-2.5 py-1 rounded-lg text-xs font-extrabold transition-all shrink-0 ml-2"
                                                        >
                                                            Ambil →
                                                        </button>
                                                    </div>
                                                ))
                                            )}
                                        </div>
                                    </div>

                                    {/* KOLOM 2: SEDANG DIMASAK / DISIAPKAN (COOKING) */}
                                    <div className="bg-amber-50/80 border-2 border-amber-300 rounded-2xl p-4 shadow-sm flex flex-col">
                                        <div className="flex items-center justify-between pb-3 border-b border-amber-200 mb-3">
                                            <div>
                                                <h5 className="font-black text-amber-950 text-sm">SEDANG DIMASAK / DISIAPKAN</h5>
                                                <p className="text-[10px] text-amber-700 font-semibold">Staff sedang memproses makanan</p>
                                            </div>
                                            <span className="bg-amber-500 text-white text-xs font-black px-2.5 py-0.5 rounded-full shadow-xs">
                                                {cookingFiltered.length} Dimasak
                                            </span>
                                        </div>

                                        <div className="space-y-2 overflow-y-auto max-h-72 pr-1">
                                            {cookingFiltered.length === 0 ? (
                                                <div className="text-center py-8 text-amber-600/70 text-xs font-bold italic">
                                                    Tidak ada antrean yang sedang dimasak di loket ini.
                                                </div>
                                            ) : (
                                                cookingFiltered.map(item => (
                                                    <div 
                                                        key={item.id}
                                                        onClick={() => { setQuery(item.order_code); fetchOrder(item.order_code); }}
                                                        className="bg-white p-3 rounded-xl border border-amber-200 hover:border-amber-500 shadow-2xs hover:shadow-sm cursor-pointer transition-all flex items-center justify-between group"
                                                    >
                                                        <div className="flex items-center gap-2.5 min-w-0">
                                                            <div className="min-w-0">
                                                                <div className="flex items-center gap-1.5">
                                                                    <span className="font-black text-base text-amber-950 group-hover:text-amber-600 transition-colors">
                                                                        {item.order_code}
                                                                    </span>
                                                                    <span className="text-[9px] font-black uppercase px-1.5 py-0.2 rounded bg-amber-100 text-amber-800">
                                                                        {item.canteen_name}
                                                                    </span>
                                                                </div>
                                                                <p className="text-xs text-slate-500 font-medium truncate">
                                                                    {item.customer_name} • {item.portions} porsi • {item.created_time}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <span className="text-[10px] font-extrabold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md shrink-0 ml-2">
                                                            Proses
                                                        </span>
                                                    </div>
                                                ))
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Footer Modal */}
                        <div className="p-3.5 sm:p-4 bg-slate-50 border-t border-slate-200 flex justify-between items-center shrink-0">
                            <div className="flex items-center gap-1.5 text-xs text-slate-500 font-semibold">
                                <span className="hidden sm:inline">Pembaruan otomatis tiap 4 detik</span>
                                <span className="sm:hidden">Auto-refresh aktif</span>
                            </div>
                            <button 
                                type="button"
                                onClick={onClose} 
                                className="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all shadow-xs"
                            >
                                Tutup Papan Antrean
                            </button>
                        </div>
                    </div>
                </div>
            );
        }

        // --- MODAL DETEKSI GPS SISWA DI SMKN 1 CIOMAS (CROSS-DEVICE & DIRECT) --- //
        function GpsCrossDeviceModal({ isOpen, onClose, onVerified, currentStudentName, currentUserId }) {
            const [activeTab, setActiveTab] = useState('cross_device'); // 'cross_device', 'direct', 'simulation'
            const [session, setSession] = useState(null);
            const [isCreatingSession, setIsCreatingSession] = useState(false);
            const [pollCount, setPollCount] = useState(0);
            const [isDirectVerifying, setIsDirectVerifying] = useState(false);
            const [directResult, setDirectResult] = useState(null);
            const [copied, setCopied] = useState(false);
            const [errorMsg, setErrorMsg] = useState('');

            // Inisialisasi sesi perangkat berbeda saat modal dibuka
            useEffect(() => {
                if (isOpen) {
                    initSession();
                } else {
                    setErrorMsg('');
                    setDirectResult(null);
                }
            }, [isOpen]);

            const initSession = () => {
                setIsCreatingSession(true);
                setErrorMsg('');
                fetch('/api/gps/create-session', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        student_name: currentStudentName || 'Siswa SMKN 1 Ciomas',
                        user_id: currentUserId || null
                    })
                })
                .then(res => res.json())
                .then(data => {
                    setIsCreatingSession(false);
                    if (data.success) {
                        setSession(data);
                    } else {
                        setErrorMsg('Gagal membuat sesi GPS perangkat berbeda.');
                    }
                })
                .catch(() => {
                    setIsCreatingSession(false);
                    setErrorMsg('Terjadi kendala jaringan saat menghubungkan server.');
                });
            };

            // Polling status verifikasi dari perangkat berbeda tiap 2 detik
            useEffect(() => {
                if (!isOpen || !session || !session.token) return;
                if (session.status === 'verified_inside') return; // Sudah sukses diverifikasi

                const interval = setInterval(() => {
                    fetch('/api/gps/session/' + session.token)
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.found) {
                                if (data.status === 'verified_inside' || data.status === 'verified_outside') {
                                    setSession(prev => Object.assign({}, prev, data));
                                    if (data.status === 'verified_inside') {
                                        const verifiedPayload = {
                                            is_inside: true,
                                            distance: data.distance_meters,
                                            device: data.device_info || 'Perangkat HP Terverifikasi',
                                            time: data.verified_at || new Date().toISOString()
                                        };
                                        try {
                                            localStorage.setItem('kantin_gps_verified', JSON.stringify(verifiedPayload));
                                        } catch(e) {}
                                        if (onVerified) onVerified(verifiedPayload);
                                    }
                                }
                            }
                        })
                        .catch(() => {});
                    setPollCount(c => c + 1);
                }, 2000);

                return () => clearInterval(interval);
            }, [isOpen, session]);

            // Deteksi langsung menggunakan browser GPS perangkat saat ini
            const handleDirectDetect = () => {
                if (!navigator.geolocation) {
                    setErrorMsg('Browser atau perangkat ini tidak mendukung sensor GPS Geolocation.');
                    return;
                }

                setIsDirectVerifying(true);
                setErrorMsg('');
                setDirectResult(null);

                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        const coords = pos.coords;
                        sendVerifyLocation(coords.latitude, coords.longitude, coords.accuracy, 'Browser GPS Langsung');
                    },
                    (err) => {
                        setIsDirectVerifying(false);
                        let msg = 'Izin lokasi GPS ditolak oleh pengguna atau tidak dapat diakses.';
                        if (err.code === 1) msg = 'Izin akses GPS ditolak. Silakan izinkan akses lokasi pada browser Anda atau gunakan fitur Scan QR dari HP.';
                        else if (err.code === 2) msg = 'Sinyal GPS tidak ditemukan. Pastikan lokasi perangkat Anda aktif.';
                        else if (err.code === 3) msg = 'Waktu permintaan GPS habis. Coba lagi atau gunakan mode simulasi.';
                        setErrorMsg(msg);
                    },
                    { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                );
            };

            // Kirim data koordinat ke backend
            const sendVerifyLocation = (lat, lng, accuracy, deviceInfo) => {
                fetch('/api/gps/verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        token: session ? session.token : null,
                        latitude: lat,
                        longitude: lng,
                        accuracy: accuracy || 10,
                        device_info: deviceInfo,
                        student_name: currentStudentName || 'Siswa',
                        user_id: currentUserId || null
                    })
                })
                .then(res => res.json())
                .then(data => {
                    setIsDirectVerifying(false);
                    if (data.success) {
                        setDirectResult(data);
                        if (session) {
                            setSession(prev => Object.assign({}, prev, {
                                status: data.status,
                                is_inside_school: data.is_inside_school,
                                distance_meters: data.distance_meters,
                                device_info: deviceInfo
                            }));
                        }
                        if (data.is_inside_school) {
                            const verifiedPayload = {
                                is_inside: true,
                                distance: data.distance_meters,
                                device: deviceInfo,
                                time: data.verified_at
                            };
                            try {
                                localStorage.setItem('kantin_gps_verified', JSON.stringify(verifiedPayload));
                            } catch(e) {}
                            if (onVerified) onVerified(verifiedPayload);
                        }
                    } else {
                        setErrorMsg(data.message || 'Gagal memverifikasi koordinat lokasi.');
                    }
                })
                .catch(() => {
                    setIsDirectVerifying(false);
                    setErrorMsg('Gagal terhubung dengan server untuk verifikasi GPS.');
                });
            };

            // Simulasi untuk pengujian lokasi (SMKN 1 Ciomas vs Luar Sekolah)
            const handleSimulate = (inside) => {
                setIsDirectVerifying(true);
                setErrorMsg('');
                const lat = inside ? -6.586080 : -6.608000;
                const lng = inside ? 106.758530 : 106.769000;
                const dev = inside ? 'Simulasi: Area SMKN 1 Ciomas' : 'Simulasi: Luar Sekolah (Ciomas)';
                sendVerifyLocation(lat, lng, 5, dev);
            };

            const copyVerifyLink = () => {
                if (!session || !session.verify_url) return;
                navigator.clipboard.writeText(session.verify_url).then(() => {
                    setCopied(true);
                    setTimeout(() => setCopied(false), 2500);
                });
            };

            if (!isOpen) return null;

            const isVerifiedSuccess = (session && session.status === 'verified_inside') || (directResult && directResult.is_inside_school);

            return (
                <div className="fixed inset-0 z-[99995] bg-slate-950/75 backdrop-blur-sm flex items-center justify-center p-3 sm:p-5 overflow-y-auto animate-page-enter">
                    <div className="bg-white rounded-3xl max-w-xl w-full shadow-2xl border border-slate-100 overflow-hidden my-auto max-h-[94vh] flex flex-col">
                        {/* Header Modal */}
                        <div className="bg-gradient-to-r from-emerald-700 via-teal-700 to-emerald-900 p-5 sm:p-6 text-white relative shrink-0">
                            <button 
                                onClick={onClose} 
                                className="absolute top-5 right-5 w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center font-bold transition-all text-sm"
                            >
                                ✕
                            </button>
                            <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 text-white text-[11px] font-bold uppercase tracking-wider mb-2">
                                GEOFENCING SMKN 1 CIOMAS
                            </div>
                            <h3 className="text-xl sm:text-2xl font-black text-white">Deteksi Kehadiran Siswa</h3>
                            <p className="text-emerald-100 text-xs sm:text-sm mt-0.5">
                                Verifikasi bahwa Anda sedang berada di SMKN 1 Ciomas menggunakan sensor GPS dari HP atau perangkat lain.
                            </p>
                        </div>

                        {/* Navigasi Tab */}
                        <div className="flex border-b border-slate-200 bg-slate-50 px-4 pt-2 gap-1 sm:gap-2 shrink-0 overflow-x-auto">
                            <button 
                                type="button" 
                                onClick={() => setActiveTab('cross_device')} 
                                className={`px-4 py-2.5 font-bold text-xs sm:text-sm rounded-t-xl transition-all whitespace-nowrap flex items-center gap-1.5 ${
                                    activeTab === 'cross_device' 
                                        ? 'bg-white text-emerald-700 border-t-2 border-l border-r border-slate-200 shadow-xs' 
                                        : 'text-slate-500 hover:text-slate-800'
                                }`}
                            >
                                Scan dari HP (Perangkat Berbeda)
                            </button>
                            <button 
                                type="button" 
                                onClick={() => setActiveTab('direct')} 
                                className={`px-4 py-2.5 font-bold text-xs sm:text-sm rounded-t-xl transition-all whitespace-nowrap flex items-center gap-1.5 ${
                                    activeTab === 'direct' 
                                        ? 'bg-white text-emerald-700 border-t-2 border-l border-r border-slate-200 shadow-xs' 
                                        : 'text-slate-500 hover:text-slate-800'
                                }`}
                            >
                                GPS Perangkat Ini
                            </button>
                            <button 
                                type="button" 
                                onClick={() => setActiveTab('simulation')} 
                                className={`px-3 py-2.5 font-bold text-xs sm:text-sm rounded-t-xl transition-all whitespace-nowrap flex items-center gap-1.5 ${
                                    activeTab === 'simulation' 
                                        ? 'bg-white text-purple-700 border-t-2 border-l border-r border-slate-200 shadow-xs' 
                                        : 'text-slate-500 hover:text-purple-600'
                                }`}
                            >
                                Uji Coba / Simulasi
                            </button>
                        </div>

                        {/* Konten Scrollable */}
                        <div className="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1">
                            {errorMsg && (
                                <div className="p-3 bg-red-50 border border-red-200 rounded-2xl text-red-700 text-xs font-bold flex items-center gap-2 animate-scale-up">
                                    <span>[Peringatan]</span> <span>{errorMsg}</span>
                                </div>
                            )}

                            {/* Status Sukses Banner */}
                            {isVerifiedSuccess && (
                                <div className="p-4 bg-emerald-50 border-2 border-emerald-400 rounded-2xl flex items-start gap-3.5 animate-scale-up">
                                    <div className="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-xl shrink-0">
                                        ✓
                                    </div>
                                    <div className="flex-1">
                                        <div className="flex items-center gap-2">
                                            <span className="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-emerald-200 text-emerald-900">
                                                TERVERIFIKASI
                                            </span>
                                            <span className="text-xs text-emerald-700 font-bold">SMKN 1 Ciomas</span>
                                        </div>
                                        <p className="font-extrabold text-slate-800 text-sm mt-1">
                                            Siswa Terdeteksi Berada di SMKN 1 Ciomas!
                                        </p>
                                        <p className="text-xs text-slate-600 font-medium mt-0.5">
                                            Jarak: <span className="font-bold text-emerald-700">{session && session.distance_meters !== null ? session.distance_meters : (directResult ? directResult.distance_meters : 20)} meter</span> dari titik sekolah (Radius aman: 350m).
                                        </p>
                                        <p className="text-[11px] text-slate-500 mt-1">
                                            Perangkat: {session && session.device_info ? session.device_info : (directResult ? directResult.device_info : 'HP Siswa')}
                                        </p>
                                    </div>
                                </div>
                            )}

                            {/* TAB 1: VERIFIKASI VIA PERANGKAT BERBEDA (HP) */}
                            {activeTab === 'cross_device' && (
                                <div className="space-y-4 animate-page-enter">
                                    <div className="text-center bg-slate-50 p-4 rounded-2xl border border-slate-200">
                                        <p className="text-xs text-slate-600 font-semibold mb-3">
                                            Buka kamera HP atau aplikasi scanner di smartphone Anda, lalu scan QR Code di bawah untuk memverifikasi GPS dari HP:
                                        </p>

                                        {/* QR Code Container */}
                                        <div className="inline-block p-3 bg-white rounded-2xl border-2 border-slate-200 shadow-md relative group">
                                            {session && session.verify_url ? (
                                                <img 
                                                    src={`https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(session.verify_url)}&margin=2&color=0f172a`} 
                                                    alt="QR Code GPS"
                                                    className="w-44 h-44 object-contain mx-auto"
                                                />
                                            ) : (
                                                <div className="w-44 h-44 flex items-center justify-center text-slate-400 font-bold text-xs">
                                                    {isCreatingSession ? 'Membuat QR...' : 'Memuat Sesi...'}
                                                </div>
                                            )}
                                        </div>

                                        {session && session.token && (
                                            <div className="mt-3">
                                                <p className="text-[10px] font-black text-slate-400 uppercase tracking-wider">Kode Sesi Perangkat</p>
                                                <div className="inline-block bg-slate-900 text-emerald-400 font-mono font-black text-sm px-3.5 py-1 rounded-xl tracking-wider mt-0.5">
                                                    {session.token}
                                                </div>
                                            </div>
                                        )}
                                    </div>

                                    {/* Action Buttons for Link */}
                                    {session && session.verify_url && (
                                        <div className="flex flex-col sm:flex-row gap-2">
                                            <button 
                                                type="button" 
                                                onClick={copyVerifyLink}
                                                className="flex-1 py-2.5 px-4 rounded-xl border-2 border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/50 text-slate-700 font-bold text-xs transition-all flex items-center justify-center gap-2"
                                            >
                                                {copied ? 'Link Berhasil Disalin!' : 'Salin Link Buka di HP'}
                                            </button>
                                            <a 
                                                href={session.verify_url} 
                                                target="_blank" 
                                                rel="noreferrer"
                                                className="py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs transition-all flex items-center justify-center gap-1.5 shadow-sm"
                                            >
                                                Buka di Tab Baru
                                            </a>
                                        </div>
                                    )}

                                    {/* Live Polling Status Box */}
                                    <div className="p-3.5 bg-emerald-50/70 border border-emerald-200 rounded-2xl flex items-center justify-between text-xs font-bold text-emerald-900">
                                        <div className="flex items-center gap-2.5">
                                            <span className="relative flex h-3 w-3">
                                                <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                                <span className="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                                            </span>
                                            <span>
                                                {session && session.status === 'verified_inside' 
                                                    ? 'HP Terhubung & Lokasi di SMKN 1 Ciomas Terverifikasi!' 
                                                    : 'Menunggu pemindaian dari HP / perangkat berbeda...'}
                                            </span>
                                        </div>
                                        <button 
                                            type="button" 
                                            onClick={initSession} 
                                            title="Buat sesi baru"
                                            className="text-[11px] text-emerald-700 hover:underline shrink-0"
                                        >
                                            Sesi Baru
                                        </button>
                                    </div>
                                </div>
                            )}

                            {/* TAB 2: DETEKSI LANGSUNG DI PERANGKAT INI */}
                            {activeTab === 'direct' && (
                                <div className="space-y-4 animate-page-enter">
                                    <div className="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-center">
                                        <div className="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-black mx-auto mb-2 shadow-xs">
                                            GPS
                                        </div>
                                        <h4 className="font-extrabold text-slate-800 text-base">Deteksi GPS Browser Langsung</h4>
                                        <p className="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                                            Jika Anda membuka situs ini langsung dari smartphone atau perangkat yang memiliki sensor GPS aktif, klik tombol di bawah untuk mengambil titik koordinat Anda saat ini.
                                        </p>

                                        <button 
                                            type="button"
                                            onClick={handleDirectDetect}
                                            disabled={isDirectVerifying}
                                            className="mt-4 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm rounded-2xl transition-all shadow-md disabled:opacity-50 inline-flex items-center gap-2"
                                        >
                                            {isDirectVerifying ? 'Membaca Sensor GPS...' : 'Ambil Lokasi GPS Saya Sekarang'}
                                        </button>
                                    </div>

                                    {/* Hasil Langsung */}
                                    {directResult && (
                                        <div className={`p-4 rounded-2xl border-2 ${directResult.is_inside_school ? 'bg-emerald-50 border-emerald-300' : 'bg-amber-50 border-amber-300'}`}>
                                            <p className="font-black text-sm text-slate-800 mb-1">{directResult.message}</p>
                                            <div className="grid grid-cols-2 gap-2 text-[11px] text-slate-600 mt-2 font-medium">
                                                <div>Koordinat Siswa: <span className="font-bold text-slate-800">{directResult.student_latitude}, {directResult.student_longitude}</span></div>
                                                <div>Jarak ke SMKN 1 Ciomas: <span className="font-bold text-slate-800">{directResult.distance_meters} m</span></div>
                                                <div>Target Sekolah: <span className="font-bold text-slate-800">SMKN 1 Ciomas</span></div>
                                                <div>Status Geofence: <span className={`font-bold ${directResult.is_inside_school ? 'text-emerald-700' : 'text-amber-700'}`}>{directResult.is_inside_school ? 'DI DALAM AREA' : 'DI LUAR AREA'}</span></div>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            )}

                            {/* TAB 3: MODE SIMULASI / PENGUJIAN */}
                            {activeTab === 'simulation' && (
                                <div className="space-y-4 animate-page-enter">
                                    <div className="p-4 bg-purple-50 rounded-2xl border border-purple-200">
                                        <div className="flex items-center gap-2 text-purple-900 font-extrabold text-sm mb-1">
                                            Mode Pengujian Lokasi (Demo & Presentasi)
                                        </div>
                                        <p className="text-xs text-purple-700 mb-4 leading-relaxed">
                                            Gunakan mode ini untuk menguji bagaimana sistem bereaksi saat siswa terdeteksi di dalam SMKN 1 Ciomas maupun di luar area sekolah tanpa harus berada di lokasi fisik:
                                        </p>

                                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <button 
                                                type="button"
                                                onClick={() => handleSimulate(true)}
                                                className="p-3.5 bg-white border-2 border-emerald-300 hover:border-emerald-500 rounded-2xl text-left transition-all shadow-xs hover:shadow-md group"
                                            >
                                                <div className="text-emerald-700 font-extrabold text-xs mb-1 flex items-center justify-between">
                                                    <span>Di Dalam Sekolah</span>
                                                    <span className="text-[10px] bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full">~20 meter</span>
                                                </div>
                                                <p className="font-bold text-slate-800 text-xs">Simulasi: SMKN 1 Ciomas</p>
                                                <p className="text-[11px] text-slate-500 mt-0.5">Gerbang depan & kantin sekolah</p>
                                            </button>

                                            <button 
                                                type="button"
                                                onClick={() => handleSimulate(false)}
                                                className="p-3.5 bg-white border-2 border-amber-300 hover:border-amber-500 rounded-2xl text-left transition-all shadow-xs hover:shadow-md group"
                                            >
                                                <div className="text-amber-700 font-extrabold text-xs mb-1 flex items-center justify-between">
                                                    <span>Di Luar Sekolah</span>
                                                    <span className="text-[10px] bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full">~2.5 km</span>
                                                </div>
                                                <p className="font-bold text-slate-800 text-xs">Simulasi: Ciomas Indah</p>
                                                <p className="text-[11px] text-slate-500 mt-0.5">Di luar radius aman 350m</p>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* Info Titik Koordinat Resmi SMKN 1 Ciomas */}
                            <div className="p-3 bg-slate-100 rounded-2xl text-[11px] text-slate-500 flex items-center justify-between">
                                <div>
                                    <span className="font-bold text-slate-700">Titik SMKN 1 Ciomas:</span> Lat -6.5860, Lng 106.7585 (Radius: 350m)
                                </div>
                                <span className="font-bold text-emerald-700">Jl. Raya Laladon</span>
                            </div>
                        </div>

                        {/* Footer Modal */}
                        <div className="p-4 bg-slate-50 border-t border-slate-100 flex justify-between items-center shrink-0">
                            <span className="text-[11px] text-slate-400 font-medium">Fast Track Kantin GPS Geofencing</span>
                            <div className="flex gap-2">
                                <button 
                                    type="button"
                                    onClick={onClose} 
                                    className="px-5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold transition-all shadow-sm"
                                >
                                    Selesai & Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            );
        }

        // --- HALAMAN VERIFIKASI GPS DARI PERANGKAT BERBEDA / HP SISWA --- //
        function GpsDeviceVerification({ token, setPath }) {
            const [sessionData, setSessionData] = useState(null);
            const [isLoading, setIsLoading] = useState(true);
            const [isVerifying, setIsVerifying] = useState(false);
            const [result, setResult] = useState(null);
            const [errorMsg, setErrorMsg] = useState('');
            const [manualToken, setManualToken] = useState(token || '');

            useEffect(() => {
                if (token) {
                    fetchSession(token);
                } else {
                    setIsLoading(false);
                }
            }, [token]);

            const fetchSession = (tok) => {
                setIsLoading(true);
                setErrorMsg('');
                fetch('/api/gps/session/' + encodeURIComponent(tok))
                    .then(res => res.json())
                    .then(data => {
                        setIsLoading(false);
                        if (data && data.found) {
                            setSessionData(data);
                            if (data.status === 'verified_inside' || data.status === 'verified_outside') {
                                setResult({
                                    is_inside_school: data.is_inside_school,
                                    distance_meters: data.distance_meters,
                                    message: data.is_inside_school 
                                        ? `Terverifikasi! Siswa berada di SMKN 1 Ciomas (Jarak: ${data.distance_meters}m).`
                                        : `Di luar area: Jarak siswa ${data.distance_meters}m dari SMKN 1 Ciomas.`,
                                    verified_at: data.verified_at,
                                    device_info: data.device_info
                                });
                            }
                        } else {
                            setErrorMsg(data.message || 'Sesi verifikasi tidak ditemukan.');
                        }
                    })
                    .catch(() => {
                        setIsLoading(false);
                        setErrorMsg('Gagal memuat sesi dari server.');
                    });
            };

            const handleDetectGps = () => {
                if (!navigator.geolocation) {
                    setErrorMsg('Browser HP Anda tidak mendukung fitur Geolocation GPS.');
                    return;
                }

                setIsVerifying(true);
                setErrorMsg('');

                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        const coords = pos.coords;
                        const deviceName = `${navigator.userAgent.indexOf('iPhone') !== -1 ? 'iPhone' : (navigator.userAgent.indexOf('Android') !== -1 ? 'Android' : 'HP Siswa')} • Browser Mobile`;
                        submitGpsData(coords.latitude, coords.longitude, coords.accuracy, deviceName);
                    },
                    (err) => {
                        setIsVerifying(false);
                        let msg = 'Izin lokasi GPS ditolak oleh browser HP Anda.';
                        if (err.code === 1) msg = 'Mohon izinkan akses lokasi GPS pada browser smartphone Anda untuk melanjutkan verifikasi.';
                        else if (err.code === 2) msg = 'Sinyal GPS tidak tersedia. Pastikan Lokasi/GPS di HP Anda dalam kondisi AKTIF.';
                        else if (err.code === 3) msg = 'Waktu permintaan lokasi habis. Coba lagi atau gunakan mode simulasi.';
                        setErrorMsg(msg);
                    },
                    { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                );
            };

            const submitGpsData = (lat, lng, accuracy, deviceInfo) => {
                const activeTok = token || manualToken || (sessionData ? sessionData.token : null);
                const sName = sessionData ? sessionData.student_name : 'Siswa SMKN 1 Ciomas';
                const uId = sessionData ? sessionData.user_id : null;

                fetch('/api/gps/verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        token: activeTok,
                        latitude: lat,
                        longitude: lng,
                        accuracy: accuracy || 10,
                        device_info: deviceInfo,
                        student_name: sName,
                        user_id: uId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    setIsVerifying(false);
                    if (data.success) {
                        setResult(data);
                        setErrorMsg('');
                    } else {
                        setErrorMsg(data.message || 'Gagal memverifikasi lokasi.');
                    }
                })
                .catch(() => {
                    setIsVerifying(false);
                    setErrorMsg('Terjadi kesalahan jaringan saat mengirim koordinat GPS.');
                });
            };

            const handleSimulate = (inside) => {
                setIsVerifying(true);
                setErrorMsg('');
                const lat = inside ? -6.586080 : -6.608000;
                const lng = inside ? 106.758530 : 106.769000;
                const dev = inside ? 'Simulasi HP: Di SMKN 1 Ciomas' : 'Simulasi HP: Di Luar Sekolah';
                submitGpsData(lat, lng, 5, dev);
            };

            return (
                <div className="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950 text-white p-4 sm:p-6 flex flex-col justify-between animate-page-enter">
                    {/* Top Branding Bar */}
                    <div className="max-w-md mx-auto w-full pt-4 pb-2 text-center">
                        <div className="inline-flex items-center gap-2 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-3.5 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider mb-2">
                            FAST TRACK VERIFIKASI LOKASI
                        </div>
                        <h1 className="text-2xl sm:text-3xl font-black text-white tracking-tight">
                            SMKN 1 Ciomas
                        </h1>
                        <p className="text-slate-400 text-xs mt-1">
                            Sistem Deteksi Kehadiran Siswa di Area Sekolah (Kantin Pintar)
                        </p>
                    </div>

                    {/* Main Card */}
                    <div className="max-w-md mx-auto w-full my-auto">
                        <div className="bg-white/95 backdrop-blur-md text-slate-800 rounded-3xl p-6 sm:p-7 shadow-2xl border border-white/20">
                            {/* Icon Radar */}
                            <div className="flex justify-center mb-5">
                                <div className="relative">
                                    <div className="w-20 h-20 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-400 p-0.5 shadow-xl shadow-emerald-500/20 flex items-center justify-center">
                                        <div className="w-full h-full bg-slate-950 rounded-[0.9rem] flex items-center justify-center text-sm font-black text-emerald-400">
                                            {result && result.is_inside_school ? 'OK' : (result ? '!' : 'GPS')}
                                        </div>
                                    </div>
                                    <span className="absolute -top-1 -right-1 flex h-4 w-4">
                                        <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span className="relative inline-flex rounded-full h-4 w-4 bg-emerald-500 border-2 border-white"></span>
                                    </span>
                                </div>
                            </div>

                            {/* Session Information */}
                            {sessionData && (
                                <div className="bg-slate-50 border border-slate-200 rounded-2xl p-3.5 mb-5 text-center">
                                    <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Siswa</p>
                                    <p className="font-extrabold text-slate-900 text-base">{sessionData.student_name}</p>
                                    <div className="mt-1 flex items-center justify-center gap-1.5 text-xs text-slate-500">
                                        <span>Kode Sesi:</span>
                                        <span className="font-mono font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">{sessionData.token}</span>
                                    </div>
                                </div>
                            )}

                            {!token && !sessionData && (
                                <div className="mb-4">
                                    <label className="block text-xs font-bold text-slate-600 mb-1.5">Masukkan Kode Sesi (jika ada):</label>
                                    <div className="flex gap-2">
                                        <input 
                                            type="text" 
                                            value={manualToken} 
                                            onChange={e => setManualToken(e.target.value)} 
                                            placeholder="Contoh: GPS-123-ABCD" 
                                            className="flex-1 border-2 border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 uppercase focus:border-emerald-500 focus:outline-none"
                                        />
                                        <button 
                                            type="button" 
                                            onClick={() => fetchSession(manualToken)} 
                                            className="px-3.5 py-2 bg-emerald-600 text-white font-bold text-xs rounded-xl hover:bg-emerald-700"
                                        >
                                            Cari
                                        </button>
                                    </div>
                                </div>
                            )}

                            {errorMsg && (
                                <div className="p-3 bg-red-50 border border-red-200 rounded-2xl text-red-700 text-xs font-bold mb-4 flex items-center gap-2">
                                    <span>[Peringatan]</span> <span>{errorMsg}</span>
                                </div>
                            )}

                            {/* Verification Result */}
                            {result ? (
                                <div className={`p-4 rounded-2xl border-2 mb-5 animate-scale-up ${
                                    result.is_inside_school ? 'bg-emerald-50 border-emerald-400 text-emerald-950' : 'bg-amber-50 border-amber-400 text-amber-950'
                                }`}>
                                    <div className="flex items-center gap-2 font-black text-sm mb-1">
                                        <span>{result.is_inside_school ? 'TERVERIFIKASI DI SMKN 1 CIOMAS' : 'DI LUAR AREA SEKOLAH'}</span>
                                    </div>
                                    <p className="text-xs font-semibold leading-relaxed mb-2">{result.message}</p>
                                    <div className="bg-white/80 rounded-xl p-2.5 text-[11px] font-medium space-y-1 text-slate-700 border border-slate-200">
                                        <div className="flex justify-between">
                                            <span>Jarak ke Sekolah:</span>
                                            <span className="font-extrabold text-slate-900">{result.distance_meters} meter</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span>Radius Aman:</span>
                                            <span className="font-bold">Maksimal 350 meter</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span>Waktu Verifikasi:</span>
                                            <span className="font-bold">{result.verified_at || new Date().toLocaleTimeString('id-ID')}</span>
                                        </div>
                                    </div>
                                    {result.is_inside_school && (
                                        <p className="text-[11px] text-emerald-700 font-bold mt-2 text-center bg-emerald-100/70 p-2 rounded-xl">
                                            ✓ Layar perangkat utama (komputer/laptop) Anda sekarang telah otomatis terverifikasi!
                                        </p>
                                    )}
                                </div>
                            ) : null}

                            {/* Main Button: Activate GPS */}
                            <div className="space-y-3">
                                <button 
                                    type="button"
                                    onClick={handleDetectGps}
                                    disabled={isVerifying}
                                    className="w-full py-4 bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 text-white font-black text-sm sm:text-base rounded-2xl shadow-lg shadow-emerald-500/30 transition-all flex items-center justify-center gap-2 disabled:opacity-50"
                                >
                                    {isVerifying ? (
                                        <span className="inline-flex items-center gap-2">
                                            Membaca Sensor GPS HP...
                                        </span>
                                    ) : (
                                        <span className="inline-flex items-center gap-2">
                                            Aktifkan GPS & Verifikasi Lokasi Saya
                                        </span>
                                    )}
                                </button>

                                {/* Demo Simulation Buttons for Phone */}
                                <div className="pt-2 border-t border-slate-100">
                                    <p className="text-[10px] font-bold text-slate-400 text-center uppercase tracking-wider mb-2">
                                        Opsi Pengujian / Demo Lokasi:
                                    </p>
                                    <div className="grid grid-cols-2 gap-2">
                                        <button 
                                            type="button" 
                                            onClick={() => handleSimulate(true)} 
                                            className="p-2 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-xl transition-all"
                                        >
                                            Simulasi: Di SMKN 1 Ciomas
                                        </button>
                                        <button 
                                            type="button" 
                                            onClick={() => handleSimulate(false)} 
                                            className="p-2 bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-800 text-xs font-bold rounded-xl transition-all"
                                        >
                                            Simulasi: Di Luar Sekolah
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Navigation link back to portal */}
                        <div className="text-center mt-5">
                            <button 
                                type="button" 
                                onClick={() => setPath('/student/dashboard')} 
                                className="text-emerald-400 hover:text-emerald-300 font-bold text-xs underline"
                            >
                                ← Buka Portal Kantin Siswa
                            </button>
                        </div>
                    </div>

                    {/* Bottom School Info */}
                    <div className="max-w-md mx-auto w-full text-center pb-2 text-slate-400 text-[11px]">
                        SMKN 1 Ciomas • Jl. Raya Laladon, Ciomas, Bogor
                    </div>
                </div>
            );
        }

        function Landing({ setPath }) {
            const [settings, setSettings] = useState(null);
            const [myOrderStatus, setMyOrderStatus] = useState(null);
            const [showCheckModal, setShowCheckModal] = useState(false);

            const loadLiveStatus = () => {
                fetch('/api/settings')
                    .then(res => res.json())
                    .then(data => {
                        setSettings(data);
                    })
                    .catch(() => {});

                try {
                    const saved = localStorage.getItem('kantin_my_last_order');
                    if (saved) {
                        const parsed = JSON.parse(saved);
                        if (parsed && parsed.id && (Date.now() - (parsed.timestamp || 0) < 12 * 3600 * 1000)) {
                            fetch('/api/order-status/' + parsed.id)
                                .then(res => res.json())
                                .then(orderData => {
                                    if (orderData && orderData.found && orderData.status !== 'completed') {
                                        setMyOrderStatus(orderData);
                                    } else {
                                        setMyOrderStatus(null);
                                    }
                                })
                                .catch(() => {});
                        }
                    }
                } catch (e) {}
            };

            const [activeSlide, setActiveSlide] = useState(0);
            const [isPaused, setIsPaused] = useState(false);

            useEffect(() => {
                loadLiveStatus();
                const timer = setInterval(loadLiveStatus, 3000);
                return () => clearInterval(timer);
            }, []);

            const activeOrders = (settings && Array.isArray(settings.active_orders)) ? settings.active_orders : [];
            const canteenQueues = (settings && Array.isArray(settings.canteen_queues)) ? settings.canteen_queues : [];

            // Carousel auto slide for active orders
            useEffect(() => {
                if (isPaused || activeOrders.length <= 1) return;
                const slideTimer = setInterval(() => {
                    setActiveSlide(prev => (prev + 1) % activeOrders.length);
                }, 3500);
                return () => clearInterval(slideTimer);
            }, [activeOrders.length, isPaused]);

            const currentOrderSlide = activeOrders.length > 0 ? activeOrders[activeSlide % activeOrders.length] : null;

            const displayInfo = myOrderStatus ? {
                title: myOrderStatus.status_text,
                subtitle: myOrderStatus.subtext,
                badge: myOrderStatus.badge || 'PESANAN ANDA',
                type: myOrderStatus.type || 'ready',
                isPersonal: true
            } : currentOrderSlide ? {
                title: `Antrean ${currentOrderSlide.order_code} ${currentOrderSlide.status === 'ready' ? 'Siap Diambil di ' + currentOrderSlide.canteen_name : 'Sedang Dimasak di ' + currentOrderSlide.canteen_name}!`,
                subtitle: `Atas nama ${currentOrderSlide.customer_name} • ${currentOrderSlide.portions} Porsi • ${currentOrderSlide.instruction}`,
                badge: currentOrderSlide.status === 'ready' ? 'Siap Diambil' : (currentOrderSlide.status === 'cooking' ? 'Sedang Dimasak' : 'Dalam Antrean'),
                type: currentOrderSlide.status,
                activeCount: (settings && settings.active_queue_count) || 0,
                isPersonal: false
            } : {
                title: (settings && (settings.promo_status || settings.auto_status)) || 'Loket Kantin 1 - 7 Buka & Siap Melayani',
                subtitle: (settings && settings.auto_subtext) || 'Pesan sekarang untuk jajan tanpa desak-desakan',
                badge: (settings && settings.status_badge) || 'LOKET BUKA',
                type: (settings && settings.status_type) || 'idle',
                activeCount: (settings && settings.active_queue_count) || 0,
                isPersonal: false
            };

            return (
                <div className="min-h-screen">
                    {/* Modal Cek Pesanan Saya */}
                    <CheckOrderModal isOpen={showCheckModal} onClose={() => setShowCheckModal(false)} />

                    <nav className="bg-white/80 backdrop-blur-md border-b border-slate-100 p-4 sticky top-0 z-50">
                        <div className="max-w-7xl mx-auto flex justify-between items-center gap-3">
                            <div className="flex items-center space-x-3 shrink-0">
                                <div className="w-10 h-10 bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center shadow-lg shadow-green-200 text-white font-bold text-xl">K</div>
                                <span className="text-xl font-extrabold text-slate-800 tracking-tight">Kantin Pintar</span>
                            </div>
                            <div className="flex items-center space-x-2 sm:space-x-3 flex-wrap justify-end">
                                <button 
                                    type="button"
                                    onClick={() => setShowCheckModal(true)} 
                                    className="py-2 px-3 sm:px-4 text-xs sm:text-sm font-extrabold rounded-full border-2 border-emerald-300 text-emerald-800 bg-emerald-50/60 hover:bg-emerald-100 transition-all flex items-center gap-1.5 shadow-xs"
                                >
                                    Papan Antrean & Cek
                                </button>
                                <Button onClick={() => setPath('/student/dashboard')} className="py-2 px-3 sm:px-4 text-xs sm:text-sm">
                                    Masuk untuk Jajan
                                </Button>
                                <Button variant="outline" onClick={() => setPath('/login/admin')} className="py-2 px-3 sm:px-4 text-xs sm:text-sm">
                                    Staff Kantin (1-7)
                                </Button>
                            </div>
                        </div>
                    </nav>

                    <main className="max-w-7xl mx-auto p-4 py-8 sm:py-14 grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
                        <div className="space-y-6 sm:space-y-8">
                            <div className="flex items-center gap-2 flex-wrap">
                                <div className="inline-block bg-green-50 text-green-700 border border-green-100 px-4 py-1.5 rounded-full font-bold text-sm shadow-sm">
                                    Jajan Cepat Tanpa Antre di Sekolah
                                </div>
                                <div className="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-800 border border-emerald-200 px-3.5 py-1.5 rounded-full font-extrabold text-xs shadow-xs">
                                    <span className="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                                    <span>SMKN 1 Ciomas • Stand Kantin 1 s/d 7</span>
                                </div>
                            </div>
                            <h1 className="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-tight text-slate-900 tracking-tight">
                                Jajan di Kantin Lebih <span className="text-transparent bg-clip-text bg-gradient-to-r from-green-500 to-emerald-600">Cepat</span> & <span className="text-transparent bg-clip-text bg-gradient-to-r from-blue-500 to-indigo-600">Mudah</span>
                            </h1>
                            <p className="text-base sm:text-lg text-slate-500 font-medium max-w-lg leading-relaxed">
                                Pesan menu bergizi langsung dari kelas sebelum bel istirahat. Sistem otomatis memisahkan pesanan ke loket Kantin 1 sampai 7 dengan nomor antrean tersendiri.
                            </p>
                            <div className="flex flex-wrap gap-3 sm:gap-4 items-center">
                                <Button onClick={() => setPath('/student/dashboard')} className="px-6 py-3 sm:px-8 sm:py-4 text-sm sm:text-lg shadow-lg shadow-emerald-500/20">
                                    Mulai Pesan Sekarang →
                                </Button>
                                <button 
                                    type="button"
                                    onClick={() => setShowCheckModal(true)} 
                                    className="px-5 py-3 sm:px-6 sm:py-4 rounded-full border-2 border-slate-300 hover:border-emerald-500 hover:text-emerald-700 font-extrabold text-xs sm:text-base text-slate-700 transition-all flex items-center gap-2 bg-white hover:bg-emerald-50/50 shadow-xs"
                                >
                                    Buka Papan Antrean
                                </button>
                            </div>
                        </div>

                        <div className="relative">
                            <div className="w-full min-h-[480px] sm:h-[520px] bg-slate-100 rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col justify-end relative border-4 sm:border-8 border-white group">
                                <img src={settings && settings.promo_image ? `/storage/${settings.promo_image}` : "https://images.unsplash.com/photo-1543339308-43e59d6b73a6?auto=format&fit=crop&q=80"} className="absolute inset-0 w-full h-full object-cover" />
                                <div className="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/40 to-transparent"></div>
                                
                                {/* Widget Status Pesanan Otomatis Interaktif & Anti-Crash */}
                                <div 
                                    onMouseEnter={() => setIsPaused(true)}
                                    onMouseLeave={() => setIsPaused(false)}
                                    className="relative m-3 sm:m-5 bg-white/95 backdrop-blur-md rounded-2xl p-3.5 sm:p-5 shadow-2xl border border-white/70 transition-all duration-300 hover:shadow-emerald-500/20"
                                >
                                    {/* Header Bar */}
                                    <div className="flex items-center justify-between gap-2 mb-2 pb-2 border-b border-slate-100">
                                        <div className="flex items-center gap-1.5 flex-wrap">
                                            <span className={`inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border ${
                                                displayInfo.type === 'ready' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' :
                                                displayInfo.type === 'cooking' ? 'bg-amber-100 text-amber-800 border-amber-300' :
                                                'bg-slate-100 text-slate-700 border-slate-200'
                                            }`}>
                                                <span className={`w-1.5 h-1.5 rounded-full ${displayInfo.type === 'ready' ? 'bg-emerald-500 animate-ping' : 'bg-emerald-500'}`}></span>
                                                {displayInfo.badge}
                                            </span>

                                            {displayInfo.activeCount > 0 && !displayInfo.isPersonal && (
                                                <span className="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">
                                                    {displayInfo.activeCount} antrean aktif
                                                </span>
                                            )}
                                        </div>

                                        <button 
                                            type="button"
                                            onClick={() => setShowCheckModal(true)}
                                            className="text-[11px] font-extrabold text-emerald-700 hover:text-emerald-800 flex items-center gap-1 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200 hover:bg-emerald-100 transition-all shrink-0 cursor-pointer"
                                        >
                                            Papan Antrean
                                        </button>
                                    </div>

                                    {/* Content Bar with Slide Transition */}
                                    <div 
                                        onClick={() => setShowCheckModal(true)} 
                                        className="flex items-center gap-3 sm:gap-4 cursor-pointer group/card"
                                    >
                                        <div className={`w-12 h-12 sm:w-14 sm:h-14 rounded-2xl flex justify-center items-center text-2xl shadow-md shrink-0 relative transition-transform group-hover/card:scale-105 ${
                                            displayInfo.type === 'ready' ? 'bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-emerald-500/40' :
                                            displayInfo.type === 'cooking' ? 'bg-gradient-to-br from-amber-500 to-orange-500 text-white shadow-amber-500/40' :
                                            'bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-emerald-500/30'
                                        }`}>
                                            <span className="text-xs font-black tracking-wider">
                                                {displayInfo.type === 'ready' ? 'SIAP' : displayInfo.type === 'cooking' ? 'MASAK' : 'STAND'}
                                            </span>
                                            {displayInfo.type === 'ready' && (
                                                <span className="absolute -top-1 -right-1 flex h-3.5 w-3.5">
                                                    <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                                    <span className="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500 border border-white"></span>
                                                </span>
                                            )}
                                        </div>

                                        <div className="flex-1 min-w-0">
                                            <p className="font-black text-slate-800 text-sm sm:text-base tracking-tight leading-snug truncate group-hover/card:text-emerald-700 transition-colors">
                                                {displayInfo.title}
                                            </p>
                                            <p className="text-xs text-slate-500 font-medium truncate mt-0.5">
                                                {displayInfo.subtitle}
                                            </p>
                                        </div>

                                        {/* Slider Navigation Dots */}
                                        {activeOrders.length > 1 && (
                                            <div className="flex items-center gap-1 shrink-0 pl-2">
                                                {activeOrders.slice(0, 5).map((_, idx) => (
                                                    <span 
                                                        key={idx} 
                                                        className={`w-1.5 h-1.5 rounded-full transition-all ${idx === (activeSlide % 5) ? 'w-4 bg-emerald-600' : 'bg-slate-300'}`}
                                                    />
                                                ))}
                                            </div>
                                        )}
                                    </div>

                                    {/* Canteen Pills (Kantin 1 s/d 7) */}
                                    <div className="mt-3 pt-2.5 border-t border-slate-100 flex items-center gap-1.5 overflow-x-auto text-[10px] font-bold text-slate-600 scrollbar-none">
                                        <span className="text-emerald-700 font-black shrink-0">Loket:</span>
                                        {[
                                            { id: 1, name: 'Kantin 1', code: 'K1', count: (canteenQueues.find(cq => cq.canteen_id === 1) && canteenQueues.find(cq => cq.canteen_id === 1).ready_count) || 0 },
                                            { id: 2, name: 'Kantin 2', code: 'K2', count: (canteenQueues.find(cq => cq.canteen_id === 2) && canteenQueues.find(cq => cq.canteen_id === 2).ready_count) || 0 },
                                            { id: 3, name: 'Kantin 3', code: 'K3', count: (canteenQueues.find(cq => cq.canteen_id === 3) && canteenQueues.find(cq => cq.canteen_id === 3).ready_count) || 0 },
                                            { id: 4, name: 'Kantin 4', code: 'K4', count: (canteenQueues.find(cq => cq.canteen_id === 4) && canteenQueues.find(cq => cq.canteen_id === 4).ready_count) || 0 },
                                            { id: 5, name: 'Kantin 5', code: 'K5', count: (canteenQueues.find(cq => cq.canteen_id === 5) && canteenQueues.find(cq => cq.canteen_id === 5).ready_count) || 0 },
                                            { id: 6, name: 'Kantin 6', code: 'K6', count: (canteenQueues.find(cq => cq.canteen_id === 6) && canteenQueues.find(cq => cq.canteen_id === 6).ready_count) || 0 },
                                            { id: 7, name: 'Kantin 7', code: 'K7', count: (canteenQueues.find(cq => cq.canteen_id === 7) && canteenQueues.find(cq => cq.canteen_id === 7).ready_count) || 0 },
                                        ].map(c => (
                                            <button 
                                                key={c.id}
                                                type="button"
                                                onClick={() => setShowCheckModal(true)}
                                                className={`px-2 py-0.5 rounded-full border whitespace-nowrap shrink-0 transition-all flex items-center gap-1 ${c.count > 0 ? 'bg-emerald-100 text-emerald-900 border-emerald-300 font-black' : 'bg-slate-50 text-slate-600 border-slate-200'}`}
                                            >
                                                <span className="font-extrabold">{c.code}</span> {c.name} {c.count > 0 && <span className="bg-emerald-600 text-white px-1 py-0.2 rounded-full text-[9px]">{c.count}</span>}
                                            </button>
                                        ))}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </main>

                    <section className="bg-white border-t-2 border-slate-200 py-16">
                        <div className="max-w-7xl mx-auto px-4">
                            <div className="text-center mb-12">
                                <span className="bg-slate-100 text-slate-600 font-bold px-4 py-1 rounded-full text-sm">Keunggulan Utama</span>
                                <h2 className="text-3xl font-extrabold mt-4 text-dark">Kenapa Harus Pakai E-Kantin?</h2>
                            </div>
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                                <Card>
                                    <div className="w-12 h-12 border-2 border-green-200 bg-green-50 text-green-700 font-black rounded-xl flex items-center justify-center text-sm mb-4">01</div>
                                    <h3 className="text-xl font-bold mb-2">Pesan Online Praktis</h3>
                                    <p className="text-slate-600">Pilih menu sarapan atau makan siang bergizi langsung dari kelas atau rumah sebelum bel istirahat berbunyi.</p>
                                </Card>
                                <Card>
                                    <div className="w-12 h-12 border-2 border-blue-200 bg-blue-50 text-blue-700 font-black rounded-xl flex items-center justify-center text-sm mb-4">02</div>
                                    <h3 className="text-xl font-bold mb-2">Bayar Mudah Cashless</h3>
                                    <p className="text-slate-600">Dukung pembayaran non-tunai lewat Transfer Bank atau scan QRIS secara instan.</p>
                                </Card>
                                <Card>
                                    <div className="w-12 h-12 border-2 border-amber-200 bg-amber-50 text-amber-700 font-black rounded-xl flex items-center justify-center text-sm mb-4">03</div>
                                    <h3 className="text-xl font-bold mb-2">Ambil Cepat Tanpa Antre</h3>
                                    <p className="text-slate-600">Tiba di kantin tinggal sebutkan nomor antrean di loket "Fast Track". Makanan dalam kotak hangat sudah siap.</p>
                                </Card>
                            </div>
                        </div>
                    </section>
                </div>
            );
        }

        function LoginAdmin({ setPath }) {
            const [email, setEmail] = useState('');
            const [password, setPassword] = useState('');
            const [error, setError] = useState('');
            const [isLoading, setIsLoading] = useState(false);

            const staffPresets = [
                { id: 1, name: 'Kantin 1', desc: 'Aneka Nasi', email: 'kantin1@kantin.com', pass: 'password', icon: 'K1' },
                { id: 2, name: 'Kantin 2', desc: 'Sarapan', email: 'kantin2@kantin.com', pass: 'password', icon: 'K2' },
                { id: 3, name: 'Kantin 3', desc: 'Minuman', email: 'kantin3@kantin.com', pass: 'password', icon: 'K3' },
                { id: 4, name: 'Kantin 4', desc: 'Cemilan', email: 'kantin4@kantin.com', pass: 'password', icon: 'K4' },
                { id: 5, name: 'Kantin 5', desc: 'Bakso & Soto', email: 'kantin5@kantin.com', pass: 'password', icon: 'K5' },
                { id: 6, name: 'Kantin 6', desc: 'Western', email: 'kantin6@kantin.com', pass: 'password', icon: 'K6' },
                { id: 7, name: 'Kantin 7', desc: 'Makan Siang', email: 'kantin7@kantin.com', pass: 'password', icon: 'K7' },
                { id: 0, name: 'Super Admin', desc: 'Semua Kantin', email: 'admin@kantin.com', pass: 'password', icon: 'SA' },
            ];

            const submitLogin = (loginEmail, loginPass) => {
                setError('');
                setIsLoading(true);
                
                fetch('/api/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email: loginEmail, password: loginPass })
                })
                .then(response => response.json())
                .then(data => {
                    setIsLoading(false);
                    if (data.success && (data.role === 'admin' || data.role === 'canteen_staff')) {
                        setPath('/admin/dashboard');
                    } else if (data.success) {
                        setError('Akses ditolak. Halaman ini khusus Petugas & Staff Kantin.');
                    } else {
                        setError(data.message || 'Login gagal. Periksa kembali username/password.');
                    }
                })
                .catch(err => {
                    setIsLoading(false);
                    setError('Terjadi kesalahan jaringan.');
                });
            };

            const handleLogin = (e) => {
                e.preventDefault();
                submitLogin(email, password);
            };

            const quickSelect = (preset) => {
                setEmail(preset.email);
                setPassword(preset.pass);
                submitLogin(preset.email, preset.pass);
            };

            return (
                <div className="min-h-screen flex items-center justify-center p-4 bg-slate-100">
                    <Card className="w-full max-w-lg border-dark shadow-flat-dark">
                        <div className="mb-6 border-b-2 border-slate-100 pb-4">
                            <span className="bg-emerald-100 text-emerald-800 font-extrabold text-xs px-3 py-1 rounded-full mb-3 inline-block">PORTAL PETUGAS & STAFF KANTIN 1 - 7</span>
                            <h2 className="text-2xl font-black text-dark">Masuk ke Sistem Kantin</h2>
                            <p className="text-slate-500 text-xs mt-1">Pilih akun staff kantin Anda atau masukkan email & password petugas.</p>
                        </div>

                        {/* Quick Login Chips for Kantin 1 - 7 */}
                        <div className="mb-6 p-3.5 bg-slate-50 rounded-2xl border border-slate-200">
                            <p className="text-[11px] font-extrabold text-slate-500 uppercase tracking-wider mb-2.5">
                                Klik Cepat Masuk Akun Staff (Password: password):
                            </p>
                            <div className="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                {staffPresets.map(preset => (
                                    <button 
                                        key={preset.id}
                                        type="button"
                                        disabled={isLoading}
                                        onClick={() => quickSelect(preset)}
                                        className="p-2 rounded-xl border border-slate-200 bg-white hover:border-emerald-500 hover:bg-emerald-50/50 transition-all text-left group flex flex-col justify-between shadow-2xs disabled:opacity-50"
                                    >
                                        <div className="flex items-center justify-between">
                                            <span className="text-xs font-black text-emerald-800 bg-emerald-100 px-1.5 py-0.5 rounded">{preset.icon}</span>
                                            <span className="text-[9px] font-black uppercase text-slate-400 group-hover:text-emerald-700">Login →</span>
                                        </div>
                                        <div className="mt-1">
                                            <p className="font-extrabold text-xs text-slate-800 leading-tight group-hover:text-emerald-800">{preset.name}</p>
                                            <p className="text-[10px] text-slate-400 truncate">{preset.desc}</p>
                                        </div>
                                    </button>
                                ))}
                            </div>
                        </div>

                        {error && <div className="mb-4 bg-red-100 text-red-700 px-4 py-2 rounded-xl text-xs font-bold">{error}</div>}
                        
                        <form className="space-y-4" onSubmit={handleLogin}>
                            <div>
                                <label className="block text-xs font-bold text-slate-700 mb-1">Email / Username Petugas</label>
                                <input 
                                    type="text" 
                                    value={email} 
                                    onChange={(e) => setEmail(e.target.value)} 
                                    placeholder="contoh: kantin1@kantin.com atau kantin1"
                                    required 
                                    className="w-full border-2 border-slate-200 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-800 focus:border-dark focus:outline-none transition-colors" 
                                />
                            </div>
                            <div>
                                <label className="block text-xs font-bold text-slate-700 mb-1">Password</label>
                                <input 
                                    type="password" 
                                    value={password} 
                                    onChange={(e) => setPassword(e.target.value)} 
                                    required 
                                    className="w-full border-2 border-slate-200 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-800 focus:border-dark focus:outline-none transition-colors" 
                                />
                            </div>
                            <Button variant="dark" className="w-full mt-2 py-3" disabled={isLoading}>
                                {isLoading ? 'Memeriksa Kredensial...' : 'Masuk ke Dashboard Staff →'}
                            </Button>
                        </form>

                        <div className="mt-6 text-center">
                            <button 
                                type="button" 
                                onClick={() => setPath('/')} 
                                className="text-slate-500 hover:text-slate-800 text-xs font-bold transition-colors"
                            >
                                ← Kembali ke Beranda
                            </button>
                        </div>
                    </Card>
                </div>
            );
        }

        // --- PORTAL SISWA: KATALOG MENU, KANTIN 1-7, & PEMESANAN --- //
        const CANTEEN_METAS = {
            1: { id: 1, name: 'Kantin 1', desc: 'Nasi & Lauk Pauk', icon: 'K1', color: 'from-amber-500 to-orange-600', badge: 'bg-amber-100 text-amber-800 border-amber-300' },
            2: { id: 2, name: 'Kantin 2', desc: 'Sarapan Pagi', icon: 'K2', color: 'from-yellow-500 to-amber-600', badge: 'bg-yellow-100 text-yellow-800 border-yellow-300' },
            3: { id: 3, name: 'Kantin 3', desc: 'Minuman Segar', icon: 'K3', color: 'from-blue-500 to-cyan-600', badge: 'bg-blue-100 text-blue-800 border-blue-300' },
            4: { id: 4, name: 'Kantin 4', desc: 'Cemilan & Gorengan', icon: 'K4', color: 'from-red-500 to-rose-600', badge: 'bg-rose-100 text-rose-800 border-rose-300' },
            5: { id: 5, name: 'Kantin 5', desc: 'Aneka Mie & Bakso', icon: 'K5', color: 'from-purple-500 to-indigo-600', badge: 'bg-purple-100 text-purple-800 border-purple-300' },
            6: { id: 6, name: 'Kantin 6', desc: 'Aneka Jus & Buah', icon: 'K6', color: 'from-emerald-500 to-teal-600', badge: 'bg-teal-100 text-teal-800 border-teal-300' },
            7: { id: 7, name: 'Kantin 7', desc: 'Paket Makan Siang', icon: 'K7', color: 'from-pink-500 to-rose-600', badge: 'bg-pink-100 text-pink-800 border-pink-300' }
        };

        function StudentDashboard({ setPath }) {
            const [data, setData] = useState(null);
            const [cart, setCart] = useState([]);
            const [isCheckingOut, setIsCheckingOut] = useState(false);
            const [activeCategory, setActiveCategory] = useState('Semua');
            const [activeCanteen, setActiveCanteen] = useState('all'); // 'all' or 1..7
            const [paymentMethod, setPaymentMethod] = useState('Cash');
            const [customerName, setCustomerName] = useState('');
            const [activeTab, setActiveTab] = useState('menu');
            const [showGpsModal, setShowGpsModal] = useState(false);
            const [orderSuccessData, setOrderSuccessData] = useState(null);
            const [gpsStatus, setGpsStatus] = useState(() => {
                try {
                    const saved = localStorage.getItem('kantin_gps_verified');
                    return saved ? JSON.parse(saved) : null;
                } catch(e) {
                    return null;
                }
            });

            const loadData = () => {
                fetch('/api/student/data')
                    .then(res => res.json())
                    .then(resData => {
                        if (resData && !resData.error) {
                            setData(resData);
                            if (!customerName && resData.user) {
                                setCustomerName(resData.user);
                            }
                            if (resData.is_present && !gpsStatus) {
                                setGpsStatus({
                                    is_inside: true,
                                    distance: 25,
                                    device: 'Presensi Siswa SMKN 1 Ciomas',
                                    time: resData.attendance_date || new Date().toISOString()
                                });
                            }
                        }
                    })
                    .catch(() => {});
            };

            useEffect(() => {
                loadData();
            }, []);

            const addToCart = (menu) => {
                setCart(prev => {
                    const existing = prev.find(item => item.id === menu.id);
                    if (existing) {
                        return prev.map(item => item.id === menu.id ? Object.assign({}, item, { quantity: item.quantity + 1 }) : item);
                    }
                    return prev.concat([Object.assign({}, menu, { quantity: 1 })]);
                });
            };

            const updateQty = (id, delta) => {
                setCart(prev => {
                    return prev.map(item => {
                        if (item.id === id) {
                            return Object.assign({}, item, { quantity: Math.max(0, item.quantity + delta) });
                        }
                        return item;
                    }).filter(item => item.quantity > 0);
                });
            };

            const checkout = () => {
                if (cart.length === 0) return;

                setIsCheckingOut(true);
                fetch('/api/student/order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        items: cart, 
                        payment_method: paymentMethod, 
                        customer_name: customerName || (data ? data.user : 'Siswa'),
                        user_id: data ? data.user_id : null,
                        is_at_school: gpsStatus && gpsStatus.is_inside ? 1 : 0,
                        gps_distance: gpsStatus ? gpsStatus.distance : null,
                        gps_device: gpsStatus ? (gpsStatus.device || 'HP Siswa') : null
                    })
                })
                .then(res => res.json())
                .then(resData => {
                    setIsCheckingOut(false);
                    if (resData.success) {
                        try {
                            const ordersList = resData.orders && resData.orders.length > 0 ? resData.orders : (resData.order ? [resData.order] : []);
                            if (ordersList.length > 0) {
                                localStorage.setItem('kantin_my_last_orders', JSON.stringify({
                                    orders: ordersList,
                                    codes_summary: resData.codes_summary || '',
                                    customer_name: customerName || (data ? data.user : 'Siswa'),
                                    timestamp: Date.now()
                                }));
                            }
                        } catch (e) {}

                        setOrderSuccessData({
                            orders: resData.orders || (resData.order ? [resData.order] : []),
                            codes_summary: resData.codes_summary || resData.message,
                            customer_name: customerName || (data ? data.user : 'Siswa'),
                            group_code: resData.group_code || ''
                        });
                        setCart([]);
                        loadData();
                    } else {
                        alert(resData.message || resData.error || 'Gagal membuat pesanan');
                    }
                })
                .catch(() => {
                    setIsCheckingOut(false);
                    alert('Terjadi kesalahan jaringan.');
                });
            };

            if (!data) return <PageLoader title="Memuat Portal Kantin..." subtitle="Mengambil katalog menu & pemesanan..." icon="K" />;

            const cartTotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

            // Group cart items by canteen
            const cartCanteens = {};
            cart.forEach(item => {
                const cId = item.canteen_id || 1;
                if (!cartCanteens[cId]) cartCanteens[cId] = [];
                cartCanteens[cId].push(item);
            });
            const canteensInCartCount = Object.keys(cartCanteens).length;

            return (
                <div className="min-h-screen bg-slate-50 pb-20">
                    <nav className="bg-white/80 backdrop-blur-md border-b border-slate-100 p-4 sticky top-0 z-50">
                        <div className="max-w-7xl mx-auto flex justify-between items-center">
                            <div className="flex items-center space-x-2">
                                <div className="w-8 h-8 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center text-white font-bold">K</div>
                                <span className="text-xl font-extrabold text-slate-800 tracking-tight">Kantin Pintar <span className="text-emerald-600 font-bold">Sekolah</span></span>
                            </div>
                            <div className="flex items-center space-x-2 sm:space-x-3">
                                <Button variant="outline" className="py-1.5 px-3 sm:px-4 text-xs font-bold" onClick={() => setPath('/')}>Kembali ke Beranda</Button>
                                <button 
                                    type="button" 
                                    onClick={() => {
                                        fetch('/api/logout', { 
                                            method: 'POST', 
                                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } 
                                        }).then(() => setPath('/'));
                                    }}
                                    className="py-1.5 px-3 sm:px-4 text-xs font-bold rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition-all shadow-xs"
                                >
                                    Logout
                                </button>
                            </div>
                        </div>
                    </nav>

                    {/* Pop-up Modal Sukses Buat Pesanan dengan Rincian Tiket per Kantin 1 - 7 */}
                    {orderSuccessData && (
                        <div className="fixed inset-0 z-[9999] bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 animate-page-enter">
                            <div className="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-100 max-h-[90vh] overflow-y-auto">
                                <div className="w-16 h-16 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-black mx-auto mb-4 shadow-sm font-mono">
                                    SUKSES
                                </div>
                                <h3 className="text-2xl font-black text-center text-slate-800">Pesanan Berhasil Dibuat!</h3>
                                <p className="text-center text-slate-500 text-xs sm:text-sm mt-1">
                                    Atas nama: <span className="font-extrabold text-slate-800">{orderSuccessData.customer_name}</span>
                                </p>
                                
                                <div className="mt-5 p-3.5 bg-emerald-50 rounded-2xl border border-emerald-200 text-xs text-emerald-900 font-bold flex items-center gap-2">
                                    <span>Tiap pesanan telah dipisahkan ke loket masing-masing staf kantin. Tunjukkan tiket di bawah saat mengambil:</span>
                                </div>

                                <div className="mt-4 space-y-3">
                                    {(orderSuccessData.orders || []).map((ord, idx) => {
                                        const cId = ord.canteen_id || 1;
                                        const cInfo = CANTEEN_METAS[cId] || { name: `Kantin ${cId}`, desc: 'Loket Kantin', icon: 'K' + cId };
                                        return (
                                            <div key={idx} className="p-4 rounded-2xl bg-slate-50 border-2 border-slate-200 flex items-center justify-between gap-3">
                                                <div className="flex items-center gap-3">
                                                    <div className="w-12 h-12 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-xs font-black shadow-xs text-slate-800">
                                                        {cInfo.icon}
                                                    </div>
                                                    <div>
                                                        <span className="text-[10px] font-black uppercase text-slate-400 block tracking-wider">
                                                            {cInfo.name} • {cInfo.desc}
                                                        </span>
                                                        <span className="text-lg font-black text-slate-800 font-mono tracking-wider">
                                                            {ord.order_code || `K${cId}-${String(ord.id).padStart(3, '0')}`}
                                                        </span>
                                                        <p className="text-xs text-slate-500">
                                                            {(ord.items || []).length} Menu • Rp {Number(ord.total_amount).toLocaleString('id-ID')}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div className="text-right">
                                                    <span className="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-800 border border-amber-200">
                                                        Antrean Loket
                                                    </span>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>

                                <div className="mt-6 pt-4 border-t border-slate-100 flex gap-2">
                                    <Button 
                                        className="w-full py-3" 
                                        onClick={() => {
                                            setOrderSuccessData(null);
                                            setActiveTab('riwayat');
                                        }}
                                    >
                                        Lihat di Riwayat Pesanan →
                                    </Button>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Header Banner Kantin Sekolah */}
                    <div className="bg-gradient-to-r from-emerald-800 via-green-700 to-teal-700 text-white p-8 md:p-10 mb-6 shadow-inner relative overflow-hidden">
                        <div className="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl"></div>
                        <div className="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>
                        <div className="max-w-7xl mx-auto relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                            <div>
                                <div className="inline-flex items-center gap-2 bg-white/20 text-white text-xs font-black px-3.5 py-1.5 rounded-full uppercase tracking-widest mb-3 backdrop-blur-md border border-white/30 shadow-xs">
                                    KANTIN 1 SAMPAI 7 • SISTEM TIKET LOKET TERPISAH
                                </div>
                                <h1 className="text-3xl md:text-4xl font-black text-left tracking-tight">
                                    SELAMAT JAJAN BARUDAK SKANIC
                                </h1>
                                <p className="mt-1 text-emerald-100 font-semibold text-sm md:text-base">
                                    Pesan makanan dari Kantin 1 - 7 langsung ke loket masing-masing staf kantin tanpa antre berdesakan.
                                </p>
                            </div>

                            <div className="bg-slate-900/40 backdrop-blur-md border border-white/20 p-4 rounded-2xl flex items-center gap-3 shrink-0 shadow-lg">
                                <div className="w-12 h-12 rounded-xl bg-emerald-500/30 border border-emerald-400/40 flex items-center justify-center text-xs font-black text-emerald-300">
                                    7 Stand
                                </div>
                                <div>
                                    <p className="text-[11px] font-bold text-slate-300 uppercase tracking-wider">Fast Track Loket</p>
                                    <p className="font-black text-white text-sm">
                                        7 Loket Kantin Mandiri
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <main className="max-w-7xl mx-auto p-4 space-y-6">
                        {/* Modal Deteksi Keberadaan Siswa di SMKN 1 Ciomas */}
                        <GpsCrossDeviceModal 
                            isOpen={showGpsModal} 
                            onClose={() => setShowGpsModal(false)} 
                            onVerified={(val) => setGpsStatus(val)} 
                            currentStudentName={customerName || (data ? data.user : 'Siswa')} 
                            currentUserId={data ? data.user_id : null} 
                        />

                        {/* Top Tab Bar: Katalog Menu, Halaman Pemesanan (badge), Riwayat Pesanan */}
                        <div className="flex items-center space-x-2 sm:space-x-4 border-b border-slate-200 pb-3 overflow-x-auto">
                            <button 
                                type="button" 
                                onClick={() => setActiveTab('menu')} 
                                className={`py-2.5 px-4 font-black text-xs sm:text-sm rounded-2xl transition-all flex items-center gap-2 whitespace-nowrap ${activeTab === 'menu' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'bg-white text-slate-700 border border-slate-200 hover:border-slate-300'}`}
                            >
                                <span>Katalog Menu Kantin</span>
                            </button>
                            <button 
                                type="button" 
                                onClick={() => setActiveTab('order')} 
                                className={`py-2.5 px-4 font-black text-xs sm:text-sm rounded-2xl transition-all flex items-center gap-2 whitespace-nowrap relative ${activeTab === 'order' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'bg-white text-slate-700 border border-slate-200 hover:border-slate-300'}`}
                            >
                                <span>Halaman Pemesanan</span>
                                {cart.length > 0 && (
                                    <span className={`px-2 py-0.5 rounded-full text-xs font-black ${activeTab === 'order' ? 'bg-white text-emerald-800' : 'bg-emerald-600 text-white'}`}>
                                        {cart.reduce((s, it) => s + it.quantity, 0)}
                                    </span>
                                )}
                            </button>
                            <button 
                                type="button" 
                                onClick={() => setActiveTab('riwayat')} 
                                className={`py-2.5 px-4 font-black text-xs sm:text-sm rounded-2xl transition-all flex items-center gap-2 whitespace-nowrap ${activeTab === 'riwayat' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'bg-white text-slate-700 border border-slate-200 hover:border-slate-300'}`}
                            >
                                <span>Riwayat Pesanan</span>
                                {data.orders && data.orders.length > 0 && (
                                    <span className={`px-2 py-0.5 rounded-full text-xs font-black ${activeTab === 'riwayat' ? 'bg-white text-emerald-800' : 'bg-slate-200 text-slate-700'}`}>
                                        {data.orders.length}
                                    </span>
                                )}
                            </button>
                        </div>

                        {/* Banner Widget Deteksi Keberadaan GPS Siswa di SMKN 1 Ciomas */}
                        <div className={`p-4 rounded-3xl border-2 transition-all duration-300 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xs ${
                            gpsStatus && gpsStatus.is_inside 
                                ? 'bg-gradient-to-r from-emerald-50 via-green-50 to-teal-50 border-emerald-400' 
                                : 'bg-white border-slate-200 hover:border-emerald-300'
                        }`}>
                            <div className="flex items-center gap-3.5 min-w-0">
                                <div className={`w-12 h-12 rounded-2xl flex items-center justify-center text-xs font-black shrink-0 shadow-md ${
                                    gpsStatus && gpsStatus.is_inside 
                                        ? 'bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-emerald-500/20' 
                                        : 'bg-slate-100 text-slate-600'
                                }`}>
                                    GPS
                                </div>
                                <div className="min-w-0">
                                    <div className="flex items-center gap-2 flex-wrap mb-0.5">
                                        <span className={`text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full ${
                                            gpsStatus && gpsStatus.is_inside 
                                                ? 'bg-emerald-200 text-emerald-900 border border-emerald-300' 
                                                : 'bg-slate-200 text-slate-700'
                                        }`}>
                                            {gpsStatus && gpsStatus.is_inside ? 'TERDETEKSI DI SMKN 1 CIOMAS' : 'STATUS LOKASI BELUM DIVERIFIKASI'}
                                        </span>
                                        {gpsStatus && gpsStatus.is_inside && (
                                            <span className="text-xs text-emerald-700 font-extrabold">
                                                Radius: {Math.round(gpsStatus.distance || 0)} meter
                                            </span>
                                        )}
                                    </div>
                                    <p className="font-extrabold text-slate-800 text-xs sm:text-sm truncate">
                                        {gpsStatus && gpsStatus.is_inside 
                                            ? `Siswa terverifikasi berada di area SMKN 1 Ciomas (${gpsStatus.device || 'Perangkat HP'})` 
                                            : 'Deteksi apakah siswa sedang berada di SMKN 1 Ciomas via sensor GPS HP / perangkat lain'}
                                    </p>
                                    <p className="text-[11px] text-slate-500">
                                        Titik SMKN 1 Ciomas: Jl. Raya Laladon • Geofence aman 350 meter
                                    </p>
                                </div>
                            </div>

                            <div className="flex items-center gap-2 w-full sm:w-auto shrink-0">
                                <button 
                                    type="button"
                                    onClick={() => setShowGpsModal(true)}
                                    className={`w-full sm:w-auto px-4 py-2.5 rounded-2xl text-xs font-black transition-all shadow-md flex items-center justify-center gap-2 ${
                                        gpsStatus && gpsStatus.is_inside 
                                            ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-600/20' 
                                            : 'bg-slate-900 hover:bg-slate-800 text-white'
                                    }`}
                                >
                                    {gpsStatus && gpsStatus.is_inside ? 'Cek Status GPS' : 'Deteksi GPS Siswa'}
                                </button>
                                {gpsStatus && (
                                    <button 
                                        type="button" 
                                        onClick={() => {
                                            localStorage.removeItem('kantin_gps_verified');
                                            setGpsStatus(null);
                                        }}
                                        title="Reset status GPS"
                                        className="px-3 py-2 text-slate-400 hover:text-slate-700 text-xs font-bold rounded-xl hover:bg-slate-100 border border-slate-200"
                                    >
                                        Reset
                                    </button>
                                )}
                            </div>
                        </div>

                            {activeTab === 'menu' && (
                                <div key="tab-menu" className="animate-page-enter space-y-6">
                                    {/* Filter Baris 1: Filter Khusus Kantin 1 sampai 7 */}
                                    <div>
                                        <div className="flex items-center justify-between mb-2">
                                            <span className="text-xs font-black uppercase text-slate-400 tracking-wider">
                                                🏪 Filter Loket Stand Kantin (1 - 7):
                                            </span>
                                            {activeCanteen !== 'all' && (
                                                <button 
                                                    type="button" 
                                                    onClick={() => setActiveCanteen('all')}
                                                    className="text-xs text-emerald-600 hover:underline font-bold"
                                                >
                                                    Lihat Semua Kantin
                                                </button>
                                            )}
                                        </div>
                                        <div className="flex space-x-2 overflow-x-auto pb-2">
                                            <button 
                                                type="button" 
                                                onClick={() => setActiveCanteen('all')} 
                                                className={`font-black text-xs px-4 py-2 rounded-2xl whitespace-nowrap transition-all shadow-xs flex items-center gap-1.5 ${activeCanteen === 'all' ? 'bg-slate-900 text-white shadow-md' : 'bg-white text-slate-700 border border-slate-200 hover:border-slate-400'}`}
                                            >
                                                Semua Kantin (1 - 7)
                                            </button>
                                            {[1, 2, 3, 4, 5, 6, 7].map(cId => {
                                                const cInfo = CANTEEN_METAS[cId];
                                                const isSel = activeCanteen === cId;
                                                return (
                                                    <button 
                                                        key={cId} 
                                                        type="button" 
                                                        onClick={() => setActiveCanteen(cId)} 
                                                        className={`font-black text-xs px-3.5 py-2 rounded-2xl whitespace-nowrap transition-all shadow-xs flex items-center gap-1.5 ${isSel ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'bg-white text-slate-700 border border-slate-200 hover:border-emerald-300 hover:bg-emerald-50/30'}`}
                                                    >
                                                        <span className="font-mono">{cInfo.icon}</span>
                                                        <span>{cInfo.name}</span>
                                                        <span className={`text-[10px] font-bold ${isSel ? 'text-emerald-100' : 'text-slate-400'}`}>({cInfo.desc})</span>
                                                    </button>
                                                );
                                            })}
                                        </div>
                                    </div>

                                    {/* Filter Baris 2: Kategori Menu */}
                                    <div className="flex space-x-2 overflow-x-auto pb-2">
                                        {['Semua', 'Sarapan', 'Makan Siang', 'Minuman', 'Cemilan'].map(cat => (
                                            <button 
                                                key={cat}
                                                onClick={() => setActiveCategory(cat)}
                                                className={`font-bold text-xs px-4 py-1.5 rounded-full whitespace-nowrap transition-colors ${activeCategory === cat ? 'bg-emerald-800 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:border-dark'}`}
                                            >
                                                {cat === 'Semua' ? 'Semua Jenis Menu' : cat}
                                            </button>
                                        ))}
                                    </div>

                                    {/* Katalog Menu Grid */}
                                    <div className="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                        {data.menus && (() => {
                                            const filtered = data.menus.filter(m => {
                                                // Filter Kantin
                                                if (activeCanteen !== 'all' && Number(m.canteen_id) !== Number(activeCanteen)) {
                                                    return false;
                                                }
                                                // Filter Kategori
                                                if (activeCategory === 'Semua') return true;
                                                if (activeCategory === 'Cemilan') return m.category === 'Cemilan' || m.category === 'Camilan';
                                                return m.category === activeCategory;
                                            });

                                            if (filtered.length === 0) {
                                                return (
                                                    <div className="col-span-2 sm:col-span-2 md:col-span-3 lg:col-span-4 text-center py-12 bg-white border-2 border-dashed border-slate-200 rounded-3xl p-6">
                                                        <div className="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center font-bold text-xs mx-auto mb-2 font-mono">
                                                            KOSONG
                                                        </div>
                                                        <p className="font-extrabold text-slate-700 text-sm">Tidak ada menu pada filter ini</p>
                                                        <p className="text-slate-400 text-xs mt-1">Coba ubah pilihan Kantin atau Kategori menu di atas.</p>
                                                    </div>
                                                );
                                            }

                                            return filtered.map(menu => {
                                                const mCanteenId = menu.canteen_id || 1;
                                                const cMeta = CANTEEN_METAS[mCanteenId] || { name: `Kantin ${mCanteenId}`, icon: 'K' + mCanteenId, desc: 'Loket Kantin' };
                                                const catLabel = (menu.category || '').toLowerCase().includes('cami') ? 'Cemilan' : menu.category;

                                                return (
                                                <div key={menu.id} className="bg-white border border-slate-200/90 rounded-2xl p-2.5 sm:p-3 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group hover:border-emerald-300">
                                                    <div>
                                                        <div className="aspect-[4/3] w-full bg-slate-50 border border-slate-100 rounded-xl overflow-hidden mb-2.5 flex items-center justify-center p-1.5 relative">
                                                            {/* Canteen Stand Badge */}
                                                            <span className="absolute top-2 left-2 bg-slate-900/90 backdrop-blur-sm text-white font-extrabold text-[10px] px-2 py-0.5 rounded-lg shadow-sm z-10 flex items-center gap-1 border border-white/20">
                                                                <span className="font-mono">{cMeta.icon}</span>
                                                                <span>{cMeta.name}</span>
                                                            </span>

                                                            {/* Category badge */}
                                                            <span className="absolute bottom-2 left-2 bg-white/90 backdrop-blur-sm text-slate-700 font-bold text-[9px] px-2 py-0.5 rounded-md shadow-xs z-10 border border-slate-100">
                                                                {catLabel}
                                                            </span>

                                                            {menu.image ? (
                                                                <img 
                                                                    src={`/storage/${menu.image}`} 
                                                                    alt={menu.name}
                                                                    className="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300" 
                                                                />
                                                            ) : (
                                                                <div className="w-full h-full flex items-center justify-center bg-slate-100 text-slate-400 text-xs font-bold font-mono">
                                                                    {menu.category || 'MENU'}
                                                                </div>
                                                            )}
                                                        </div>
                                                        <div className="flex items-center justify-between text-[11px] font-semibold text-slate-400 mb-1">
                                                            <span>Stok: {menu.stock} porsi</span>
                                                        </div>
                                                        <h3 className="font-bold text-sm sm:text-base text-dark leading-snug line-clamp-2 mb-2" title={menu.name}>
                                                            {menu.name}
                                                        </h3>
                                                    </div>
                                                    <div className="pt-2 border-t border-slate-100 flex items-center justify-between gap-1">
                                                        <span className="text-sm sm:text-base font-extrabold text-primary">
                                                            Rp {Number(menu.price).toLocaleString('id-ID')}
                                                        </span>
                                                        <button 
                                                            className="font-bold py-1 px-3 sm:px-3.5 rounded-full text-xs sm:text-sm bg-primary hover:bg-green-600 text-white disabled:opacity-40 disabled:cursor-not-allowed transition-colors shrink-0 shadow-xs" 
                                                            onClick={() => addToCart(menu)} 
                                                            disabled={menu.stock <= 0}
                                                        >
                                                            {menu.stock <= 0 ? 'Habis' : '+ Tambah'}
                                                        </button>
                                                    </div>
                                                </div>
                                                );
                                            });
                                        })()}
                                    </div>

                                    {/* Floating Bottom Bar: Cart Summary & Button ke Halaman Pemesanan */}
                                    {cart.length > 0 && (
                                        <div className="sticky bottom-6 z-40 bg-slate-900 text-white p-4 rounded-2xl shadow-2xl flex flex-col sm:flex-row items-center justify-between gap-3 border border-slate-700 animate-slide-up">
                                            <div className="flex items-center gap-3 w-full sm:w-auto">
                                                <div className="w-10 h-10 rounded-xl bg-emerald-500 text-white font-black flex items-center justify-center text-sm shrink-0 font-mono">
                                                    {cart.reduce((s, it) => s + it.quantity, 0)}
                                                </div>
                                                <div>
                                                    <p className="text-xs text-slate-400 font-bold uppercase tracking-wider">Item Terpilih ({canteensInCartCount} Loket Kantin)</p>
                                                    <p className="text-lg font-black text-emerald-400">
                                                        Total: Rp {cartTotal.toLocaleString('id-ID')}
                                                    </p>
                                                </div>
                                            </div>
                                            <Button 
                                                onClick={() => setActiveTab('order')} 
                                                className="w-full sm:w-auto py-2.5 px-6 font-black text-sm bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-600/30"
                                            >
                                                Lanjut ke Halaman Pemesanan →
                                            </Button>
                                        </div>
                                    )}
                                </div>
                            )}
                            
                            {activeTab === 'riwayat' && (
                                <div key="tab-riwayat" className="animate-page-enter space-y-4 pt-2">
                                {data.orders && data.orders.length > 0 ? (
                                    data.orders.map(order => {
                                        const ordCanteenId = order.canteen_id || 1;
                                        const ordCanteen = CANTEEN_METAS[ordCanteenId] || { name: `Kantin ${ordCanteenId}`, desc: 'Loket Kantin', icon: 'K' + ordCanteenId };
                                        const ticketCode = order.order_code || (`K${ordCanteenId}-${String(order.id).padStart(3, '0')}`);

                                        return (
                                        <Card key={order.id} className="overflow-hidden p-0 border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                                            <div className="p-6">
                                                <div className="flex justify-between items-start mb-4 gap-2 flex-wrap">
                                                    <div>
                                                        <div className="flex items-center gap-2 flex-wrap">
                                                            <span className="font-mono font-black text-lg text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-xl border border-emerald-200">
                                                                TIKET {ticketCode}
                                                            </span>
                                                            <span className="inline-flex items-center gap-1.5 text-xs font-black px-2.5 py-1 rounded-xl bg-slate-900 text-white">
                                                                <span className="font-mono">{ordCanteen.icon}</span> {ordCanteen.name}
                                                            </span>
                                                            {order.is_at_school ? (
                                                                <span className="inline-flex items-center gap-1 text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                                    SMKN 1 CIOMAS ({Math.round(order.gps_distance || 0)}m)
                                                                </span>
                                                            ) : null}
                                                        </div>
                                                        <p className="text-xs text-slate-400 mt-1.5">{new Date(order.created_at).toLocaleString('id-ID')}</p>
                                                        <p className="text-xs font-bold text-slate-700 mt-0.5">Atas Nama: <span className="text-primary font-extrabold">{order.customer_name || data.user}</span></p>
                                                    </div>
                                                    <div className="text-right">
                                                        <p className="font-black text-lg text-primary">Rp {Number(order.total_amount).toLocaleString('id-ID')}</p>
                                                        <span className={`inline-block text-xs font-extrabold px-2.5 py-0.5 rounded-full mt-1 ${
                                                            order.status === 'completed' ? 'bg-green-100 text-green-700' :
                                                            order.status === 'ready' ? 'bg-emerald-500 text-white animate-pulse' :
                                                            order.status === 'cooking' ? 'bg-amber-100 text-amber-800' :
                                                            'bg-blue-100 text-blue-700'
                                                        }`}>
                                                            {order.status === 'completed' ? 'Sudah Diambil' :
                                                             order.status === 'ready' ? 'Siap Diambil di Loket' :
                                                             order.status === 'cooking' ? 'Sedang Dimasak' : 'Menunggu Konfirmasi'}
                                                        </span>
                                                    </div>
                                                </div>

                                                {/* List item yang dipesan */}
                                                {order.items && order.items.length > 0 && (
                                                    <div className="my-3 py-2 border-y border-slate-100 space-y-1.5">
                                                        {order.items.map((it, iIdx) => (
                                                            <div key={iIdx} className="flex justify-between items-center text-xs">
                                                                <span className="font-bold text-slate-700">
                                                                    {it.quantity}x {it.menu ? it.menu.name : 'Item Makanan'}
                                                                </span>
                                                                <span className="text-slate-500 font-medium">
                                                                    Rp {(Number(it.price) * Number(it.quantity)).toLocaleString('id-ID')}
                                                                </span>
                                                            </div>
                                                        ))}
                                                    </div>
                                                )}

                                                <div className="border-t border-slate-100 pt-3 flex items-center justify-between text-xs text-slate-500">
                                                    <div>
                                                        <span className="font-extrabold text-slate-700">Loket Pengambilan:</span> {ordCanteen.name} ({ordCanteen.desc})
                                                    </div>
                                                    <div className="font-mono text-slate-400">
                                                        ID #{order.id}
                                                    </div>
                                                </div>
                                            </div>
                                        </Card>
                                        );
                                    })
                                ) : (
                                    <div className="text-center py-16 bg-white rounded-3xl border-2 border-dashed border-slate-200 p-6">
                                        <div className="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center font-bold text-xs mx-auto mb-2 font-mono">
                                            RIWAYAT
                                        </div>
                                        <p className="font-bold text-slate-700 text-base">Belum Ada Riwayat Pesanan</p>
                                        <p className="text-slate-400 text-xs mt-1">Pesanan Anda dari Kantin 1 - 7 akan tercatat di sini dengan nomor tiket loket masing-masing.</p>
                                    </div>
                                )}
                                </div>
                            )}

                        {/* TAB 2: HALAMAN PEMESANAN (DEDICATED VIEW) */}
                        {activeTab === 'order' && (
                            <div key="tab-order" className="animate-page-enter space-y-6">
                                {cart.length === 0 ? (
                                    <div className="text-center py-16 bg-white rounded-3xl border-2 border-dashed border-slate-200 p-8 max-w-xl mx-auto shadow-xs">
                                        <div className="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center font-bold text-xs mx-auto mb-4 font-mono">
                                            KOSONG
                                        </div>
                                        <h3 className="font-extrabold text-slate-800 text-lg">Halaman Pemesanan Masih Kosong</h3>
                                        <p className="text-slate-400 text-xs sm:text-sm mt-1 max-w-md mx-auto">
                                            Anda belum memilih menu makanan atau minuman. Silakan buka Katalog Menu untuk memilih hidangan dari Kantin 1 sampai 7.
                                        </p>
                                        <div className="mt-6">
                                            <Button onClick={() => setActiveTab('menu')} className="py-2.5 px-6 font-black text-sm">
                                                ← Buka Katalog Menu Kantin
                                            </Button>
                                        </div>
                                    </div>
                                ) : (
                                    <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                                        {/* Kolom Kiri: Rincian Menu Pesanan per Kantin */}
                                        <div className="lg:col-span-7 space-y-6">
                                            <Card className="border border-slate-200">
                                                <div className="flex items-center justify-between border-b-2 border-slate-100 pb-3 mb-4">
                                                    <div>
                                                        <h3 className="font-extrabold text-lg text-slate-800">Daftar Menu yang Dipesan</h3>
                                                        <p className="text-xs text-slate-400 font-semibold">Total {cart.reduce((s, it) => s + it.quantity, 0)} porsi dari {canteensInCartCount} stand kantin</p>
                                                    </div>
                                                    <button 
                                                        type="button" 
                                                        onClick={() => setActiveTab('menu')}
                                                        className="text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline"
                                                    >
                                                        + Tambah Menu Lain
                                                    </button>
                                                </div>

                                                {canteensInCartCount > 1 && (
                                                    <div className="mb-4 p-3 rounded-2xl bg-blue-50 border border-blue-200 text-blue-900 text-xs font-bold">
                                                        <p className="font-black uppercase tracking-wider text-[10px] text-blue-700 mb-0.5">[Pemberitahuan Multi-Kantin]</p>
                                                        <p>Pesanan Anda mencakup <b>{canteensInCartCount} stand kantin berbeda</b>. Sistem akan otomatis membagi tiket loket ke masing-masing kantin sehingga pengambilan makanan cepat dan teratur.</p>
                                                    </div>
                                                )}

                                                {/* Group by Canteen */}
                                                <div className="space-y-4">
                                                    {Object.keys(cartCanteens).map(cId => {
                                                        const cInfo = CANTEEN_METAS[cId] || { name: `Kantin ${cId}`, desc: 'Loket Kantin', icon: 'K' + cId };
                                                        const items = cartCanteens[cId];
                                                        const subTotalCanteen = items.reduce((s, it) => s + (it.price * it.quantity), 0);

                                                        return (
                                                            <div key={cId} className="border border-slate-200 rounded-2xl p-4 bg-slate-50/50">
                                                                <div className="flex items-center justify-between mb-3 border-b border-slate-200 pb-2">
                                                                    <div className="flex items-center gap-2">
                                                                        <span className="font-mono text-xs font-black bg-slate-900 text-white px-2 py-0.5 rounded-lg">{cInfo.icon}</span>
                                                                        <span className="font-black text-sm text-slate-800">{cInfo.name}</span>
                                                                        <span className="text-xs text-slate-500 font-semibold">({cInfo.desc})</span>
                                                                    </div>
                                                                    <span className="text-xs font-bold text-emerald-700">
                                                                        Rp {subTotalCanteen.toLocaleString('id-ID')}
                                                                    </span>
                                                                </div>

                                                                <div className="space-y-2.5">
                                                                    {items.map(item => (
                                                                        <div key={item.id} className="p-2.5 rounded-xl bg-white border border-slate-200 flex justify-between items-center gap-3">
                                                                            <div className="min-w-0">
                                                                                <p className="font-bold text-slate-800 text-sm truncate">{item.name}</p>
                                                                                <p className="text-slate-500 text-xs">Rp {Number(item.price).toLocaleString('id-ID')} / porsi</p>
                                                                            </div>
                                                                            <div className="flex items-center space-x-2 shrink-0">
                                                                                <div className="flex items-center space-x-1.5 bg-slate-100 border border-slate-200 rounded-lg p-1">
                                                                                    <button onClick={() => updateQty(item.id, -1)} className="w-6 h-6 flex items-center justify-center font-bold text-slate-600 hover:bg-white rounded transition-colors">-</button>
                                                                                    <span className="text-xs font-black w-5 text-center text-slate-800">{item.quantity}</span>
                                                                                    <button onClick={() => updateQty(item.id, 1)} className="w-6 h-6 flex items-center justify-center font-bold text-slate-600 hover:bg-white rounded transition-colors">+</button>
                                                                                </div>
                                                                                <span className="text-xs font-black text-slate-700 min-w-[70px] text-right">
                                                                                    Rp {(Number(item.price) * Number(item.quantity)).toLocaleString('id-ID')}
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    ))}
                                                                </div>
                                                            </div>
                                                        );
                                                    })}
                                                </div>
                                            </Card>

                                            {/* Status Verifikasi Lokasi Siswa */}
                                            <Card className="border border-slate-200">
                                                <h4 className="font-extrabold text-sm text-slate-800 mb-2">Verifikasi Lokasi SMKN 1 Ciomas</h4>
                                                <div className={`p-3.5 rounded-2xl border-2 flex items-center justify-between text-xs transition-all ${
                                                    gpsStatus && gpsStatus.is_inside 
                                                        ? 'bg-emerald-50 border-emerald-300 text-emerald-950 shadow-xs' 
                                                        : 'bg-slate-50 border-slate-200 text-slate-700'
                                                }`}>
                                                    <div className="min-w-0">
                                                        <p className="font-extrabold truncate">
                                                            {gpsStatus && gpsStatus.is_inside ? 'Status: Terdeteksi di Area SMKN 1 Ciomas' : 'Status: Lokasi Belum Diverifikasi'}
                                                        </p>
                                                        <p className="text-[11px] text-slate-500 font-medium truncate mt-0.5">
                                                            {gpsStatus && gpsStatus.is_inside ? `Jarak ~${Math.round(gpsStatus.distance || 0)}m • ${gpsStatus.device || 'Perangkat HP'}` : 'Bisa pesan langsung atau scan GPS via HP untuk kepastian antrean'}
                                                        </p>
                                                    </div>
                                                    <button 
                                                        type="button" 
                                                        onClick={() => setShowGpsModal(true)} 
                                                        className="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl transition-all shadow-xs shrink-0 ml-3"
                                                    >
                                                        {gpsStatus && gpsStatus.is_inside ? 'Cek Status' : 'Scan via HP'}
                                                    </button>
                                                </div>
                                            </Card>
                                        </div>

                                        {/* Kolom Kanan: Form Data Pemesan & Checkout */}
                                        <div className="lg:col-span-5 space-y-6">
                                            <Card className="border border-slate-200 sticky top-24">
                                                <h3 className="font-extrabold text-lg text-slate-800 mb-4 border-b-2 border-slate-100 pb-2">
                                                    Form Pemesanan & Pembayaran
                                                </h3>

                                                <div className="space-y-4 mb-6">
                                                    <div className="mb-4">
                                        <label className="block font-bold text-dark text-sm mb-2">Atas Nama Siswa</label>
                                        <input type="text" value={customerName} onChange={e => setCustomerName(e.target.value)} placeholder={data.user || 'Contoh: Budi Santoso'} className="w-full border-2 border-slate-200 rounded-xl px-4 py-2 font-bold text-slate-700 focus:border-dark focus:outline-none" />
                                    </div>
                                    <label className="block font-bold text-dark text-sm mb-2">Metode Pembayaran</label>
                                    <select value={paymentMethod} onChange={e => setPaymentMethod(e.target.value)} className="w-full border-2 border-slate-200 rounded-xl px-4 py-2 font-bold text-slate-700 mb-2">
                                        <option value="Cash">Cash (Bayar di Kasir Kantin)</option>
                                        <option value="Transfer">Transfer Bank</option>
                                        <option value="QRIS">QRIS</option>
                                    </select>
                                    
                                    {paymentMethod === 'Transfer' && data.settings && data.settings.bank_account && (
                                        <div className="mt-2 mb-4 p-3 bg-blue-50 border-2 border-blue-200 rounded-xl text-blue-800 text-sm font-bold">
                                            Transfer ke: {data.settings.bank_account}
                                        </div>
                                    )}
                                    {paymentMethod === 'QRIS' && data.settings && data.settings.qris_image && (
                                        <div className="mt-2 mb-4 p-3 bg-purple-50 border-2 border-purple-200 rounded-xl flex flex-col items-center">
                                            <p className="text-purple-800 text-sm font-bold mb-2">Scan QRIS berikut:</p>
                                            <img src={`/storage/${data.settings.qris_image}`} className="w-32 h-32 object-contain" />
                                        </div>
                                    )}
                                </div>

                                                <div className="border-t-2 border-slate-100 pt-4 mb-6 space-y-2">
                                                    <div className="flex justify-between font-bold text-xs text-slate-500">
                                                        <span>Stand Loket Kantin:</span>
                                                        <span className="font-mono text-slate-700 font-black">{canteensInCartCount} Kantin</span>
                                                    </div>
                                                    <div className="flex justify-between font-bold text-xs text-slate-500">
                                                        <span>Total Jumlah Menu:</span>
                                                        <span className="text-slate-700 font-black">{cart.reduce((s, it) => s + it.quantity, 0)} porsi</span>
                                                    </div>
                                                    <div className="flex justify-between font-bold text-xs text-slate-500">
                                                        <span>Subtotal Pesanan:</span>
                                                        <span>Rp {cartTotal.toLocaleString('id-ID')}</span>
                                                    </div>
                                                    <div className="flex justify-between font-extrabold text-xl text-slate-800 pt-2 border-t border-slate-200">
                                                        <span>Total Bayar:</span>
                                                        <span className="text-primary font-black">Rp {cartTotal.toLocaleString('id-ID')}</span>
                                                    </div>
                                                </div>

                                                <Button 
                                                    className="w-full py-3.5 font-black text-sm shadow-lg shadow-primary/20" 
                                                    onClick={checkout} 
                                                    disabled={cart.length === 0 || isCheckingOut}
                                                >
                                                    {isCheckingOut ? 'Memproses Tiket Kantin...' : 'Konfirmasi & Pesan Sekarang →'}
                                                </Button>

                                                <div className="mt-3 text-center">
                                                    <button 
                                                        type="button" 
                                                        onClick={() => setActiveTab('menu')}
                                                        className="text-xs font-bold text-slate-500 hover:text-slate-800"
                                                    >
                                                        ← Kembali ke Katalog Menu
                                                    </button>
                                                </div>
                                            </Card>
                                        </div>
                                    </div>
                                )}
                            </div>
                        )}
                    </main>
                </div>
            );
        }

        // --- EXPORT TO EXCEL CLIENT-SIDE HELPER --- //
        function exportTableToExcel(tableId, filename = 'Laporan_Kantin.xls') {
            const table = document.getElementById(tableId);
            if (!table) return;
            const tableHtml = table.outerHTML;
            const fullHtml = '<html><head><meta charset="utf-8">' +
                '<style>table{border-collapse:collapse;width:100%;font-family:Arial,sans-serif;}th,td{border:1px solid #cbd5e1;padding:8px 12px;text-align:left;}th{background:#f1f5f9;font-weight:bold;color:#0f172a;}.num{text-align:right;}.currency{text-align:right;font-weight:bold;color:#16a34a;}</style>' +
                '</head><body>' +
                '<h2 style="font-family:Arial,sans-serif;color:#0f172a;margin-bottom:4px;">E-KANTIN SMKN 1 CIOMAS</h2>' +
                '<p style="font-family:Arial,sans-serif;color:#64748b;font-size:12px;margin-bottom:16px;">Diekspor pada: ' + new Date().toLocaleString('id-ID') + '</p>' +
                tableHtml +
                '</body></html>';
            const blob = new Blob(['\ufeff' + fullHtml], { type: 'application/vnd.ms-excel;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename.endsWith('.xls') ? filename : (filename + '.xls');
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        function AdminDashboard({ setPath }) {
            const [data, setData] = useState(null);
            const [tab, setTab] = useState('dashboard'); // 'dashboard', 'recap', 'menu', 'settings', 'scanner', 'gps'
            const [recapSubTab, setRecapSubTab] = useState('daily'); // 'daily', 'weekly', 'monthly', 'yearly', 'transactions'
            const [selectedCanteenFilter, setSelectedCanteenFilter] = useState(''); // for Super Admin
            const [newMenu, setNewMenu] = useState({ name: '', category: 'Sarapan', price: '', stock: '', canteen_id: 1 });
            const [newMenuImage, setNewMenuImage] = useState(null);
            
            const [scanInput, setScanInput] = useState('');
            const [scanResult, setScanResult] = useState(null);
            const [editingMenuId, setEditingMenuId] = useState(null);
            const [settingsData, setSettingsData] = useState({ promo_status: '', bank_account: '' });
            const [promoImageFile, setPromoImageFile] = useState(null);
            const [qrisImageFile, setQrisImageFile] = useState(null);
            const [gpsHistory, setGpsHistory] = useState([]);
            const [isGpsLoading, setIsGpsLoading] = useState(false);

            const loadGpsHistory = () => {
                setIsGpsLoading(true);
                fetch('/api/gps/history')
                    .then(res => res.json())
                    .then(resData => {
                        setIsGpsLoading(false);
                        if (resData && resData.history) {
                            setGpsHistory(resData.history);
                        }
                    })
                    .catch(() => {
                        setIsGpsLoading(false);
                    });
            };

            const loadData = (canteenId = null) => {
                const targetCanteen = canteenId !== null ? canteenId : selectedCanteenFilter;
                const url = targetCanteen ? `/api/admin/data?canteen_id=${encodeURIComponent(targetCanteen)}` : '/api/admin/data';

                fetch(url)
                    .then(res => {
                        if (res.status === 401) { setPath('/login/admin'); return null; }
                        if (!res.ok) {
                            return res.text().then(text => { throw new Error(text); });
                        }
                        return res.json();
                    })
                    .then(resData => {
                        if (resData && !resData.error) {
                            setData(resData);
                            if (resData.canteen_id && !selectedCanteenFilter && !resData.is_super_admin) {
                                setSelectedCanteenFilter(String(resData.canteen_id));
                            }
                            if (resData.settings) {
                                setSettingsData({
                                    promo_status: resData.settings.promo_status || '',
                                    bank_account: resData.settings.bank_account || ''
                                });
                            }
                            if (resData.canteen_id) {
                                setNewMenu(prev => Object.assign({}, prev, { canteen_id: resData.canteen_id }));
                            }
                        } else if (resData && resData.error) {
                            alert("Error from API: " + resData.error);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert("Gagal memuat data admin. Buka console (F12) untuk detail. Error: " + err.message.substring(0, 100));
                    });
            };

            useEffect(() => {
                loadData();
            }, []);

            const handleCanteenFilterChange = (cId) => {
                setSelectedCanteenFilter(cId);
                loadData(cId);
            };

            const handleLogout = () => {
                fetch('/api/logout', { 
                    method: 'POST', 
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } 
                }).then(() => {
                    setPath('/');
                });
            };

            const triggerServerExport = (period = 'all') => {
                const cId = selectedCanteenFilter || (data ? (data.canteen_id || '') : '');
                const url = `/api/admin/export-sales?period=${encodeURIComponent(period)}&canteen_id=${encodeURIComponent(cId)}`;
                window.open(url, '_blank');
            };

            const updateOrder = (id, targetStatus = null) => {
                return fetch(`/api/admin/order/${id}`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(targetStatus ? { status: targetStatus } : {})
                })
                .then(res => res.json())
                .then(resData => {
                    if (resData.success) {
                        loadData();
                        return resData.order;
                    } else {
                        alert(resData.error || 'Gagal update status');
                        return null;
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Gagal update status: " + err.message);
                    return null;
                });
            };

            const handleAddMenu = (e) => {
                e.preventDefault();
                const formData = new FormData();
                formData.append('name', newMenu.name);
                formData.append('category', newMenu.category);
                formData.append('price', newMenu.price);
                formData.append('stock', newMenu.stock);
                formData.append('canteen_id', newMenu.canteen_id || (data ? (data.canteen_id || 1) : 1));
                if (newMenuImage) {
                    formData.append('image', newMenuImage);
                }

                const url = editingMenuId ? `/api/admin/menu/${editingMenuId}` : '/api/admin/menu';

                fetch(url, {
                    method: 'POST',
                    headers: { 
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => {
                    return res.text().then(text => {
                        try {
                            const json = JSON.parse(text);
                            return { ok: res.ok, status: res.status, data: json };
                        } catch (e) {
                            throw new Error(text);
                        }
                    });
                })
                .then(res => {
                    const resData = res.data;
                    if (res.ok && resData.success) {
                        setNewMenu({ name: '', category: 'Sarapan', price: '', stock: '', canteen_id: data ? (data.canteen_id || 1) : 1 });
                        setNewMenuImage(null);
                        setEditingMenuId(null);
                        loadData();
                        alert(editingMenuId ? 'Menu berhasil diperbarui!' : 'Menu berhasil ditambahkan!');
                        setTab('dashboard');
                    } else {
                        let errorMsg = resData.error || resData.message || (editingMenuId ? 'Gagal perbarui menu' : 'Gagal tambah menu');
                        if (resData.errors) {
                            errorMsg += '\n' + Object.values(resData.errors).flat().join('\n');
                        }
                        alert(errorMsg);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Terjadi kesalahan sistem: " + err.message.substring(0, 100));
                });
            };

            const handleSaveSettings = (e) => {
                e.preventDefault();
                const formData = new FormData();
                formData.append('promo_status', settingsData.promo_status);
                formData.append('bank_account', settingsData.bank_account);
                if (promoImageFile) formData.append('promo_image', promoImageFile);
                if (qrisImageFile) formData.append('qris_image', qrisImageFile);

                fetch('/api/admin/settings', {
                    method: 'POST',
                    headers: { 
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(resData => {
                    if (resData.success) {
                        alert('Pengaturan berhasil disimpan!');
                        loadData();
                    } else {
                        alert(resData.error || 'Gagal menyimpan pengaturan');
                    }
                })
                .catch(err => alert("Terjadi kesalahan sistem: " + err.message.substring(0, 100)));
            };

            const handleEditMenu = (menu) => {
                setEditingMenuId(menu.id);
                setNewMenu({ 
                    name: menu.name, 
                    category: menu.category, 
                    price: menu.price, 
                    stock: menu.stock,
                    canteen_id: menu.canteen_id || 1
                });
                setNewMenuImage(null);
                setTab('menu');
            };

            const handleDeleteMenu = (id) => {
                if (!confirm("Apakah Anda yakin ingin menghapus menu ini?")) return;
                fetch('/api/admin/menu/' + id, {
                    method: 'DELETE',
                    headers: { 
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(resData => {
                    if (resData.success) {
                        alert("Menu berhasil dihapus!");
                        loadData();
                    } else {
                        alert(resData.error || "Gagal menghapus menu");
                    }
                })
                .catch(err => alert("Terjadi kesalahan sistem: " + err.message.substring(0, 100)));
            };

            if (!data) return <PageLoader title="Memuat Dashboard Admin..." subtitle="Sinkronisasi data omzet, kelola menu & antrean loket..." icon="🔐" />;

            const recap = data.recap || { summary: {}, daily: [], weekly: [], monthly: [], yearly: [], recent_transactions: [] };
            const summary = recap.summary || {};

            return (
                <div className="min-h-screen flex flex-col md:flex-row bg-slate-50">
                    {/* SIDEBAR */}
                    <aside className="w-full md:w-64 bg-slate-900 text-white md:min-h-screen p-5 border-b md:border-b-0 md:border-r border-slate-800 shrink-0 flex flex-col justify-between">
                        <div>
                            {/* Logo & Brand */}
                            <div className="flex items-center justify-between gap-3 mb-6 pb-4 border-b border-slate-800">
                                <div className="flex items-center gap-3">
                                    <div className="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-green-400 flex items-center justify-center text-white text-base font-black shadow-md shadow-emerald-500/30">
                                        K
                                    </div>
                                    <div>
                                        <h2 className="font-black text-lg leading-tight text-white tracking-tight">E-Kantin <span className="text-emerald-400">Staff</span></h2>
                                        <p className="text-[11px] text-slate-400 font-medium">SMKN 1 Ciomas</p>
                                    </div>
                                </div>
                                <button 
                                    type="button" 
                                    onClick={handleLogout} 
                                    title="Logout Petugas" 
                                    className="px-2.5 py-1 text-[11px] font-black rounded-lg bg-rose-500/20 text-rose-300 hover:bg-rose-500/30 border border-rose-500/30 transition-all"
                                >
                                    Logout
                                </button>
                            </div>

                            {/* Canteen Identity Badge */}
                            <div className="mb-5 p-3 rounded-2xl bg-slate-800/80 border border-slate-700/80">
                                <div className="flex items-center gap-2 mb-1">
                                    <span className="text-[10px] font-mono font-black px-1.5 py-0.5 rounded bg-slate-700 text-emerald-400">{(data.canteen_info && data.canteen_info.icon) || (data.is_super_admin ? 'SA' : 'K')}</span>
                                    <span className="text-[10px] font-black uppercase tracking-wider text-emerald-400">
                                        {data.is_super_admin ? 'Super Administrator' : 'Staff Petugas Stand'}
                                    </span>
                                </div>
                                <p className="font-black text-sm text-white truncate">
                                    {data.is_super_admin ? 'Semua Kantin (1-7)' : ((data.canteen_info && data.canteen_info.name) || 'Kantin ' + (data.canteen_id || 1))}
                                </p>
                                <p className="text-[11px] text-slate-400 truncate">
                                    {data.is_super_admin ? 'Akses Penuh Seluruh Loket' : ((data.canteen_info && data.canteen_info.desc) || 'Pengelola Stand')}
                                </p>
                            </div>

                            {/* Navigation */}
                            <nav className="space-y-1.5">
                                <button 
                                    onClick={() => setTab('dashboard')} 
                                    className={`w-full text-left flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-extrabold transition-all ${tab === 'dashboard' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'}`}
                                >
                                    Dashboard
                                </button>

                                <button 
                                    onClick={() => setTab('recap')} 
                                    className={`w-full text-left flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-extrabold transition-all ${tab === 'recap' ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-lg shadow-emerald-600/30 ring-1 ring-emerald-400/50' : 'text-emerald-400 hover:text-white hover:bg-emerald-950/40'}`}
                                >
                                    <span>Rekap & Ekspor Excel</span>
                                    <span className="px-1.5 py-0.5 rounded text-[9px] font-black bg-emerald-500/20 text-emerald-300 uppercase">
                                        Excel
                                    </span>
                                </button>

                                <button 
                                    onClick={() => setTab('scanner')} 
                                    className={`w-full text-left flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-extrabold transition-all ${tab === 'scanner' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'}`}
                                >
                                    Loket Antrean ({((data.stats && data.stats.active_orders) || 0)})
                                </button>

                                <button 
                                    onClick={() => setTab('menu')} 
                                    className={`w-full text-left flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-extrabold transition-all ${tab === 'menu' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'}`}
                                >
                                    Kelola Menu
                                </button>

                                <button 
                                    onClick={() => setTab('settings')} 
                                    className={`w-full text-left flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-extrabold transition-all ${tab === 'settings' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'}`}
                                >
                                    Pengaturan Loket
                                </button>

                                <button 
                                    onClick={() => { setTab('gps'); loadGpsHistory(); }} 
                                    className={`w-full text-left flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-extrabold transition-all ${tab === 'gps' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'}`}
                                >
                                    Monitoring GPS Siswa
                                </button>
                            </nav>
                        </div>

                        {/* Logout & Footer */}
                        <div className="mt-6 pt-4 border-t border-slate-800">
                            <button 
                                onClick={handleLogout} 
                                className="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl border border-slate-700 text-slate-300 hover:bg-rose-500/10 hover:text-rose-400 hover:border-rose-500/30 text-xs font-bold transition-all"
                            >
                                Logout Petugas
                            </button>
                        </div>
                    </aside>

                    {/* MAIN CONTENT AREA */}
                    <main className="flex-1 p-4 md:p-8 overflow-y-auto">
                        {/* Top Header Bar */}
                        <div className="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4 pb-4 border-b border-slate-200">
                            <div>
                                <div className="flex items-center gap-2.5 flex-wrap">
                                    <h1 className="text-2xl md:text-3xl font-black text-slate-800">
                                        {tab === 'dashboard' ? 'Overview Kantin' : 
                                         tab === 'recap' ? 'Rekap Penjualan & Ekspor Excel' : 
                                         tab === 'settings' ? 'Pengaturan Tampilan & Rekening' : 
                                         tab === 'scanner' ? 'Loket Pengambilan Pesanan' : 
                                         tab === 'gps' ? 'Monitoring GPS Siswa SMKN 1 Ciomas' : 'Kelola Menu Stand'}
                                    </h1>

                                    {/* Canteen Badge */}
                                    <span className="inline-flex items-center gap-1.5 text-xs font-extrabold px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        {data.is_super_admin ? 'Super Admin' : `Stand: ${((data.canteen_info && data.canteen_info.name) || 'Kantin ' + data.canteen_id)}`}
                                    </span>
                                </div>
                                <p className="text-xs text-slate-500 mt-1 font-medium">
                                    Petugas Aktif: <b className="text-slate-800">{data.user}</b> • {new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })}
                                </p>
                            </div>

                            {/* Canteen Filter Dropdown (Super Admin only) & Actions */}
                            <div className="flex items-center gap-3 flex-wrap">
                                {data.is_super_admin && (
                                    <div className="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-2xs">
                                        <span className="text-xs font-bold text-slate-500">Filter Stand:</span>
                                        <select 
                                            value={selectedCanteenFilter} 
                                            onChange={e => handleCanteenFilterChange(e.target.value)}
                                            className="text-xs font-extrabold text-slate-800 bg-transparent focus:outline-none"
                                        >
                                            <option value="">Semua Kantin (1 - 7)</option>
                                            <option value="1">Kantin 1 (Nasi & Lauk)</option>
                                            <option value="2">Kantin 2 (Sarapan Pagi)</option>
                                            <option value="3">Kantin 3 (Minuman Segar)</option>
                                            <option value="4">Kantin 4 (Cemilan & Gorengan)</option>
                                            <option value="5">Kantin 5 (Aneka Mie & Bakso)</option>
                                            <option value="6">Kantin 6 (Aneka Jus & Buah)</option>
                                            <option value="7">Kantin 7 (Paket Makan Siang)</option>
                                        </select>
                                    </div>
                                )}

                                <button 
                                    onClick={() => loadData()} 
                                    className="px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-2xs flex items-center gap-1.5 transition-all"
                                    title="Segarkan data terbaru"
                                >
                                    Refresh
                                </button>

                                {tab !== 'recap' && (
                                    <button 
                                        onClick={() => setTab('recap')} 
                                        className="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-md shadow-emerald-600/20 flex items-center gap-1.5 transition-all"
                                    >
                                        Buka Rekap Omset
                                    </button>
                                )}

                                <button 
                                    type="button"
                                    onClick={handleLogout} 
                                    className="px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 font-bold text-xs shadow-2xs flex items-center gap-1.5 transition-all"
                                    title="Keluar dari akun admin"
                                >
                                    Logout Petugas
                                </button>
                            </div>
                        </div>

                        {/* ============================================================== */}
                        {/* TAB 1: DASHBOARD OVERVIEW                                      */}
                        {/* ============================================================== */}
                        {tab === 'dashboard' && (
                            <div key="tab-dashboard" className="animate-page-enter space-y-6">
                                {/* 3 Metric Cards */}
                                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                                    <Card className="border-emerald-500 shadow-flat-primary">
                                        <div className="flex items-center justify-between mb-2">
                                            <span className="text-slate-500 font-bold text-xs uppercase tracking-wider">Omset Hari Ini</span>
                                            <span className="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs font-mono">RP</span>
                                        </div>
                                        <p className="text-3xl font-black text-dark">Rp {Number(data.stats ? data.stats.revenue : 0).toLocaleString('id-ID')}</p>
                                        <p className="text-xs text-emerald-700 font-bold mt-1">
                                            {data.stats ? data.stats.portions : 0} porsi makanan terjual hari ini
                                        </p>
                                    </Card>

                                    <Card className="border-secondary shadow-flat-secondary">
                                        <div className="flex items-center justify-between mb-2">
                                            <span className="text-slate-500 font-bold text-xs uppercase tracking-wider">Porsi Terjual</span>
                                            <span className="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs font-mono">QTY</span>
                                        </div>
                                        <p className="text-3xl font-black text-dark">{data.stats ? data.stats.portions : 0}</p>
                                        <p className="text-xs text-blue-700 font-bold mt-1">
                                            {data.stats ? (data.stats.completed_orders || 0) : 0} pesanan telah selesai diserahkan
                                        </p>
                                    </Card>

                                    <Card className="border-amber-400">
                                        <div className="flex items-center justify-between mb-2">
                                            <span className="text-slate-500 font-bold text-xs uppercase tracking-wider">Antrean Menunggu</span>
                                            <span className="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs font-mono">ANTRE</span>
                                        </div>
                                        <p className="text-3xl font-black text-dark">{data.stats ? data.stats.active_orders : 0}</p>
                                        <p className="text-xs text-amber-700 font-bold mt-1">
                                            {data.stats ? data.stats.cooking_orders || 0 : 0} dimasak • {data.stats ? data.stats.ready_orders || 0 : 0} siap diambil
                                        </p>
                                    </Card>
                                </div>

                                {/* Banner Cepat ke Rekap Penjualan */}
                                <div className="p-5 rounded-2xl bg-gradient-to-r from-emerald-800 to-teal-800 text-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-lg shadow-emerald-900/10">
                                    <div className="flex items-center gap-3.5">
                                        <div className="w-12 h-12 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-xs font-black font-mono shrink-0">
                                            EXCEL
                                        </div>
                                        <div>
                                            <h3 className="font-extrabold text-base">Rekap Data Penjualan Lengkap (Excel)</h3>
                                            <p className="text-xs text-emerald-100">
                                                Pantau omset perhari, perminggu, perbulan, pertahun dan ekspor langsung dalam format Microsoft Excel.
                                            </p>
                                        </div>
                                    </div>
                                    <button 
                                        type="button" 
                                        onClick={() => setTab('recap')}
                                        className="px-5 py-2.5 bg-white text-emerald-900 font-black text-xs rounded-xl shadow-md hover:bg-emerald-50 transition-all shrink-0"
                                    >
                                        Lihat Rekap Penjualan →
                                    </button>
                                </div>

                                {/* Daftar Pesanan Terbaru */}
                                <Card>
                                    <div className="flex justify-between items-center border-b-2 border-slate-100 pb-4 mb-4 flex-wrap gap-2">
                                        <div>
                                            <h2 className="text-xl font-extrabold text-dark">Pesanan Masuk di Loket</h2>
                                            <p className="text-xs text-slate-500">Daftar pesanan aktif dan terbaru yang masuk ke stand kantin</p>
                                        </div>
                                        <button onClick={() => setTab('scanner')} className="text-xs font-black text-emerald-700 hover:underline">
                                            Buka Loket Panggilan Tiket →
                                        </button>
                                    </div>
                                    <div className="overflow-x-auto">
                                        <table className="w-full text-left min-w-[700px]">
                                            <thead>
                                                <tr className="text-slate-500 text-xs uppercase tracking-wider border-b-2 border-slate-100">
                                                    <th className="py-3 font-bold">Tiket Loket</th>
                                                    <th className="py-3 font-bold">Waktu</th>
                                                    <th className="py-3 font-bold">Stand</th>
                                                    <th className="py-3 font-bold">Siswa</th>
                                                    <th className="py-3 font-bold">Total Harga</th>
                                                    <th className="py-3 font-bold">Status</th>
                                                    <th className="py-3 font-bold text-right">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {data.orders && data.orders.length > 0 ? (
                                                    data.orders.slice(0, 15).map(order => {
                                                        const ticketCode = order.order_code || (`K${order.canteen_id || 1}-${String(order.id).padStart(3, '0')}`);
                                                        return (
                                                            <tr key={order.id} className="border-b border-slate-50 hover:bg-slate-50/60 transition-colors">
                                                                <td className="py-3.5 font-mono font-black text-emerald-700 text-sm">
                                                                    {ticketCode}
                                                                </td>
                                                                <td className="py-3.5 font-bold text-slate-500 text-xs">
                                                                    {new Date(order.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})}
                                                                </td>
                                                                <td className="py-3.5">
                                                                    <span className="text-xs font-extrabold px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                                                                        Kantin {order.canteen_id || 1}
                                                                    </span>
                                                                </td>
                                                                <td className="py-3.5 font-bold text-dark text-xs">
                                                                    <div>{order.customer_name || ((order.user && order.user.name) ? order.user.name : 'Siswa')}</div>
                                                                    {order.is_at_school ? (
                                                                        <span className="inline-flex items-center gap-1 text-[9px] font-black uppercase px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 mt-0.5">
                                                                            SMKN 1 Ciomas ({Math.round(order.gps_distance || 0)}m)
                                                                        </span>
                                                                    ) : null}
                                                                </td>
                                                                <td className="py-3.5 text-slate-800 font-extrabold text-xs">
                                                                    Rp {Number(order.total_amount).toLocaleString('id-ID')}
                                                                </td>
                                                                <td className="py-3.5">
                                                                    <span className={`px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase ${
                                                                        order.status === 'completed' ? 'bg-green-100 text-green-700' :
                                                                        order.status === 'ready' ? 'bg-emerald-500 text-white' :
                                                                        order.status === 'cooking' ? 'bg-amber-100 text-amber-800' :
                                                                        'bg-blue-100 text-blue-700'
                                                                    }`}>
                                                                        {order.status === 'completed' ? 'Selesai' :
                                                                         order.status === 'ready' ? 'Siap Diambil' :
                                                                         order.status === 'cooking' ? 'Dimasak' : 'Menunggu'}
                                                                    </span>
                                                                </td>
                                                                <td className="py-3.5 text-right">
                                                                    {order.status !== 'completed' && (
                                                                        <Button 
                                                                            className="py-1 px-3 text-xs" 
                                                                            onClick={() => updateOrder(order.id)}
                                                                        >
                                                                            {order.status === 'pending' ? 'Mulai Masak' : order.status === 'cooking' ? 'Siap Diambil' : 'Selesaikan'}
                                                                        </Button>
                                                                    )}
                                                                </td>
                                                            </tr>
                                                        );
                                                    })
                                                ) : (
                                                    <tr>
                                                        <td colSpan="7" className="text-center py-8 text-slate-400 italic text-xs">
                                                            Belum ada pesanan masuk untuk stand ini.
                                                        </td>
                                                    </tr>
                                                )}
                                            </tbody>
                                        </table>
                                    </div>
                                </Card>
                            </div>
                        )}

                        {/* ============================================================== */}
                        {/* TAB 2: REKAP PENJUALAN & EKSPOR EXCEL (HARIAN, MINGGUAN, DLL) */}
                        {/* ============================================================== */}
                        {tab === 'recap' && (
                            <div key="tab-recap" className="animate-page-enter space-y-6">
                                {/* Banner Header Rekap dengan Tombol Download Excel */}
                                <div className="p-6 rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 text-white border border-slate-800 shadow-xl relative overflow-hidden">
                                    <div className="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-5">
                                        <div>
                                            <div className="inline-flex items-center gap-2 bg-emerald-500/20 text-emerald-300 text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider mb-2 border border-emerald-500/30">
                                                LAPORAN KEUANGAN KANTIN SMKN 1 CIOMAS
                                            </div>
                                            <h2 className="text-2xl sm:text-3xl font-black text-white tracking-tight">
                                                Rekap Omset & Laporan Penjualan Excel
                                            </h2>
                                            <p className="text-slate-300 text-xs sm:text-sm mt-1 max-w-xl">
                                                Pantau omset perhari, perminggu, perbulan, dan pertahun. Tekan tombol ekspor di bawah untuk langsung mengunduh spreadsheet Excel (.xls).
                                            </p>
                                        </div>

                                        {/* Download Buttons Group */}
                                        <div className="flex flex-wrap items-center gap-2">
                                            <button 
                                                type="button" 
                                                onClick={() => triggerServerExport('all')}
                                                className="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs rounded-xl shadow-lg shadow-emerald-600/30 flex items-center gap-2 transition-all"
                                                title="Download laporan lengkap semua periode ke Excel"
                                            >
                                                Unduh Laporan Lengkap (.xls)
                                            </button>
                                            <button 
                                                type="button" 
                                                onClick={() => triggerServerExport(recapSubTab === 'daily' ? 'today' : recapSubTab === 'weekly' ? 'week' : recapSubTab === 'monthly' ? 'month' : 'year')}
                                                className="px-3.5 py-2.5 bg-white/10 hover:bg-white/20 text-white font-bold text-xs rounded-xl border border-white/20 flex items-center gap-2 transition-all"
                                            >
                                                Unduh Sesuai Tab ({recapSubTab})
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {/* 4 KARTU KPI UTAMA OMSET: HARIAN, MINGGUAN, BULANAN, TAHUNAN */}
                                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                    {/* Hari Ini */}
                                    <Card className="border-l-4 border-l-emerald-500 hover:shadow-md transition-shadow">
                                        <div className="flex items-center justify-between mb-1">
                                            <span className="text-[11px] font-black uppercase text-slate-400 tracking-wider">Omset Hari Ini</span>
                                            <span className="text-xs font-black px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Harian</span>
                                        </div>
                                        <h3 className="text-2xl font-black text-slate-900 mt-1">
                                            Rp {Number(summary.today_revenue || 0).toLocaleString('id-ID')}
                                        </h3>
                                        <div className="mt-2 text-xs font-bold text-slate-500 flex items-center justify-between">
                                            <span>{summary.today_transactions || 0} transaksi</span>
                                            <span className="text-emerald-700">{summary.today_portions || 0} porsi</span>
                                        </div>
                                        <button 
                                            type="button" 
                                            onClick={() => triggerServerExport('today')}
                                            className="mt-3 w-full py-1.5 text-[11px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg text-center transition-colors flex items-center justify-center gap-1"
                                        >
                                            Ekspor Harian (Excel)
                                        </button>
                                    </Card>

                                    {/* Minggu Ini */}
                                    <Card className="border-l-4 border-l-blue-500 hover:shadow-md transition-shadow">
                                        <div className="flex items-center justify-between mb-1">
                                            <span className="text-[11px] font-black uppercase text-slate-400 tracking-wider">Omset Minggu Ini</span>
                                            <span className="text-xs font-black px-2 py-0.5 rounded-full bg-blue-100 text-blue-800">Mingguan</span>
                                        </div>
                                        <h3 className="text-2xl font-black text-slate-900 mt-1">
                                            Rp {Number(summary.week_revenue || 0).toLocaleString('id-ID')}
                                        </h3>
                                        <div className="mt-2 text-xs font-bold text-slate-500 flex items-center justify-between">
                                            <span>{summary.week_transactions || 0} transaksi</span>
                                            <span className="text-blue-700">{summary.week_portions || 0} porsi</span>
                                        </div>
                                        <button 
                                            type="button" 
                                            onClick={() => triggerServerExport('week')}
                                            className="mt-3 w-full py-1.5 text-[11px] font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg text-center transition-colors flex items-center justify-center gap-1"
                                        >
                                            Ekspor Mingguan (Excel)
                                        </button>
                                    </Card>

                                    {/* Bulan Ini */}
                                    <Card className="border-l-4 border-l-purple-500 hover:shadow-md transition-shadow">
                                        <div className="flex items-center justify-between mb-1">
                                            <span className="text-[11px] font-black uppercase text-slate-400 tracking-wider">Omset Bulan Ini</span>
                                            <span className="text-xs font-black px-2 py-0.5 rounded-full bg-purple-100 text-purple-800">Bulanan</span>
                                        </div>
                                        <h3 className="text-2xl font-black text-slate-900 mt-1">
                                            Rp {Number(summary.month_revenue || 0).toLocaleString('id-ID')}
                                        </h3>
                                        <div className="mt-2 text-xs font-bold text-slate-500 flex items-center justify-between">
                                            <span>{summary.month_transactions || 0} transaksi</span>
                                            <span className="text-purple-700">{summary.month_portions || 0} porsi</span>
                                        </div>
                                        <button 
                                            type="button" 
                                            onClick={() => triggerServerExport('month')}
                                            className="mt-3 w-full py-1.5 text-[11px] font-bold text-purple-700 bg-purple-50 hover:bg-purple-100 rounded-lg text-center transition-colors flex items-center justify-center gap-1"
                                        >
                                            Ekspor Bulanan (Excel)
                                        </button>
                                    </Card>

                                    {/* Tahun Ini */}
                                    <Card className="border-l-4 border-l-amber-500 hover:shadow-md transition-shadow">
                                        <div className="flex items-center justify-between mb-1">
                                            <span className="text-[11px] font-black uppercase text-slate-400 tracking-wider">Omset Tahun Ini</span>
                                            <span className="text-xs font-black px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">Tahunan</span>
                                        </div>
                                        <h3 className="text-2xl font-black text-slate-900 mt-1">
                                            Rp {Number(summary.year_revenue || 0).toLocaleString('id-ID')}
                                        </h3>
                                        <div className="mt-2 text-xs font-bold text-slate-500 flex items-center justify-between">
                                            <span>{summary.year_transactions || 0} transaksi</span>
                                            <span className="text-amber-700">{summary.year_portions || 0} porsi</span>
                                        </div>
                                        <button 
                                            type="button" 
                                            onClick={() => triggerServerExport('year')}
                                            className="mt-3 w-full py-1.5 text-[11px] font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-lg text-center transition-colors flex items-center justify-center gap-1"
                                        >
                                            Ekspor Tahunan (Excel)
                                        </button>
                                    </Card>
                                </div>

                                {/* TABEL RINCIAN PERIODIK DENGAN SUBTABS */}
                                <Card>
                                    {/* Subtab Selector */}
                                    <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b-2 border-slate-100 pb-4 mb-5 gap-3">
                                        <div className="flex space-x-1 sm:space-x-2 overflow-x-auto pb-1 max-w-full">
                                            {[
                                                { id: 'daily', label: 'Rekap Harian' },
                                                { id: 'weekly', label: 'Rekap Mingguan' },
                                                { id: 'monthly', label: 'Rekap Bulanan' },
                                                { id: 'yearly', label: 'Rekap Tahunan' },
                                                { id: 'transactions', label: 'Rincian Transaksi' }
                                            ].map(st => (
                                                <button
                                                    key={st.id}
                                                    type="button"
                                                    onClick={() => setRecapSubTab(st.id)}
                                                    className={`px-3.5 py-2 rounded-xl text-xs font-extrabold whitespace-nowrap transition-all ${recapSubTab === st.id ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'}`}
                                                >
                                                    {st.label}
                                                </button>
                                            ))}
                                        </div>

                                        {/* Tombol Ekspor Tabel yang sedang aktif */}
                                        <button 
                                            type="button" 
                                            onClick={() => exportTableToExcel(`table-recap-${recapSubTab}`, `Rekap_${recapSubTab}_${new Date().toISOString().slice(0,10)}.xls`)}
                                            className="px-3.5 py-1.5 rounded-xl border-2 border-emerald-500 text-emerald-800 bg-emerald-50 hover:bg-emerald-100 font-black text-xs transition-colors flex items-center gap-1.5 shrink-0"
                                            title="Download tabel ini ke format Excel"
                                        >
                                            Unduh Tabel Ini (.xls)
                                        </button>
                                    </div>

                                    {/* SUBTAB 1: REKAP HARIAN */}
                                    {recapSubTab === 'daily' && (
                                        <div className="overflow-x-auto">
                                            <table id="table-recap-daily" className="w-full text-left min-w-[650px]">
                                                <thead>
                                                    <tr className="text-slate-500 text-xs uppercase tracking-wider border-b-2 border-slate-100 bg-slate-50/50">
                                                        <th className="py-3 px-3 font-bold">Tanggal</th>
                                                        <th className="py-3 px-3 font-bold text-center">Jumlah Transaksi</th>
                                                        <th className="py-3 px-3 font-bold text-center">Porsi Terjual</th>
                                                        <th className="py-3 px-3 font-bold text-right">Total Omset (Rp)</th>
                                                        <th className="py-3 px-3 font-bold text-right">Rata-rata/Trx</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {recap.daily && recap.daily.length > 0 ? (
                                                        recap.daily.map((row, idx) => {
                                                            const avg = row.transactions > 0 ? Math.round(row.revenue / row.transactions) : 0;
                                                            return (
                                                                <tr key={idx} className="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                                                    <td className="py-3 px-3 font-bold text-slate-800 text-xs">
                                                                        {row.label || row.date}
                                                                    </td>
                                                                    <td className="py-3 px-3 text-center font-bold text-slate-700 text-xs">
                                                                        <span className="px-2.5 py-1 rounded-full bg-slate-100 text-slate-800">
                                                                            {row.transactions} trx
                                                                        </span>
                                                                    </td>
                                                                    <td className="py-3 px-3 text-center font-bold text-emerald-800 text-xs">
                                                                        {row.portions} porsi
                                                                    </td>
                                                                    <td className="py-3 px-3 text-right font-black text-emerald-700 text-sm">
                                                                        Rp {Number(row.revenue).toLocaleString('id-ID')}
                                                                    </td>
                                                                    <td className="py-3 px-3 text-right font-medium text-slate-500 text-xs">
                                                                        Rp {avg.toLocaleString('id-ID')}
                                                                    </td>
                                                                </tr>
                                                            );
                                                        })
                                                    ) : (
                                                        <tr>
                                                            <td colSpan="5" className="text-center py-8 text-slate-400 italic text-xs">
                                                                Belum ada data rekap harian untuk stand ini.
                                                            </td>
                                                        </tr>
                                                    )}
                                                </tbody>
                                                <tfoot>
                                                    <tr className="bg-slate-100/80 font-black text-slate-900 border-t-2 border-slate-300">
                                                        <td className="py-3 px-3 text-xs uppercase">TOTAL REKAP HARIAN</td>
                                                        <td className="py-3 px-3 text-center text-xs">
                                                            {(recap.daily || []).reduce((acc, r) => acc + (r.transactions || 0), 0)} trx
                                                        </td>
                                                        <td className="py-3 px-3 text-center text-xs text-emerald-800">
                                                            {(recap.daily || []).reduce((acc, r) => acc + (r.portions || 0), 0)} porsi
                                                        </td>
                                                        <td className="py-3 px-3 text-right text-emerald-700 text-base">
                                                            Rp {(recap.daily || []).reduce((acc, r) => acc + (r.revenue || 0), 0).toLocaleString('id-ID')}
                                                        </td>
                                                        <td className="py-3 px-3 text-right text-xs text-slate-500">-</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    )}

                                    {/* SUBTAB 2: REKAP MINGGUAN */}
                                    {recapSubTab === 'weekly' && (
                                        <div className="overflow-x-auto">
                                            <table id="table-recap-weekly" className="w-full text-left min-w-[650px]">
                                                <thead>
                                                    <tr className="text-slate-500 text-xs uppercase tracking-wider border-b-2 border-slate-100 bg-slate-50/50">
                                                        <th className="py-3 px-3 font-bold">Periode Minggu</th>
                                                        <th className="py-3 px-3 font-bold text-center">Jumlah Transaksi</th>
                                                        <th className="py-3 px-3 font-bold text-center">Porsi Terjual</th>
                                                        <th className="py-3 px-3 font-bold text-right">Total Omset (Rp)</th>
                                                        <th className="py-3 px-3 font-bold text-right">Status Penjualan</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {recap.weekly && recap.weekly.length > 0 ? (
                                                        recap.weekly.map((row, idx) => (
                                                            <tr key={idx} className="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                                                <td className="py-3 px-3 font-bold text-slate-800 text-xs">
                                                                    {row.label || row.week}
                                                                </td>
                                                                <td className="py-3 px-3 text-center font-bold text-slate-700 text-xs">
                                                                    <span className="px-2.5 py-1 rounded-full bg-blue-100 text-blue-800">
                                                                        {row.transactions} trx
                                                                    </span>
                                                                </td>
                                                                <td className="py-3 px-3 text-center font-bold text-blue-800 text-xs">
                                                                    {row.portions} porsi
                                                                </td>
                                                                <td className="py-3 px-3 text-right font-black text-emerald-700 text-sm">
                                                                    Rp {Number(row.revenue).toLocaleString('id-ID')}
                                                                </td>
                                                                <td className="py-3 px-3 text-right">
                                                                    <span className="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-emerald-100 text-emerald-800">
                                                                        Aktif
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        ))
                                                    ) : (
                                                        <tr>
                                                            <td colSpan="5" className="text-center py-8 text-slate-400 italic text-xs">
                                                                Belum ada data rekap mingguan untuk stand ini.
                                                            </td>
                                                        </tr>
                                                    )}
                                                </tbody>
                                                <tfoot>
                                                    <tr className="bg-slate-100/80 font-black text-slate-900 border-t-2 border-slate-300">
                                                        <td className="py-3 px-3 text-xs uppercase">TOTAL REKAP MINGGUAN</td>
                                                        <td className="py-3 px-3 text-center text-xs">
                                                            {(recap.weekly || []).reduce((acc, r) => acc + (r.transactions || 0), 0)} trx
                                                        </td>
                                                        <td className="py-3 px-3 text-center text-xs text-blue-800">
                                                            {(recap.weekly || []).reduce((acc, r) => acc + (r.portions || 0), 0)} porsi
                                                        </td>
                                                        <td className="py-3 px-3 text-right text-emerald-700 text-base">
                                                            Rp {(recap.weekly || []).reduce((acc, r) => acc + (r.revenue || 0), 0).toLocaleString('id-ID')}
                                                        </td>
                                                        <td className="py-3 px-3 text-right text-xs text-slate-500">-</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    )}

                                    {/* SUBTAB 3: REKAP BULANAN */}
                                    {recapSubTab === 'monthly' && (
                                        <div className="overflow-x-auto">
                                            <table id="table-recap-monthly" className="w-full text-left min-w-[650px]">
                                                <thead>
                                                    <tr className="text-slate-500 text-xs uppercase tracking-wider border-b-2 border-slate-100 bg-slate-50/50">
                                                        <th className="py-3 px-3 font-bold">Bulan & Tahun</th>
                                                        <th className="py-3 px-3 font-bold text-center">Jumlah Transaksi</th>
                                                        <th className="py-3 px-3 font-bold text-center">Porsi Terjual</th>
                                                        <th className="py-3 px-3 font-bold text-right">Total Omset (Rp)</th>
                                                        <th className="py-3 px-3 font-bold text-right">Laporan Kasir</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {recap.monthly && recap.monthly.length > 0 ? (
                                                        recap.monthly.map((row, idx) => (
                                                            <tr key={idx} className="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                                                <td className="py-3 px-3 font-bold text-slate-800 text-xs">
                                                                    {row.label || row.month}
                                                                </td>
                                                                <td className="py-3 px-3 text-center font-bold text-slate-700 text-xs">
                                                                    <span className="px-2.5 py-1 rounded-full bg-purple-100 text-purple-800">
                                                                        {row.transactions} trx
                                                                    </span>
                                                                </td>
                                                                <td className="py-3 px-3 text-center font-bold text-purple-800 text-xs">
                                                                    {row.portions} porsi
                                                                </td>
                                                                <td className="py-3 px-3 text-right font-black text-emerald-700 text-sm">
                                                                    Rp {Number(row.revenue).toLocaleString('id-ID')}
                                                                </td>
                                                                <td className="py-3 px-3 text-right">
                                                                    <span className="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-purple-100 text-purple-800">
                                                                        Terekap
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        ))
                                                    ) : (
                                                        <tr>
                                                            <td colSpan="5" className="text-center py-8 text-slate-400 italic text-xs">
                                                                Belum ada data rekap bulanan untuk stand ini.
                                                            </td>
                                                        </tr>
                                                    )}
                                                </tbody>
                                                <tfoot>
                                                    <tr className="bg-slate-100/80 font-black text-slate-900 border-t-2 border-slate-300">
                                                        <td className="py-3 px-3 text-xs uppercase">TOTAL REKAP BULANAN</td>
                                                        <td className="py-3 px-3 text-center text-xs">
                                                            {(recap.monthly || []).reduce((acc, r) => acc + (r.transactions || 0), 0)} trx
                                                        </td>
                                                        <td className="py-3 px-3 text-center text-xs text-purple-800">
                                                            {(recap.monthly || []).reduce((acc, r) => acc + (r.portions || 0), 0)} porsi
                                                        </td>
                                                        <td className="py-3 px-3 text-right text-emerald-700 text-base">
                                                            Rp {(recap.monthly || []).reduce((acc, r) => acc + (r.revenue || 0), 0).toLocaleString('id-ID')}
                                                        </td>
                                                        <td className="py-3 px-3 text-right text-xs text-slate-500">-</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    )}

                                    {/* SUBTAB 4: REKAP TAHUNAN */}
                                    {recapSubTab === 'yearly' && (
                                        <div className="overflow-x-auto">
                                            <table id="table-recap-yearly" className="w-full text-left min-w-[650px]">
                                                <thead>
                                                    <tr className="text-slate-500 text-xs uppercase tracking-wider border-b-2 border-slate-100 bg-slate-50/50">
                                                        <th className="py-3 px-3 font-bold">Tahun Anggaran</th>
                                                        <th className="py-3 px-3 font-bold text-center">Jumlah Transaksi</th>
                                                        <th className="py-3 px-3 font-bold text-center">Porsi Terjual</th>
                                                        <th className="py-3 px-3 font-bold text-right">Total Omset (Rp)</th>
                                                        <th className="py-3 px-3 font-bold text-right">Status Pajak & Kas</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {recap.yearly && recap.yearly.length > 0 ? (
                                                        recap.yearly.map((row, idx) => (
                                                            <tr key={idx} className="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                                                <td className="py-3 px-3 font-bold text-slate-800 text-xs">
                                                                    Tahun {row.label || row.year}
                                                                </td>
                                                                <td className="py-3 px-3 text-center font-bold text-slate-700 text-xs">
                                                                    <span className="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800">
                                                                        {row.transactions} trx
                                                                    </span>
                                                                </td>
                                                                <td className="py-3 px-3 text-center font-bold text-amber-800 text-xs">
                                                                    {row.portions} porsi
                                                                </td>
                                                                <td className="py-3 px-3 text-right font-black text-emerald-700 text-sm">
                                                                    Rp {Number(row.revenue).toLocaleString('id-ID')}
                                                                </td>
                                                                <td className="py-3 px-3 text-right">
                                                                    <span className="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-amber-100 text-amber-800">
                                                                        Selesai / Diaudit
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        ))
                                                    ) : (
                                                        <tr>
                                                            <td colSpan="5" className="text-center py-8 text-slate-400 italic text-xs">
                                                                Belum ada data rekap tahunan untuk stand ini.
                                                            </td>
                                                        </tr>
                                                    )}
                                                </tbody>
                                                <tfoot>
                                                    <tr className="bg-slate-100/80 font-black text-slate-900 border-t-2 border-slate-300">
                                                        <td className="py-3 px-3 text-xs uppercase">TOTAL REKAP TAHUNAN</td>
                                                        <td className="py-3 px-3 text-center text-xs">
                                                            {(recap.yearly || []).reduce((acc, r) => acc + (r.transactions || 0), 0)} trx
                                                        </td>
                                                        <td className="py-3 px-3 text-center text-xs text-amber-800">
                                                            {(recap.yearly || []).reduce((acc, r) => acc + (r.portions || 0), 0)} porsi
                                                        </td>
                                                        <td className="py-3 px-3 text-right text-emerald-700 text-base">
                                                            Rp {(recap.yearly || []).reduce((acc, r) => acc + (r.revenue || 0), 0).toLocaleString('id-ID')}
                                                        </td>
                                                        <td className="py-3 px-3 text-right text-xs text-slate-500">-</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    )}

                                    {/* SUBTAB 5: DAFTAR TRANSAKSI LENGKAP */}
                                    {recapSubTab === 'transactions' && (
                                        <div className="overflow-x-auto">
                                            <table id="table-recap-transactions" className="w-full text-left min-w-[750px]">
                                                <thead>
                                                    <tr className="text-slate-500 text-xs uppercase tracking-wider border-b-2 border-slate-100 bg-slate-50/50">
                                                        <th className="py-3 px-3 font-bold">Tiket</th>
                                                        <th className="py-3 px-3 font-bold">Waktu</th>
                                                        <th className="py-3 px-3 font-bold">Stand</th>
                                                        <th className="py-3 px-3 font-bold">Nama Siswa</th>
                                                        <th className="py-3 px-3 font-bold">Rincian Menu</th>
                                                        <th className="py-3 px-3 font-bold text-center">Metode</th>
                                                        <th className="py-3 px-3 font-bold text-right">Total (Rp)</th>
                                                        <th className="py-3 px-3 font-bold text-right">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {recap.recent_transactions && recap.recent_transactions.length > 0 ? (
                                                        recap.recent_transactions.map((tx, idx) => (
                                                            <tr key={idx} className="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                                                <td className="py-3 px-3 font-mono font-black text-emerald-700 text-xs">
                                                                    {tx.order_code}
                                                                </td>
                                                                <td className="py-3 px-3 text-slate-500 text-xs">
                                                                    {new Date(tx.created_at).toLocaleString('id-ID')}
                                                                </td>
                                                                <td className="py-3 px-3 font-bold text-xs text-slate-700">
                                                                    Kantin {tx.canteen_id || 1}
                                                                </td>
                                                                <td className="py-3 px-3 font-bold text-slate-900 text-xs">
                                                                    {tx.customer_name}
                                                                </td>
                                                                <td className="py-3 px-3 text-slate-600 text-xs max-w-xs truncate" title={tx.items_summary}>
                                                                    {tx.items_summary}
                                                                </td>
                                                                <td className="py-3 px-3 text-center">
                                                                    <span className="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                                                        {tx.payment_method || 'Cash'}
                                                                    </span>
                                                                </td>
                                                                <td className="py-3 px-3 text-right font-black text-emerald-700 text-xs">
                                                                    Rp {Number(tx.total_amount).toLocaleString('id-ID')}
                                                                </td>
                                                                <td className="py-3 px-3 text-right">
                                                                    <span className={`px-2 py-0.5 rounded text-[10px] font-bold uppercase ${
                                                                        tx.status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-800'
                                                                    }`}>
                                                                        {tx.status}
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        ))
                                                    ) : (
                                                        <tr>
                                                            <td colSpan="8" className="text-center py-8 text-slate-400 italic text-xs">
                                                                Belum ada data transaksi rincian.
                                                            </td>
                                                        </tr>
                                                    )}
                                                </tbody>
                                            </table>
                                        </div>
                                    )}
                                </Card>
                            </div>
                        )}

                        {/* ============================================================== */}
                        {/* TAB 3: KELOLA MENU (DENGAN KANTIN 1 - 7 SCOPE)                 */}
                        {/* ============================================================== */}
                        {tab === 'menu' && (
                            <div key="tab-menu" className="animate-page-enter grid grid-cols-1 lg:grid-cols-2 gap-8">
                                <Card>
                                    <h2 className="text-xl font-extrabold text-dark border-b-2 border-slate-100 pb-4 mb-4">
                                        {editingMenuId ? "Edit Menu Stand" : "Tambah Menu Baru"}
                                    </h2>
                                    <form onSubmit={handleAddMenu} className="space-y-4">
                                        <div>
                                            <label className="block text-sm font-bold text-slate-700 mb-1">Stand Kantin (1 - 7)</label>
                                            {data.is_super_admin ? (
                                                <select 
                                                    value={newMenu.canteen_id || 1} 
                                                    onChange={e => setNewMenu(Object.assign({}, newMenu, {canteen_id: Number(e.target.value)}))} 
                                                    className="w-full border-2 border-slate-200 rounded-xl px-4 py-2 font-bold text-slate-800 focus:border-dark focus:outline-none"
                                                >
                                                    <option value="1">[K1] Kantin 1 - Nasi & Lauk Pauk</option>
                                                    <option value="2">[K2] Kantin 2 - Sarapan Pagi</option>
                                                    <option value="3">[K3] Kantin 3 - Minuman Segar</option>
                                                    <option value="4">[K4] Kantin 4 - Cemilan & Gorengan</option>
                                                    <option value="5">[K5] Kantin 5 - Aneka Mie & Bakso</option>
                                                    <option value="6">[K6] Kantin 6 - Aneka Jus & Buah</option>
                                                    <option value="7">[K7] Kantin 7 - Paket Makan Siang</option>
                                                </select>
                                            ) : (
                                                <div className="p-3 bg-emerald-50 rounded-xl border border-emerald-200 font-extrabold text-sm text-emerald-900 flex items-center gap-2">
                                                    {((data.canteen_info && data.canteen_info.name) || 'Kantin ' + (data.canteen_id || 1))} ({((data.canteen_info && data.canteen_info.desc) || 'Stand Terkunci')})
                                                </div>
                                            )}
                                        </div>

                                        <div>
                                            <label className="block text-sm font-bold text-slate-700 mb-1">Nama Menu</label>
                                            <input type="text" value={newMenu.name} onChange={e => setNewMenu(Object.assign({}, newMenu, {name: e.target.value}))} required className="w-full border-2 border-slate-200 rounded-xl px-4 py-2 focus:border-dark focus:outline-none font-bold" />
                                        </div>

                                        <div>
                                            <label className="block text-sm font-bold text-slate-700 mb-1">Kategori Menu</label>
                                            <select value={newMenu.category} onChange={e => setNewMenu(Object.assign({}, newMenu, {category: e.target.value}))} className="w-full border-2 border-slate-200 rounded-xl px-4 py-2 focus:border-dark focus:outline-none">
                                                <option value="Sarapan">Sarapan Pagi</option>
                                                <option value="Makan Siang">Makan Siang</option>
                                                <option value="Minuman">Minuman Segar</option>
                                                <option value="Cemilan">Cemilan / Snack</option>
                                            </select>
                                        </div>

                                        <div className="grid grid-cols-2 gap-4">
                                            <div>
                                                <label className="block text-sm font-bold text-slate-700 mb-1">Harga (Rp)</label>
                                                <input type="number" value={newMenu.price} onChange={e => setNewMenu(Object.assign({}, newMenu, {price: e.target.value}))} required className="w-full border-2 border-slate-200 rounded-xl px-4 py-2 focus:border-dark focus:outline-none font-bold" />
                                            </div>
                                            <div>
                                                <label className="block text-sm font-bold text-slate-700 mb-1">Stok Porsi</label>
                                                <input type="number" value={newMenu.stock} onChange={e => setNewMenu(Object.assign({}, newMenu, {stock: e.target.value}))} required className="w-full border-2 border-slate-200 rounded-xl px-4 py-2 focus:border-dark focus:outline-none font-bold" />
                                            </div>
                                        </div>

                                        <div>
                                            <label className="block text-sm font-bold text-slate-700 mb-1">
                                                {editingMenuId ? "Ganti Foto Menu (Opsional)" : "Foto Menu"}
                                            </label>
                                            <input type="file" accept="image/*" onChange={e => setNewMenuImage(e.target.files[0])} className="w-full border-2 border-slate-200 rounded-xl px-4 py-2" />
                                        </div>

                                        <div className="flex gap-2 pt-2">
                                            <Button type="submit" className="flex-1">{editingMenuId ? "Simpan Perubahan" : "Simpan Menu Stand"}</Button>
                                            {editingMenuId && (
                                                <Button type="button" variant="outline" className="flex-1" onClick={() => {
                                                    setEditingMenuId(null);
                                                    setNewMenu({ name: '', category: 'Sarapan', price: '', stock: '', canteen_id: data.canteen_id || 1 });
                                                    setNewMenuImage(null);
                                                }}>Batal</Button>
                                            )}
                                        </div>
                                    </form>
                                </Card>

                                <Card>
                                    <div className="flex justify-between items-center border-b-2 border-slate-100 pb-4 mb-4">
                                        <h2 className="text-xl font-extrabold text-dark">Daftar Menu Stand ({data.menus ? data.menus.length : 0})</h2>
                                    </div>
                                    <div className="space-y-3 max-h-[600px] overflow-y-auto pr-1">
                                        {data.menus && data.menus.map(menu => (
                                            <div key={menu.id} className="flex items-center space-x-3.5 border-b border-slate-100 pb-3">
                                                <div className="w-14 h-14 bg-slate-100 rounded-xl overflow-hidden shrink-0 flex items-center justify-center p-1 border border-slate-200">
                                                    {menu.image ? (
                                                        <img src={`/storage/${menu.image}`} className="w-full h-full object-contain" />
                                                    ) : (
                                                        <span className="text-[10px] font-mono font-bold text-slate-400">NO FOTO</span>
                                                    )}
                                                </div>
                                                <div className="flex-1 min-w-0">
                                                    <div className="flex items-center gap-1.5 mb-0.5">
                                                        <span className="text-[10px] font-black uppercase px-2 py-0.5 rounded bg-slate-900 text-white">
                                                            Kantin {menu.canteen_id || 1}
                                                        </span>
                                                        <span className="text-[10px] text-slate-400 font-bold">
                                                            {menu.category}
                                                        </span>
                                                    </div>
                                                    <p className="font-bold text-dark text-sm truncate">{menu.name}</p>
                                                    <p className="text-xs font-black text-primary">
                                                        Rp {Number(menu.price).toLocaleString('id-ID')} • <span className="font-medium text-slate-500">Stok: {menu.stock}</span>
                                                    </p>
                                                </div>
                                                <div className="flex flex-col gap-1 shrink-0">
                                                    <button onClick={() => handleEditMenu(menu)} className="px-3 py-1 bg-amber-100 text-amber-800 font-bold rounded-lg hover:bg-amber-200 text-xs">Edit</button>
                                                    <button onClick={() => handleDeleteMenu(menu.id)} className="px-3 py-1 bg-red-100 text-red-700 font-bold rounded-lg hover:bg-red-200 text-xs">Hapus</button>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </Card>
                            </div>
                        )}

                        {/* ============================================================== */}
                        {/* TAB 4: PENGATURAN                                              */}
                        {/* ============================================================== */}
                        {tab === 'settings' && (
                            <div key="tab-settings" className="animate-page-enter max-w-2xl">
                                <Card>
                                    <h2 className="text-xl font-extrabold text-dark border-b-2 border-slate-100 pb-4 mb-4">
                                        Pengaturan Tampilan & Pembayaran Loket
                                    </h2>
                                    <form onSubmit={handleSaveSettings} className="space-y-6">
                                        <div>
                                            <h3 className="font-bold text-dark mb-2">Landing Page Promo</h3>
                                            <div className="space-y-4 border p-4 rounded-xl">
                                                <div>
                                                    <label className="block text-sm font-bold text-slate-700 mb-1">Status Promo / Pengumuman Loket</label>
                                                    <input 
                                                        type="text" 
                                                        value={settingsData.promo_status} 
                                                        onChange={e => setSettingsData(Object.assign({}, settingsData, {promo_status: e.target.value}))} 
                                                        placeholder="Otomatis mengikuti live antrean pesanan (Kosongkan untuk mode otomatis)" 
                                                        className="w-full border-2 border-slate-200 rounded-xl px-4 py-2 text-sm focus:border-dark focus:outline-none" 
                                                    />
                                                    <p className="text-[11px] text-slate-400 mt-1">
                                                        💡 <i>Tips:</i> Kosongkan kolom ini jika ingin status pesanan di halaman utama otomatis menampilkan antrean real-time dari loket kantin 1 sampai 7.
                                                    </p>
                                                </div>
                                                <div>
                                                    <label className="block text-sm font-bold text-slate-700 mb-1">Ganti Foto Promo</label>
                                                    <input type="file" accept="image/*" onChange={e => setPromoImageFile(e.target.files[0])} className="w-full border-2 border-slate-200 rounded-xl px-4 py-2" />
                                                    {data.settings && data.settings.promo_image && (
                                                        <div className="mt-2 text-sm text-green-600 font-bold">✓ Foto saat ini terpasang</div>
                                                    )}
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <h3 className="font-bold text-dark mb-2">Metode Pembayaran (Transfer/QRIS)</h3>
                                            <div className="space-y-4 border p-4 rounded-xl">
                                                <div>
                                                    <label className="block text-sm font-bold text-slate-700 mb-1">Nomor Rekening / Info Transfer</label>
                                                    <input type="text" value={settingsData.bank_account} onChange={e => setSettingsData(Object.assign({}, settingsData, {bank_account: e.target.value}))} className="w-full border-2 border-slate-200 rounded-xl px-4 py-2" />
                                                </div>
                                                <div>
                                                    <label className="block text-sm font-bold text-slate-700 mb-1">Upload QRIS</label>
                                                    <input type="file" accept="image/*" onChange={e => setQrisImageFile(e.target.files[0])} className="w-full border-2 border-slate-200 rounded-xl px-4 py-2" />
                                                    {data.settings && data.settings.qris_image && (
                                                        <div className="mt-2 text-sm text-green-600 font-bold">✓ QRIS saat ini terpasang</div>
                                                    )}
                                                </div>
                                            </div>
                                        </div>

                                        <Button type="submit" className="w-full py-3">Simpan Pengaturan</Button>
                                    </form>
                                </Card>
                            </div>
                        )}

                        {/* ============================================================== */}
                        {/* TAB 5: SCANNER / PANGGILAN ANTREAN LOKET KANTIN 1 - 7          */}
                        {/* ============================================================== */}
                        {tab === 'scanner' && (() => {
                            const raw = scanInput.trim().toUpperCase();
                            const clean = raw.replace('KANTIN-ORDER-', '').replace(/^#/, '');
                            
                            // Match by order_code (e.g. K1-001, K3-005), ID, group_code, or customer name
                            const matchedOrder = (data.orders || []).find(o => {
                                if (o.order_code && o.order_code.toUpperCase() === clean) return true;
                                if (String(o.id) === clean.replace(/^A/, '')) return true;
                                if (o.order_code && o.order_code.toUpperCase().replace(/^K\d+-/, '') === clean.replace(/^0+/, '')) return true;
                                if (o.group_code && o.group_code.toUpperCase().includes(clean)) return true;
                                const cust = (o.customer_name || '').toUpperCase();
                                if (cust && cust.includes(clean) && clean.length >= 3) return true;
                                return false;
                            });
                            const hasTyped = clean.length > 0;

                            const handleCompleteOrder = (order) => {
                                updateOrder(order.id, 'completed').then(() => {
                                    const namaCustomer = order.customer_name || ((order.user && order.user.name) ? order.user.name : 'Siswa');
                                    setScanResult({ 
                                        success: true, 
                                        message: `Pesanan ${order.order_code || ('#' + order.id)} atas nama ${namaCustomer} berhasil diserahkan & selesai!` 
                                    });
                                });
                            };

                            const handleAdvanceOrder = (order) => {
                                const nextStatus = order.status === 'pending' ? 'cooking' : 'ready';
                                updateOrder(order.id, nextStatus).then(() => {
                                    setScanResult({
                                        success: true,
                                        message: `Status pesanan ${order.order_code || ('#' + order.id)} diubah menjadi ${nextStatus === 'cooking' ? 'Sedang Dimasak' : 'Siap Diambil di Loket'}!`
                                    });
                                });
                            };

                            return (
                                <div key="tab-scanner" className="animate-page-enter max-w-3xl mx-auto mt-2 space-y-6">
                                    <Card className="border-2 border-slate-200 shadow-sm text-center py-8 px-6">
                                        <div className="w-16 h-16 bg-emerald-50 text-emerald-600 border-2 border-emerald-200 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4 shadow-sm">
                                            🎫
                                        </div>
                                        <h2 className="text-2xl font-black text-dark mb-1">Loket Panggilan Pesanan Stand</h2>
                                        <p className="text-slate-500 mb-6 font-medium text-sm max-w-lg mx-auto">
                                            Ketik nomor tiket siswa (contoh: <b className="text-emerald-700">K1-001</b> atau <b className="text-emerald-700">K3-005</b> atau nama siswa) untuk melihat menu dan menyerahkan pesanan.
                                        </p>
                                        
                                        <div className="max-w-md mx-auto">
                                            <label className="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 text-left">
                                                Nomor Tiket Loket
                                            </label>
                                            <div className="relative">
                                                <span className="absolute left-4 top-1/2 -translate-y-1/2 text-2xl font-black text-slate-400">🎫</span>
                                                <input 
                                                    type="text" 
                                                    value={scanInput} 
                                                    onChange={e => {
                                                        setScanInput(e.target.value);
                                                        setScanResult(null);
                                                    }} 
                                                    placeholder="Contoh: K3-001"
                                                    className="w-full text-center text-2xl sm:text-3xl font-black tracking-wider border-2 border-slate-300 rounded-2xl pl-12 pr-12 py-3.5 focus:border-emerald-600 focus:outline-none shadow-sm transition-all bg-white font-mono"
                                                    autoFocus
                                                />
                                                {scanInput && (
                                                    <button 
                                                        type="button" 
                                                        onClick={() => { setScanInput(''); setScanResult(null); }}
                                                        className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 font-bold text-xl w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100"
                                                        title="Hapus"
                                                    >
                                                        ✕
                                                    </button>
                                                )}
                                            </div>
                                        </div>

                                        {/* Quick Pick Daftar Antrean Aktif */}
                                        {data.orders && (() => {
                                            const activeOrders = data.orders.filter(o => o.status !== 'completed');
                                            if (activeOrders.length === 0) return null;
                                            return (
                                                <div className="mt-6 pt-5 border-t border-slate-100">
                                                    <p className="text-xs font-bold text-slate-400 mb-2 uppercase tracking-wider">
                                                        Klik Cepat Antrean yang Menunggu ({activeOrders.length}):
                                                    </p>
                                                    <div className="flex flex-wrap justify-center gap-2 max-h-32 overflow-y-auto p-1">
                                                        {activeOrders.map(o => {
                                                            const tCode = o.order_code || (`K${o.canteen_id || 1}-${String(o.id).padStart(3, '0')}`);
                                                            return (
                                                            <button 
                                                                key={o.id}
                                                                type="button"
                                                                onClick={() => {
                                                                    setScanInput(tCode);
                                                                    setScanResult(null);
                                                                }}
                                                                className={`text-xs font-extrabold px-3 py-1.5 rounded-full border transition-all flex items-center gap-1.5 ${matchedOrder && matchedOrder.id === o.id ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border-slate-200'}`}
                                                            >
                                                                <span className="font-mono">{tCode}</span>
                                                                <span className="font-normal opacity-80 truncate max-w-[90px]">{o.customer_name || (o.user ? o.user.name : 'Siswa')}</span>
                                                            </button>
                                                            );
                                                        })}
                                                    </div>
                                                </div>
                                            );
                                        })()}

                                        {scanResult && (
                                            <div className={`mt-6 p-4 rounded-xl border-2 font-bold text-left flex items-start gap-3 ${scanResult.success ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700'}`}>
                                                <span className="text-xl">{scanResult.success ? '✅' : '❌'}</span>
                                                <div className="flex-1">{scanResult.message}</div>
                                            </div>
                                        )}
                                    </Card>

                                    {/* Hasil Rincian Pesanan & Menu yang Dipesan */}
                                    {matchedOrder ? (
                                        <Card className="border-2 border-emerald-500/40 shadow-lg overflow-hidden p-0">
                                            <div className="bg-gradient-to-r from-slate-900 to-slate-800 text-white p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                                <div>
                                                    <div className="flex items-center gap-2.5 mb-1.5">
                                                        <span className="bg-emerald-600 text-white text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider font-mono">
                                                            Tiket: {matchedOrder.order_code || `K${matchedOrder.canteen_id}-${matchedOrder.id}`}
                                                        </span>
                                                        <span className="text-xs text-slate-300 font-bold bg-white/10 px-2.5 py-1 rounded-full">
                                                            Stand Kantin {matchedOrder.canteen_id || 1}
                                                        </span>
                                                    </div>
                                                    <h3 className="text-2xl font-black text-white">
                                                        {matchedOrder.customer_name || ((matchedOrder.user && matchedOrder.user.name) ? matchedOrder.user.name : 'Siswa')}
                                                    </h3>
                                                    <p className="text-xs text-slate-400 mt-1">
                                                        Dipesan pada: {new Date(matchedOrder.created_at).toLocaleString('id-ID')}
                                                    </p>
                                                </div>

                                                <div className="text-left sm:text-right">
                                                    <span className={`inline-block px-3 py-1.5 rounded-full text-xs font-extrabold ${
                                                        matchedOrder.status === 'completed' ? 'bg-green-500/20 text-green-300 border border-green-500/30' :
                                                        matchedOrder.status === 'ready' ? 'bg-emerald-500 text-white shadow-sm' :
                                                        matchedOrder.status === 'cooking' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' :
                                                        'bg-blue-500/20 text-blue-300 border border-blue-500/30'
                                                    }`}>
                                                        {matchedOrder.status === 'completed' ? '✓ Sudah Diambil / Selesai' :
                                                         matchedOrder.status === 'ready' ? '🔔 Siap Diambil di Loket' :
                                                         matchedOrder.status === 'cooking' ? '🍳 Sedang Dimasak' : '⏳ Menunggu'}
                                                    </span>
                                                    <p className="text-xs text-slate-400 mt-2 font-medium">
                                                        Bayar: <span className="font-bold text-white uppercase">{matchedOrder.payment_method || 'Cash'}</span>
                                                    </p>
                                                </div>
                                            </div>

                                            {/* Rincian Menu */}
                                            <div className="p-6">
                                                <h4 className="font-black text-slate-800 text-base mb-4 flex items-center justify-between border-b pb-2">
                                                    <span>🍽️ Rincian Menu Pesanan:</span>
                                                    <span className="text-xs text-slate-500 font-bold">
                                                        {(matchedOrder.items || []).reduce((acc, it) => acc + (it.quantity || 1), 0)} Porsi Total
                                                    </span>
                                                </h4>

                                                <div className="space-y-3 mb-6">
                                                    {matchedOrder.items && matchedOrder.items.length > 0 ? (
                                                        matchedOrder.items.map((item, idx) => {
                                                            const itemMenu = item.menu || {};
                                                            return (
                                                                <div key={idx} className="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                                                                    <div className="flex items-center space-x-3">
                                                                        <div className="w-12 h-12 bg-white rounded-lg flex items-center justify-center text-2xl border border-slate-200">
                                                                            🍽️
                                                                        </div>
                                                                        <div>
                                                                            <h5 className="font-bold text-slate-800 text-sm">{itemMenu.name || 'Menu Makanan'}</h5>
                                                                            <span className="text-xs text-slate-500">Rp {Number(item.price).toLocaleString('id-ID')} / porsi</span>
                                                                        </div>
                                                                    </div>
                                                                    <div className="text-right">
                                                                        <span className="inline-block bg-slate-900 text-white font-black text-xs px-2 py-0.5 rounded mb-1">
                                                                            x {item.quantity}
                                                                        </span>
                                                                        <p className="font-black text-emerald-600 text-sm">
                                                                            Rp {(Number(item.price) * Number(item.quantity)).toLocaleString('id-ID')}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            );
                                                        })
                                                    ) : (
                                                        <p className="text-slate-400 italic text-sm text-center py-4">Data item tidak tersedia.</p>
                                                    )}
                                                </div>

                                                <div className="pt-4 border-t-2 border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                                                    <div>
                                                        <span className="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Pembayaran</span>
                                                        <span className="text-2xl font-black text-slate-900">
                                                            Rp {Number(matchedOrder.total_amount).toLocaleString('id-ID')}
                                                        </span>
                                                    </div>

                                                    <div className="flex items-center gap-2 w-full sm:w-auto">
                                                        {matchedOrder.status !== 'ready' && matchedOrder.status !== 'completed' && (
                                                            <button 
                                                                type="button" 
                                                                onClick={() => handleAdvanceOrder(matchedOrder)}
                                                                className="px-4 py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs"
                                                            >
                                                                🍳 Ubah: Sedang Dimasak
                                                            </button>
                                                        )}
                                                        {matchedOrder.status !== 'completed' ? (
                                                            <button 
                                                                type="button" 
                                                                onClick={() => handleCompleteOrder(matchedOrder)}
                                                                className="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white font-black px-6 py-3 rounded-xl shadow-lg transition-all flex items-center justify-center gap-2 text-sm"
                                                            >
                                                                <span>✓</span> Serahkan Pesanan & Selesaikan
                                                            </button>
                                                        ) : (
                                                            <div className="bg-green-50 border border-green-200 text-green-700 px-5 py-3 rounded-xl font-bold text-sm">
                                                                ✓ Pesanan Telah Diserahkan
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        </Card>
                                    ) : hasTyped ? (
                                        <Card className="border-2 border-dashed border-red-200 bg-red-50/50 text-center py-8 px-4">
                                            <div className="text-3xl mb-2">🔍</div>
                                            <h4 className="font-bold text-red-700 text-lg mb-1">Tiket Tidak Ditemukan</h4>
                                            <p className="text-slate-500 text-sm">
                                                Tidak ditemukan pesanan dengan nomor tiket <span className="font-bold text-dark font-mono">"{scanInput}"</span> di stand ini.
                                            </p>
                                        </Card>
                                    ) : null}
                                </div>
                            );
                        })()}

                        {/* ============================================================== */}
                        {/* TAB 6: GPS MONITORING                                          */}
                        {/* ============================================================== */}
                        {tab === 'gps' && (
                            <div key="tab-gps" className="animate-page-enter space-y-6">
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <Card className="border-emerald-500 shadow-flat-primary">
                                        <div className="flex items-center justify-between mb-2">
                                            <span className="text-slate-500 font-bold text-xs uppercase tracking-wider">Titik Pusat Sekolah</span>
                                            <span className="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">📍</span>
                                        </div>
                                        <h4 className="text-xl font-extrabold text-slate-800">SMKN 1 Ciomas</h4>
                                        <p className="text-xs text-slate-500 font-mono mt-1">Lat: -6.586000 • Lng: 106.758500</p>
                                        <p className="text-[11px] text-emerald-700 font-bold mt-1">Jl. Raya Laladon, Ciomas, Bogor</p>
                                    </Card>

                                    <Card className="border-blue-500 shadow-flat-secondary">
                                        <div className="flex items-center justify-between mb-2">
                                            <span className="text-slate-500 font-bold text-xs uppercase tracking-wider">Radius Geofence</span>
                                            <span className="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm">🛰️</span>
                                        </div>
                                        <h4 className="text-xl font-extrabold text-slate-800">350 Meter</h4>
                                        <p className="text-xs text-slate-500 mt-1">Mencakup area gerbang, kelas, kantin & lapangan</p>
                                        <p className="text-[11px] text-blue-700 font-bold mt-1">Formula Haversine Terverifikasi</p>
                                    </Card>

                                    <Card>
                                        <div className="flex items-center justify-between mb-2">
                                            <span className="text-slate-500 font-bold text-xs uppercase tracking-wider">Riwayat Verifikasi</span>
                                            <span className="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-sm">📱</span>
                                        </div>
                                        <h4 className="text-xl font-extrabold text-slate-800">{gpsHistory.length} Deteksi</h4>
                                        <p className="text-xs text-slate-500 mt-1">Tercatat dari sensor GPS HP siswa</p>
                                        <button 
                                            type="button" 
                                            onClick={loadGpsHistory} 
                                            className="text-[11px] font-bold text-emerald-700 hover:underline mt-1 flex items-center gap-1"
                                        >
                                            <span>🔄</span> Segarkan Log Deteksi
                                        </button>
                                    </Card>
                                </div>

                                <Card>
                                    <div className="flex justify-between items-center border-b-2 border-slate-100 pb-4 mb-4 flex-wrap gap-2">
                                        <div>
                                            <h2 className="text-xl font-extrabold text-dark">Log Deteksi Keberadaan Siswa</h2>
                                            <p className="text-xs text-slate-500">Daftar siswa yang memverifikasi lokasi di SMKN 1 Ciomas</p>
                                        </div>
                                        <button 
                                            type="button" 
                                            onClick={loadGpsHistory} 
                                            className="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5"
                                        >
                                            <span>🔄</span> {isGpsLoading ? 'Memuat...' : 'Segarkan Data'}
                                        </button>
                                    </div>

                                    {gpsHistory.length === 0 ? (
                                        <div className="text-center py-12 text-slate-400">
                                            <div className="text-4xl mb-2">📡</div>
                                            <p className="font-bold text-sm">Belum ada data deteksi GPS siswa</p>
                                        </div>
                                    ) : (
                                        <div className="overflow-x-auto">
                                            <table className="w-full text-left min-w-[700px]">
                                                <thead>
                                                    <tr className="text-slate-500 text-xs uppercase tracking-wider border-b-2 border-slate-100">
                                                        <th className="py-3 font-bold">Waktu</th>
                                                        <th className="py-3 font-bold">Nama Siswa</th>
                                                        <th className="py-3 font-bold">Status Lokasi</th>
                                                        <th className="py-3 font-bold">Jarak ke SMKN 1</th>
                                                        <th className="py-3 font-bold">Perangkat / Device</th>
                                                        <th className="py-3 font-bold">Koordinat Siswa</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {gpsHistory.map((item, idx) => {
                                                        const isInside = item.is_inside || item.is_inside_school;
                                                        const dist = item.distance_meters !== null ? Math.round(item.distance_meters) : '-';
                                                        return (
                                                            <tr key={idx} className="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                                                <td className="py-3.5 text-xs font-bold text-slate-500">
                                                                    {item.verified_at || item.time || '-'}
                                                                </td>
                                                                <td className="py-3.5 font-bold text-dark text-sm">
                                                                    {item.student_name || 'Siswa'}
                                                                </td>
                                                                <td className="py-3.5">
                                                                    <span className={`inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold ${
                                                                        isInside ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'
                                                                    }`}>
                                                                        <span>{isInside ? '✓' : '⚠️'}</span>
                                                                        <span>{isInside ? 'Di Dalam Sekolah' : 'Di Luar Area'}</span>
                                                                    </span>
                                                                </td>
                                                                <td className="py-3.5 font-extrabold text-sm text-slate-800">
                                                                    {dist !== '-' ? `${dist} meter` : '-'}
                                                                </td>
                                                                <td className="py-3.5 text-xs text-slate-600 font-medium max-w-[200px] truncate" title={item.device_info}>
                                                                    {item.device_info || 'HP Siswa'}
                                                                </td>
                                                                <td className="py-3.5 text-xs font-mono text-slate-500">
                                                                    {item.latitude ? `${Number(item.latitude).toFixed(5)}, ${Number(item.longitude).toFixed(5)}` : '-'}
                                                                </td>
                                                            </tr>
                                                        );
                                                    })}
                                                </tbody>
                                            </table>
                                        </div>
                                    )}
                                </Card>
                            </div>
                        )}
                    </main>
                </div>
            );
        }

        // --- SPLASH / INTRO ANIMATION COMPONENT (Tampilan Pembuka Adaptif Tiap Portal) --- //
        function IntroSplash({ onFinish, currentPath = '/' }) {
            const isStudent = currentPath.includes('student');
            const isAdmin = currentPath.includes('admin');
            const isLogin = currentPath.includes('login');

            let defaultBadge = '✨ KANTIN PINTAR DIGITAL';
            let defaultTitle = 'EcoBites Canteen';
            let defaultSubtitle = 'Jajan Sehat, Cepat & Tanpa Antre di Sekolah';
            let icons = ['🍽️', '🍱', '🍔', '🍜', '🧃', '⚡'];

            if (isStudent) {
                defaultBadge = '🎒 PORTAL SISWA DIGITAL';
                defaultTitle = 'Kantin Pintar Siswa';
                defaultSubtitle = 'Siapkan selera istirahatmu — Pesan cepat tanpa antre!';
                icons = ['🎒', '🍱', '🥪', '🍜', '🧃', '✨'];
            } else if (isAdmin) {
                defaultBadge = '🔐 PANEL PENGELOLA KANTIN';
                defaultTitle = 'Dashboard Admin & Kasir';
                defaultSubtitle = 'Pantau omzet harian, stok menu & antrean loket';
                icons = ['🔐', '📊', '🎫', '🍳', '💵', '⚡'];
            } else if (isLogin) {
                defaultBadge = '🔑 AKSES MASUK KANTIN';
                defaultTitle = 'Portal Masuk E-Kantin';
                defaultSubtitle = 'Sistem otentikasi aman untuk siswa dan staf';
                icons = ['🔑', '🎒', '🔐', '✨'];
            }

            const [progress, setProgress] = useState(15);
            const [status, setStatus] = useState(
                isStudent ? 'Memeriksa status akun siswa...' :
                isAdmin ? 'Memvalidasi sesi pengelola kantin...' :
                'Menghubungkan ke server kantin...'
            );
            const [iconIndex, setIconIndex] = useState(0);
            const [isFading, setIsFading] = useState(false);

            useEffect(() => {
                const interval = setInterval(() => {
                    setIconIndex(prev => (prev + 1) % icons.length);
                }, 240);
                return () => clearInterval(interval);
            }, [icons.length]);

            useEffect(() => {
                const t1 = setTimeout(() => {
                    setProgress(48);
                    setStatus(
                        isStudent ? 'Mengambil katalog menu & rekomendasi gizi...' :
                        isAdmin ? 'Sinkronisasi omzet harian & stok pangan...' :
                        'Memuat menu bergizi & status loket...'
                    );
                }, 280);

                const t2 = setTimeout(() => {
                    setProgress(82);
                    setStatus(
                        isStudent ? 'Menyiapkan keranjang istirahat & tiket...' :
                        isAdmin ? 'Membuka loket verifikasi antrean siswa...' :
                        'Menyiapkan sistem antrean digital...'
                    );
                }, 620);

                const t3 = setTimeout(() => {
                    setProgress(100);
                    setStatus(
                        isStudent ? 'Portal Siswa Siap! Selamat jajan... 🚀' :
                        isAdmin ? 'Dashboard Admin Siap! ⚡' :
                        'Sistem Siap! Selamat menikmati... 🚀'
                    );
                }, 950);

                const t4 = setTimeout(() => {
                    setIsFading(true);
                }, 1250);

                const t5 = setTimeout(() => {
                    onFinish();
                }, 1550);

                return () => {
                    clearTimeout(t1);
                    clearTimeout(t2);
                    clearTimeout(t3);
                    clearTimeout(t4);
                    clearTimeout(t5);
                };
            }, [onFinish, isStudent, isAdmin]);

            const orbDelay = { animationDelay: '1s' };
            const progressStyle = { width: progress + '%' };

            return (
                <div className={`fixed inset-0 z-[99999] bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950 flex flex-col items-center justify-center p-6 text-white select-none overflow-hidden transition-all duration-400 ${isFading ? 'opacity-0 scale-105 pointer-events-none' : 'opacity-100 scale-100'}`}>
                    <div className="absolute top-1/4 left-1/4 w-80 h-80 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none animate-pulse-glow"></div>
                    <div className="absolute bottom-1/4 right-1/4 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl pointer-events-none animate-pulse-glow" style={orbDelay}></div>

                    <div className="relative z-10 flex flex-col items-center text-center max-w-md w-full">
                        <div className="relative mb-6">
                            <div className="w-28 h-28 rounded-full border-2 border-dashed border-emerald-400/30 animate-spin-slow absolute -inset-2"></div>
                            
                            <div className="w-24 h-24 rounded-3xl bg-gradient-to-tr from-emerald-600 via-green-500 to-emerald-400 p-0.5 shadow-2xl shadow-emerald-500/40 flex items-center justify-center relative animate-float">
                                <div className="w-full h-full bg-slate-900/90 rounded-[1.4rem] flex items-center justify-center text-4xl">
                                    <span className="transition-all transform duration-200">{icons[iconIndex]}</span>
                                </div>
                                <span className="absolute -top-1 -right-1 flex h-4 w-4">
                                    <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span className="relative inline-flex rounded-full h-4 w-4 bg-emerald-500"></span>
                                </span>
                            </div>
                        </div>

                        <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs font-extrabold tracking-wider uppercase mb-2">
                            <span>✨</span> {defaultBadge}
                        </div>
                        <h1 className="text-3xl sm:text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-white via-slate-100 to-emerald-300 tracking-tight">
                            {defaultTitle}
                        </h1>
                        <p className="text-slate-400 text-sm mt-1 font-medium">
                            {defaultSubtitle}
                        </p>

                        <div className="w-full max-w-xs mt-8">
                            <div className="flex justify-between items-center text-xs font-bold mb-2">
                                <span className="text-slate-400 truncate max-w-[200px]">{status}</span>
                                <span className="text-emerald-400 font-extrabold">{progress}%</span>
                            </div>
                            <div className="w-full h-2.5 bg-slate-800/80 rounded-full overflow-hidden p-0.5 border border-slate-700/60 shadow-inner relative">
                                <div 
                                    className="h-full bg-gradient-to-r from-emerald-500 via-green-400 to-emerald-300 rounded-full transition-all duration-300 relative overflow-hidden shadow-lg shadow-emerald-500/50"
                                    style={progressStyle}
                                >
                                    <div className="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/40 to-transparent animate-shimmer"></div>
                                </div>
                            </div>
                        </div>

                        <button 
                            type="button"
                            onClick={() => { setIsFading(true); setTimeout(onFinish, 150); }}
                            className="mt-8 text-xs font-bold text-slate-500 hover:text-slate-300 hover:underline transition-colors px-3 py-1 rounded-lg"
                        >
                            Lewati Animasi →
                        </button>
                    </div>
                </div>
            );
        }

        // --- PAGE TRANSITION OVERLAY (Animasi Transisi Halus Sebelum Masuk Tiap Halaman) --- //
        function PageTransitionOverlay({ targetInfo, isClosing }) {
            const fullStyle = { width: '100%' };
            return (
                <div className={`fixed inset-0 z-[99998] bg-slate-950/80 backdrop-blur-md flex flex-col items-center justify-center p-6 text-white select-none transition-all duration-300 ${isClosing ? 'opacity-0 scale-105 pointer-events-none' : 'opacity-100 scale-100'}`}>
                    <div className="relative z-10 flex flex-col items-center text-center max-w-sm animate-scale-up">
                        <div className="relative mb-5">
                            <div className="w-20 h-20 rounded-full border-2 border-dashed border-emerald-400/40 animate-spin-slow absolute -inset-2"></div>
                            <div className="w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 p-0.5 shadow-xl shadow-emerald-500/30 flex items-center justify-center animate-float">
                                <div className="w-full h-full bg-slate-900 rounded-[0.9rem] flex items-center justify-center text-2xl">
                                    {targetInfo.icon || '🚀'}
                                </div>
                            </div>
                        </div>

                        <div className="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-[11px] font-bold tracking-wider uppercase mb-2">
                            {targetInfo.badge || 'MEMUAT HALAMAN'}
                        </div>
                        <h2 className="text-xl font-black text-white tracking-tight mb-1">
                            {targetInfo.title || 'Menyiapkan Halaman...'}
                        </h2>
                        <p className="text-slate-400 text-xs font-medium">
                            {targetInfo.subtitle || 'Mohon tunggu sejenak...'}
                        </p>

                        <div className="w-40 h-1.5 bg-slate-800/80 rounded-full overflow-hidden mt-5 border border-slate-700/60 p-0.5">
                            <div className="h-full bg-gradient-to-r from-emerald-500 via-green-400 to-teal-300 rounded-full animate-pulse" style={fullStyle}></div>
                        </div>
                    </div>
                </div>
            );
        }

        // --- ROUTER DENGAN ANIMASI TRANSISI LENGKAP SISWA & ADMIN --- //
        function App() {
            const [path, setPath] = useState(window.location.pathname);
            const [isInitialLoading, setIsInitialLoading] = useState(true);
            const [transitionData, setTransitionData] = useState(null);

            const getPageMeta = (targetPath) => {
                if (targetPath.startsWith('/gps-verify') || targetPath.startsWith('/lokasi-siswa')) {
                    return {
                        badge: '🛰️ VERIFIKASI GPS HP',
                        title: 'Membuka Sensor GPS HP...',
                        subtitle: 'Deteksi keberadaan siswa di SMKN 1 Ciomas...',
                        icon: '📍'
                    };
                }
                if (targetPath === '/student/dashboard' || targetPath === '/login/student' || targetPath === '/register') {
                    return {
                        badge: '🍱 PORTAL JAJAN',
                        title: 'Membuka Kantin Sekolah...',
                        subtitle: 'Menyiapkan katalog jajanan segar & tiket antrean...',
                        icon: '🍱'
                    };
                }
                if (targetPath === '/admin/dashboard') {
                    return {
                        badge: '🔐 PORTAL PENGELOLA',
                        title: 'Membuka Dashboard Admin...',
                        subtitle: 'Sinkronisasi loket antrean, omzet & stok...',
                        icon: '📊'
                    };
                }
                if (targetPath === '/login/admin') {
                    return {
                        badge: '🔐 MASUK PENGELOLA',
                        title: 'Menuju Login Admin...',
                        subtitle: 'Akses staf loket, kasir dan administrasi...',
                        icon: '🛡️'
                    };
                }
                return {
                    badge: '✨ BERANDA UTAMA',
                    title: 'Kembali ke Beranda...',
                    subtitle: 'Kantin Pintar Digital EcoBites...',
                    icon: '🏫'
                };
            };

            // Handler navigasi dengan animasi sebelum memasuki tiap halaman
            const navigate = (newPath) => {
                if (newPath === path) return;
                const meta = getPageMeta(newPath);
                setTransitionData({ targetInfo: meta, isClosing: false });
                window.scrollTo({ top: 0, behavior: 'smooth' });

                setTimeout(() => {
                    setPath(newPath);
                    setTransitionData(prev => prev ? Object.assign({}, prev, { isClosing: true }) : null);

                    setTimeout(() => {
                        setTransitionData(null);
                    }, 280);
                }, 400);
            };

            useEffect(() => {
                window.history.pushState({}, '', path);
            }, [path]);

            const renderPage = () => {
                if (path.startsWith('/gps-verify') || path.startsWith('/lokasi-siswa')) {
                    const parts = path.split('/');
                    const token = parts[2] || '';
                    return <GpsDeviceVerification token={token} setPath={navigate} />;
                }
                if (path === '/login/admin') return <LoginAdmin setPath={navigate} />;
                if (path === '/student/dashboard' || path === '/login/student' || path === '/register') return <StudentDashboard setPath={navigate} />;
                if (path === '/admin/dashboard') return <AdminDashboard setPath={navigate} />;
                return <Landing setPath={navigate} />;
            };

            return (
                <div className="relative min-h-screen bg-slate-50">
                    {/* Splash Intro Animation (Muncul pertama kali saat web dibuka / direfresh sesuai halaman) */}
                    {isInitialLoading && (
                        <IntroSplash currentPath={path} onFinish={() => setIsInitialLoading(false)} />
                    )}

                    {/* Page Transition Overlay (Animasi Transisi sebelum masuk ke tiap halaman) */}
                    {transitionData && (
                        <PageTransitionOverlay targetInfo={transitionData.targetInfo} isClosing={transitionData.isClosing} />
                    )}

                    {/* Konten Halaman dengan Efek Animasi Masuk (Page Enter Animation) */}
                    <div 
                        key={path} 
                        className={`transition-all duration-300 ${transitionData ? 'opacity-20 scale-[0.99] filter blur-[1px]' : 'animate-page-enter opacity-100 scale-100'}`}
                    >
                        {renderPage()}
                    </div>
                </div>
            );
        }

        const root = ReactDOM.createRoot(document.getElementById('app'));
        root.render(<App />);
    </script>
</body>
</html>
