<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('pageTitle', 'POS')</title>
    <link rel="icon" type="image/x-icon" href="/image/logokr.jpg" />
    <link rel="stylesheet" href="{{ URL::asset('css/main.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    @yield('headerBlock')
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #f3f4f6; font-family: sans-serif; }

        /* ── TOPBAR STYLES ── */
        .pos-topbar {
            background: repeating-linear-gradient(-45deg, #fd6c24, #ff5a05, #fd6c24 3px, #ff5a05 3px);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .pos-topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .pos-topbar-logo {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .pos-topbar-logo .l1 { color: #ff4d4d; }
        .pos-topbar-logo .l2 { color: #ffa500; }
        .pos-topbar-logo .l3 { color: #ffffff; margin-left: 8px; }

        .pos-live-clock {
            font-size: 14px;
            font-weight: 500;
            color: #ffffff;
            background: rgba(0, 0, 0, 0.15);
            padding: 4px 10px;
            border-radius: 4px;
        }

        .pos-topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .pos-topbar-user {
            font-size: 13px;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .pos-topbar-user i { color: #ffffff; }
        /* ── LOADING OVERLAY STYLES ── */
        #loading-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255,255,255,0.85);
            display: none;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            z-index: 99999;
        }
        .spinner {
            border: 6px solid #f3f3f3;
            border-top: 6px solid #3498db;
            border-radius: 50%;
            width: 60px; height: 60px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        #loading-text { margin-top: 15px; font-size: 16px; color: #333; }

        /* ── STOCK ALERT POPUP STYLES ── */
        .stock-alert-trigger-wrapper {
            padding: 8px 24px;
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
        }

        .stock-alert-btn {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            color: #9a3412;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
        }

        .stock-alert-btn:hover {
            background: #ffedd5;
        }

        .stock-badge-count {
            background: #ea580c;
            color: white;
            padding: 1px 6px;
            border-radius: 10px;
            font-size: 11px;
        }

        /* Modal Overlay */
        .stock-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(2px);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        /* Modal Content Box */
        .stock-modal-content {
            background: #ffffff;
            width: 100%;
            max-width: 400px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: modalPopupScale 0.25s ease-in-out;
        }

        .stock-modal-header {
            padding: 14px 18px;
            background: #fff7ed;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #fed7aa;
        }

        .stock-modal-header h3 {
            font-size: 15px;
            color: #9a3412;
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }

        .stock-modal-close {
            background: transparent;
            border: none;
            font-size: 22px;
            font-weight: bold;
            color: #9a3412;
            cursor: pointer;
        }

        .stock-modal-body {
            padding: 16px;
            max-height: 60vh;
            overflow-y: auto;
        }

        .stock-alert-items {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .stock-alert-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #ffedd5;
            border: 1px solid #fed7aa;
            border-radius: 20px;
            padding: 4px 10px;
            font-size: 12px;
            color: #7c2d12;
            font-weight: 500;
        }

        .stock-alert-chip .chip-stock {
            font-weight: 700;
            color: #ea580c;
        }

        /* ── MODERN PLUS BUTTON STYLES (FIXED CENTERED) ── */
        .modern-plus-btn {
            width: 45px !important;
            height: 45px !important;
            min-width: 45px !important;
            min-height: 45px !important;
            max-width: 45px !important;
            max-height: 45px !important;

            position: relative !important;

            background: linear-gradient(135deg, #1f2937, #111827) !important;
            color: #ffffff !important;
            border: none !important;
            border-radius: 50% !important;

            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;

            font-size: 24px !important;
            font-weight: 300 !important;
            line-height: 1 !important;
            cursor: pointer !important;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2) !important;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;

            margin: 0 !important;
            padding: 0 !important;
            flex-shrink: 0 !important;
        }

        .modern-plus-btn > * {
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
            line-height: 1 !important;
        }

        .modern-plus-btn:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 6px 15px rgba(99, 102, 241, 0.35);
            background: linear-gradient(135deg, #374151, #1f2937) !important;
        }

        .modern-plus-btn:active {
            transform: translateY(0) scale(0.95);
        }

        @keyframes modalPopupScale {
            from {
                transform: scale(0.85);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .stock-alert-trigger-wrapper {
                padding: 8px 12px;
                justify-content: center;
            }
            .stock-alert-btn {
                width: 100%;
                justify-content: center;
                padding: 8px 14px;
            }
        }
    </style>
</head>
<body>

<div class="pos-topbar">
    <div class="pos-topbar-left">
        <h3 style="color: white;">
            <i class="fas fa-cash-register"></i> POS
        </h3>
        <!-- Live Time Display placed right after (ក្រោយ) POS -->
        <span id="pos-live-clock" class="pos-live-clock"></span>
    </div>

    <div class="pos-topbar-right">
        <div class="pos-topbar-user">
            <i class="fas fa-circle-user"></i>
            {{ Auth::user()->name }} — {{ Auth::user()->role->role_name ?? 'No Role' }}
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-back pos-back-btn">
            <i class="fas fa-chevron-left"></i> Back
        </a>
    </div>
</div>

{{-- ── STOCK ALERT POPUP BUTTON & MODAL (Shows when low stock products exist) ── --}}
@php
    $lowStockProducts = isset($products)
        ? $products->filter(fn($p) => $p->add_to_pos == 1 && $p->stock > 0 && $p->stock < 10)
        : collect();
@endphp

@if($lowStockProducts->isNotEmpty())
    <div class="stock-alert-trigger-wrapper">
        <button type="button" class="stock-alert-btn" onclick="openStockModal()">
            <i class="fas fa-triangle-exclamation"></i>
            <span>Low Stock Alert</span>
            <span class="stock-badge-count">{{ $lowStockProducts->count() }}</span>
        </button>
    </div>

    <div id="stockAlertModal" class="stock-modal-overlay" style="display: none;">
        <div class="stock-modal-content">
            <div class="stock-modal-header">
                <h3><i class="fas fa-triangle-exclamation" style="color: #ea580c;"></i> Low Stock Alert</h3>
                <button type="button" class="stock-modal-close" onclick="closeStockModal()">&times;</button>
            </div>
            <div class="stock-modal-body">
                <div class="stock-alert-items">
                    @foreach($lowStockProducts as $lp)
                        <span class="stock-alert-chip">
                          <i class="fas fa-box" style="font-size:10px;"></i>
                          {{ $lp->name }}
                          <span class="chip-stock">({{ $lp->stock }} left)</span>
                      </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif

<div id="loading-overlay">
    <div class="spinner"></div>
    <div id="loading-text">Loading...</div>
</div>

<div class="pos-page-wrap">
    @yield('content')
</div>

<script>
    // Live Clock Script
    function updateLiveClock() {
        const now = new Date();
        const options = {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        };
        const formattedTime = now.toLocaleString('en-US', options);
        const clockElement = document.getElementById('pos-live-clock');
        if (clockElement) {
            clockElement.textContent = formattedTime;
        }
    }

    updateLiveClock();
    setInterval(updateLiveClock, 1000);

    // Stock Modal Scripts
    function openStockModal() {
        const modal = document.getElementById('stockAlertModal');
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    function closeStockModal() {
        const modal = document.getElementById('stockAlertModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    window.addEventListener('click', function(event) {
        const modal = document.getElementById('stockAlertModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
</script>

{{-- ── AUTO-DOWNLOAD INVOICE PDF AFTER A SUCCESSFUL POS CHECKOUT ── --}}
@if(session('pos_success') && session('pos_order_ids'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const orderIds = @json(session('pos_order_ids'));
            if (!Array.isArray(orderIds) || orderIds.length === 0) return;

            const qs = orderIds.map(function (id) { return 'ids[]=' + id; }).join('&');
            const pdfUrl = "{{ route('orders.invoiceCombinedPdf') }}?" + qs;

            const link = document.createElement('a');
            link.href = pdfUrl;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    </script>
@endif

</body>
</html>
