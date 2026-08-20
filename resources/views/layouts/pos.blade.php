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
        background: #1e2333;
        padding: 12px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .pos-topbar-logo {
        font-size: 20px;
        font-weight: 800;
        letter-spacing: -0.02em;
    }

    .pos-topbar-logo .l1 { color: #ff4d4d; }
    .pos-topbar-logo .l2 { color: #ffa500; }
    .pos-topbar-logo .l3 { color: #ffffff; margin-left: 8px; }

    .pos-topbar-right {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .pos-topbar-user {
        font-size: 13px;
        color: #94a3b8;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .pos-topbar-user i { color: #6366f1; }

    .pos-back-btn {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 7px 24px;
        background: #dbad2e;
        color: #ffffff;
        border-radius: 5px;
        font-size: 13px;
        text-decoration: none;
        transition: background 0.15s, color 0.15s;
    }

    .pos-back-btn:hover {
        background: #d9a61a;
        color: #fff;
    }

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

        position: relative !important; /* anchor for the absolutely-centered glyph */

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

    /*
       The "+" glyph in most fonts isn't optically centered in its own
       line box (extra space below the baseline), which is why flex
       centering alone still looks off. This absolutely positions the
       inner content and shifts it back by exactly half its own size,
       which centers it regardless of font metrics or whether the
       button contains a text "+" or an <i> icon.
    */
    .modern-plus-btn > * {
        position: absolute !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
        line-height: 1 !important;
    }

    /* If the "+" is a bare text node (no wrapping span/i), wrap it in
       markup like <button class="modern-plus-btn"><span>+</span></button>
       so the rule above has an element to target. */

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
    <h3 style="color: white;">
        <i class="fas fa-cash-register"></i> POS
    </h3>
    <div class="pos-topbar-right">
      <div class="pos-topbar-user">
        <i class="fas fa-user"></i>
        {{ Auth::user()->name }} — {{ Auth::user()->role->role_name ?? 'No Role' }}
      </div>
      <a href="{{ route('dashboard') }}" class="btn btn-back">
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

  {{-- ── AUTO-DOWNLOAD INVOICE PDF AFTER A SUCCESSFUL POS CHECKOUT ──
       PosController::checkout() flashes 'pos_order_ids' (the ids of the
       orders it just created) alongside its existing 'pos_success' flash
       message. On the next page load (the redirect back to pos.index),
       if those ids are present we trigger a download of the combined
       invoice PDF for exactly the orders created by this checkout via
       OrderController::invoiceCombinedPdf(). --}}
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