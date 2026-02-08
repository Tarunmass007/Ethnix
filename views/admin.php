<?php
declare(strict_types=1);
/**
 * Admin Panel (Center content only)
 * - Tabs: Users • Redeem Codes • System
 * - Users: search by username/name/ID/telegram_id (backend must support), edit (status/credits/xcoin/kcoin)
 * - Redeem: credits-only generator (with status + expiry), list + search + 50/page, export current page (clipboard/.txt)
 *
 * Assumes APIs:
 *   GET  /api/admin/users.php?q=&page=&limit=50
 *   POST /api/admin/user_update.php      (user_id|telegram_id, status?, delta_credits?, delta_xcoin?, adjust_kcoin?)
 *   GET  /api/admin/redeem_codes.php?q=&page=&limit=50
 *   POST /api/admin/generate_code.php    (credits, count, status=FREE|PREMIUM, expiry_date? | expiry_days?)
 *   GET  /api/stats.php                  (optional quick stats)
 */
?>
<section class="space-y-6">
  <!-- Hero / Title -->
  <div class="rounded-2xl border border-white/10 bg-gradient-to-br from-slate-900/70 to-slate-900/40 p-5 shadow-xl">
    <div class="flex items-center justify-between gap-4 flex-wrap">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-violet-500 to-cyan-400 flex items-center justify-center shadow">
          <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M5 20h14l1-9-5 3-3-7-3 7-5-3 1 9zm-3 2h20v2H2z"/>
          </svg>
        </div>
        <div>
          <h1 class="text-xl font-semibold">Admin Panel</h1>
          <p class="text-xs text-slate-400">Manage users, generate credits codes, and tweak system settings.</p>
        </div>
      </div>

    </div>
  </div>

  <!-- GLOBAL STATISTICS -->
  <div class="gs-panel">
    <div class="gs-head">
      <div class="gs-chip">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M5 3h2v18H5V3zm6 6h2v12h-2V9zm6-4h2v16h-2V5z"/></svg>
      </div>
      <div>
        <div class="gs-title">Global Statistics</div>
        <div class="gs-sub">Platform-wide performance metrics</div>
      </div>
    </div>

    <div class="gs-grid">
      <!-- Total Users -->
      <div class="gs-card">
        <div class="gs-icon text-sky-400">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 3-1.57 3-3.5S17.66 4 16 4s-3 1.57-3 3.5S14.34 11 16 11zM8 11c1.66 0 3-1.57 3-3.5S9.66 4 8 4 5 5.57 5 7.5 6.34 11 8 11zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.89 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
        </div>
        <div id="gTotalUsers" class="gs-num">—</div>
        <div class="gs-label">Total Users</div>
      </div>

      <!-- Hits -->
      <div class="gs-card">
        <div class="gs-icon text-emerald-400">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
        </div>
        <div id="gTotalHits" class="gs-num">—</div>
        <div class="gs-label">Total Hits</div>
      </div>

      <!-- Live Cards -->
      <div class="gs-card">
        <div class="gs-icon text-amber-400">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 6h-2.18c.11-.31.18-.65.18-1 0-1.66-1.34-3-3-3-1.05 0-1.96.54-2.5 1.35l-.5.67-.5-.68C10.96 2.54 10.05 2 9 2 7.34 2 6 3.34 6 5c0 .35.07.69.18 1H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-5-2c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM9 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm11 15H4v-2h16v2zm0-5H4V8h5.08L7 10.83 8.62 12 11 8.76l1-1.36 1 1.36L15.38 12 17 10.83 14.92 8H20v6z"/></svg>
        </div>
        <div id="gLiveCards" class="gs-num">—</div>
        <div class="gs-label">Active CVV</div>
      </div>

    <!-- Charge Cards -->
      <div class="gs-card">
        <div class="gs-icon text-violet-400">
           <svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg>
        </div>
        <div id="gChargeCards" class="gs-num">—</div>
        <div class="gs-label">Charge Hits</div>
      </div>
    </div>
  </div>

    <!-- Tabs -->
    <div class="mt-5 flex items-center gap-2 overflow-x-auto no-scrollbar">
      <button data-tab="tabUsers" class="adm-tab active">Users</button>
      <button data-tab="tabRedeem" class="adm-tab">Redeem Codes</button>
      <button data-tab="tabSystem" class="adm-tab">System</button>
    </div>
    
  <!-- USERS TAB -->
  <div id="tabUsers" class="adm-tabpanel block">

    <!-- Toolbar -->
    <div class="flex flex-wrap items-center gap-2">
      <div class="flex-1 min-w-[260px]">
        <label class="sr-only" for="uSearch">Search users</label>
        <div class="relative">
          <input id="uSearch" type="text" placeholder="Search by username / name / ID / telegram_id"
                 class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-violet-500/40">
          <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M21 20l-5.8-5.8a7 7 0 10-1.4 1.4L20 21l1-1zM4 10a6 6 0 1112 0A6 6 0 014 10z"/></svg>
          </div>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button id="uRefresh" class="btn-soft">Refresh</button>
      </div>
    </div>

    <!-- Table -->
    <div class="mt-3 rounded-2xl border border-white/10 bg-white/5 overflow-hidden">
      <div class="overflow-x-auto no-scrollbar">
        <table class="min-w-full text-sm">
          <thead class="bg-white/5 text-slate-300">
            <tr>
              <th class="th">User</th>
              <th class="th">Status</th>
              <th class="th">XCoin</th>
              <th class="th">Killer Credits (kcoin)</th>
              <th class="th">Credits</th>
              <th class="th">Hits</th>
              <th class="th">Plan</th>
              <th class="th">Expiry</th>
              <th class="th text-right">Actions</th>
            </tr>
          </thead>
          <tbody id="uBody" class="divide-y divide-white/5"></tbody>
        </table>
      </div>

      <!-- Pager -->
      <div class="flex items-center justify-between gap-3 px-3 py-2 bg-white/5 border-t border-white/10">
        <div class="text-xs text-slate-400">Max 50 per page</div>
        <div class="flex items-center gap-2">
          <button id="uPrev" class="btn-soft">Prev</button>
          <div class="text-xs text-slate-300"><span id="uPage">1</span> / <span id="uPages">1</span></div>
          <button id="uNext" class="btn-soft">Next</button>
        </div>
      </div>
    </div>
  </div>

  <!-- REDEEM TAB -->
  <div id="tabRedeem" class="adm-tabpanel hidden">
    <div class="grid grid-cols-1 xl:grid-cols-[360px_1fr] gap-4">
      <!-- Credits-only Generator (with status + expiry) -->
      <div class="rounded-2xl border border-white/10 bg-white/5 p-4 h-max">
        <div class="flex items-center gap-2 mb-2">
          <div class="chip">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L1 21h22L12 2zM12 6l7.53 13H4.47L12 6z"/></svg>
          </div>
          <div class="font-medium">Generate Credit Codes</div>
        </div>

        <div class="space-y-3 text-sm">
          <label class="block">
            <span class="text-slate-400 text-xs">Credit Amount</span>
            <input id="gCredits" type="number" min="0" value="100" class="inp" placeholder="e.g. 100">
          </label>

          <label class="block">
            <span class="text-slate-400 text-xs">Status</span>
            <select id="gStatus" class="inp">
              <option value="FREE">FREE</option>
              <option value="PREMIUM">PREMIUM</option>
            </select>
          </label>

          <div class="grid grid-cols-2 gap-2">
            <label class="block">
              <span class="text-slate-400 text-xs">Expiry date (optional)</span>
              <input id="gExpiryDate" type="date" class="inp">
            </label>
            <label class="block">
              <span class="text-slate-400 text-xs">Expiry in days (alt)</span>
              <input id="gExpiryDays" type="number" min="0" value="0" class="inp">
            </label>
          </div>

          <label class="block">
            <span class="text-slate-400 text-xs">How many codes? (max 50)</span>
            <input id="gCount" type="number" min="1" max="50" value="1" class="inp">
          </label>

          <button id="gCreate" class="btn-primary w-full mt-2">Generate</button>
          <div id="gMsg" class="text-xs text-slate-400"></div>
        </div>
      </div>

      <!-- Code list -->
      <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
        <div class="flex flex-wrap items-center gap-2">
          <div class="flex-1 min-w-[220px]">
            <input id="rSearch" type="text" placeholder="Search code / user / telegram_id" class="inp">
          </div>
          <button id="rExportCopy" class="btn-soft">Export (Copy)</button>
          <button id="rExportTxt" class="btn-soft">Export (.txt)</button>
          <button id="rRefresh" class="btn-soft">Refresh</button>
        </div>

        <div class="mt-3 rounded-xl border border-white/10 overflow-hidden">
          <div class="overflow-x-auto no-scrollbar">
            <table class="min-w-full text-sm">
              <thead class="bg-white/5 text-slate-300">
                <tr>
                  <th class="th">Code</th>
                  <th class="th">Credits</th>
                  <th class="th">Expiry</th>
                  <th class="th">Status</th>
                  <th class="th">Redeemed By</th>
                  <th class="th text-right">Actions</th>
                </tr>
              </thead>
              <tbody id="rBody" class="divide-y divide-white/5"></tbody>
            </table>
          </div>
          <div class="flex items-center justify-between gap-3 px-3 py-2 bg-white/5 border-t border-white/10">
            <div class="text-xs text-slate-400">Max 50 per page</div>
            <div class="flex items-center gap-2">
              <button id="rPrev" class="btn-soft">Prev</button>
              <div class="text-xs text-slate-300"><span id="rPage">1</span> / <span id="rPages">1</span></div>
              <button id="rNext" class="btn-soft">Next</button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- SYSTEM TAB -->
  <div id="tabSystem" class="adm-tabpanel hidden">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      
      <!-- Admin Login Credentials (Moved to Top) -->
      <div class="lg:col-span-2 rounded-2xl border border-white/10 bg-white/5 p-5 mb-6">
        <div class="flex items-center gap-2 mb-4 text-slate-200 font-semibold border-b border-white/10 pb-2">
           <svg class="w-5 h-5 text-rose-400" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
           Admin Login Credentials
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <label class="block">
            <span class="text-xs text-slate-400 font-bold uppercase">New Username</span>
            <input id="adm_username" class="inp" placeholder="Leave empty to keep current">
          </label>
           <label class="block">
            <span class="text-xs text-slate-400 font-bold uppercase">New Password</span>
            <input id="adm_password" class="inp" type="password" placeholder="Min 6 chars (Leave empty to keep current)">
          </label>
        </div>
        <div class="mt-3 text-right">
             <button id="admSaveCreds" class="btn-soft text-rose-300 border-rose-500/30 hover:bg-rose-500/10">Update Credentials</button>
        </div>
      </div>

      <!-- Telegram Settings -->
      <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
        <div class="flex items-center gap-2 mb-4 text-slate-200 font-semibold border-b border-white/10 pb-2">
            <svg class="w-5 h-5 text-sky-400" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.48-.94-2.4-1.54-1.06-.7-.37-1.09.23-1.68.14-.13 2.52-2.31 2.57-2.5.01-.03.01-.15-.06-.21-.07-.06-.17-.04-.25-.02-.11.02-1.91 1.2-5.39 3.56-.51.34-.96.51-1.37.5-.45-.01-1.32-.26-1.96-.46-.79-.25-1.41-.38-1.35-.8.03-.22.33-.44.89-.67 3.51-1.53 5.86-2.54 7.03-3.04 3.35-1.43 4.04-1.61 4.5-1.61.1 0 .32.02.47.14.13.1.17.24.18.34 0 .07.01.27 0 .38z"/></svg>
           Telegram Configuration
        </div>
        <div class="space-y-3">
          <label class="block">
            <span class="text-xs text-slate-400 font-bold uppercase">Bot Username (no @)</span>
            <input id="sys_TELEGRAM_BOT_USERNAME" class="inp" placeholder="BabaCheckerRobott">
          </label>
           <label class="block">
            <span class="text-xs text-slate-400 font-bold uppercase">Bot Token</span>
            <input id="sys_TELEGRAM_BOT_TOKEN" class="inp font-mono text-xs" type="password" placeholder="...:...">
          </label>
          <label class="block">
            <span class="text-xs text-slate-400 font-bold uppercase">Admin Username (no @)</span>
            <input id="sys_TELEGRAM_ADMIN_USERNAME" class="inp" placeholder="admin">
          </label>
           <label class="block">
            <span class="text-xs text-slate-400 font-bold uppercase">Announce Channel ID</span>
            <input id="sys_TELEGRAM_ANNOUNCE_CHAT_ID" class="inp" placeholder="-100...">
          </label>
           <label class="block">
            <span class="text-xs text-slate-400 font-bold uppercase">Allowed Admin IDs (CSV)</span>
            <input id="sys_TELEGRAM_ALLOWED_IDS" class="inp font-mono" placeholder="123,456">
          </label>
        </div>
      </div>

      <!-- Payment Settings -->
      <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
        <div class="flex items-center gap-2 mb-4 text-slate-200 font-semibold border-b border-white/10 pb-2">
           <svg class="w-5 h-5 text-emerald-400" viewBox="0 0 24 24" fill="currentColor"><path d="M11 15h2v2h-2zm0-8h2v6h-2zM1 21h22L12 2 1 21z"/></svg>
           Payment Configuration <span class="text-xs font-normal text-slate-400 ml-auto opacity-75">(Appears on Deposit)</span>
        </div>
         <div class="space-y-3 h-[400px] overflow-y-auto no-scrollbar pr-2">
            <label class="block">
              <span class="text-xs text-slate-400 font-bold uppercase">UPI ID</span>
              <input id="sys_PAYMENT_UPI_ID" class="inp" placeholder="e.g. user@okhdfcbank">
            </label>
            <label class="block">
              <span class="text-xs text-slate-400 font-bold uppercase">QR Code Link (Image URL)</span>
              <input id="sys_PAYMENT_QR_LINK" class="inp" placeholder="https://...">
            </label>
            <div class="w-full border-t border-white/10 my-2"></div>
            <label class="block">
              <span class="text-xs text-slate-400 font-bold uppercase">Binance ID</span>
              <input id="sys_PAYMENT_BINANCE_ID" class="inp" placeholder="12345678">
            </label>
            <label class="block">
              <span class="text-xs text-slate-400 font-bold uppercase">USDT (TRC20)</span>
              <input id="sys_PAYMENT_USDT_TRC20" class="inp font-mono text-xs" placeholder="T...">
            </label>
             <label class="block">
              <span class="text-xs text-slate-400 font-bold uppercase">USDT (BEP20)</span>
              <input id="sys_PAYMENT_USDT_BEP20" class="inp font-mono text-xs" placeholder="0x...">
            </label>
             <label class="block">
              <span class="text-xs text-slate-400 font-bold uppercase">BTC Address</span>
              <input id="sys_PAYMENT_BTC_ADDR" class="inp font-mono text-xs">
            </label>
             <label class="block">
              <span class="text-xs text-slate-400 font-bold uppercase">LTC Address</span>
              <input id="sys_PAYMENT_LTC_ADDR" class="inp font-mono text-xs">
            </label>
             <label class="block">
              <span class="text-xs text-slate-400 font-bold uppercase">TRX Address</span>
              <input id="sys_PAYMENT_TRX_ADDR" class="inp font-mono text-xs">
            </label>
         </div>
      </div>
      

      
      <div class="lg:col-span-2">
        <button id="sysSave" class="btn-primary w-full py-3 text-lg shadow-lg shadow-violet-500/20">Save System Settings</button>
      </div>

    </div>
  </div>
</section>

<style>
  .no-scrollbar{-ms-overflow-style:none;scrollbar-width:none}
  .no-scrollbar::-webkit-scrollbar{display:none}
  .adm-tab{
    --ring: rgba(139,92,246,.35);
    padding:.6rem .9rem;border-radius:.9rem;background:rgba(255,255,255,.06);
    border:1px solid rgba(255,255,255,.08); color:#cbd5e1; font-weight:600; font-size:.9rem
  }
  .adm-tab.active{ background:linear-gradient(135deg,#8b5cf6,#06b6d4); border-color:transparent; color:white; box-shadow:0 8px 20px rgba(0,0,0,.25) }
  .adm-tabpanel{ animation: admfade .18s ease }
  @keyframes admfade{from{opacity:.6; transform:translateY(4px)} to{opacity:1; transform:none}}
  .btn-soft{padding:.5rem .8rem;border-radius:.75rem;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1)}
  .btn-soft:hover{background:rgba(255,255,255,.12)}
  .btn-primary{padding:.6rem .9rem;border-radius:.9rem;background:linear-gradient(135deg,#8b5cf6,#06b6d4); color:white; font-weight:600}
  .th{ text-align:left; font-weight:600; padding:.7rem .75rem; white-space:nowrap }
  .td{ padding:.65rem .75rem; vertical-align:middle }
  .chip{width:30px;height:30px;border-radius:.7rem;display:flex;align-items:center;justify-content:center;background:rgba(139,92,246,.18);border:1px solid rgba(139,92,246,.35)}
  .inp{ width:100%; border-radius:.8rem; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.12); padding:.55rem .8rem; outline:none }
  .inp:focus{ box-shadow:0 0 0 3px rgba(139,92,246,.25) }
  .badge{font-size:.68rem; padding:.15rem .4rem; border-radius:.45rem; font-weight:700}
  .stat-tile{border-radius:1rem; padding:.9rem; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.1)}
  .stat-tile .label{font-size:.72rem; color:#94a3b8}
  .stat-tile .value{font-size:1.1rem; font-weight:800}
  .act{ display:inline-flex; align-items:center; gap:.45rem; padding:.45rem .6rem; border-radius:.6rem; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.1) }
  .act:hover{ background:rgba(255,255,255,.12) }
</style>

<!-- Edit User Modal (no Set Plan) -->
<div id="uEditModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/60"></div>
  <div class="absolute inset-x-4 md:inset-x-20 top-10 bottom-10 rounded-2xl bg-slate-900 border border-white/10 flex flex-col max-w-3xl mx-auto">
    <div class="flex items-center justify-between p-4 border-b border-white/10">
      <div class="font-semibold">Edit User <span id="uEditTitle" class="text-slate-400"></span></div>
      <button data-close="uEditModal" class="act" aria-label="Close">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18L18 6M6 6l12 12"/></svg>
        Close
      </button>
    </div>
    <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 overflow-y-auto no-scrollbar">
      <div class="rounded-xl bg-white/5 p-3 space-y-2">
        <div id="uEditIdTg" class="text-xs text-slate-400"></div>
        <label class="block">
          <span class="text-xs text-slate-400">Status</span>
          <select id="eStatus" class="inp">
            <option value="free">FREE</option>
            <option value="premium">PREMIUM</option>
            <option value="banned">BANNED</option>
            <option value="admin">ADMIN</option>
          </select>
        </label>
        <label class="block">
          <span class="text-xs text-slate-400">Adjust Credits (±)</span>
          <input id="eCredit" type="number" class="inp" value="0">
        </label>
        <label class="block">
          <span class="text-xs text-slate-400">Adjust XCoin (±)</span>
          <input id="eXcoin" type="number" class="inp" value="0">
        </label>
        <label class="block">
          <span class="text-xs text-slate-400">Adjust Killer Credits (kcoin) (±)</span>
          <input id="eKcoin" type="number" class="inp" value="0">
        </label>
        <div class="text-xs text-slate-500">Positive = add, Negative = subtract</div>
      </div>
      <div class="rounded-xl bg-white/5 p-3 space-y-2">
        <div class="text-sm font-medium">Notes</div>
        <ul class="list-disc pl-5 text-xs text-slate-400 space-y-1">
          <li>Plan quick-set is removed as requested.</li>
          <li>If both XCoin & kcoin are changed together, the app will save using two quick requests.</li>
        </ul>
      </div>
    </div>
    <div class="p-4 border-t border-white/10 flex items-center justify-end gap-2">
      <button data-close="uEditModal" class="btn-soft">Cancel</button>
      <button id="eSave" class="btn-primary">Save</button>
    </div>
  </div>
</div>

<script>
(function(){
  const $ = s => document.querySelector(s);
  const $$ = s => Array.from(document.querySelectorAll(s));
  const esc = s => (s??'').toString().replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  const nf  = n => new Intl.NumberFormat().format(n|0);
  const d   = document;

  /* ---------- one shared debounce helper ---------- */
  const debounce = (fn, ms) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };

  // ------------ Tabs ------------
  $$('.adm-tab').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      $$('.adm-tab').forEach(b=>b.classList.remove('active'));
      btn.classList.add('active');
      const id = btn.dataset.tab;
      $$('.adm-tabpanel').forEach(p=>p.classList.add('hidden'));
      $('#'+id)?.classList.remove('hidden');
      $('#'+id)?.classList.add('block');
    });
  });

  // ------------ Stats (Re-using Global Stats from dashboard) ------------
  async function fillStats(){
    try{
      const r = await fetch('/api/stats.php?ts='+Date.now(),{credentials:'same-origin', cache:'no-store'});
      const j = await r.json(); if(!j.ok) return;
      const map = { gTotalUsers:'total_users', gLiveCards:'live_cards', gChargeCards:'charge_cards', gTotalHits:'total_hits' };
      Object.entries(map).forEach(([id,key])=>{ const el=document.getElementById(id); if (el) el.textContent = nf(j.data[key]); });
    }catch(e){}
  }
  fillStats(); setInterval(fillStats, 10000);

  // ============== USERS ==============
  let uQ = '', uPage = 1, uPages = 1;
  const uLimit = 50;
  const uSearch = $('#uSearch');

  function badge(status){
    status=(status||'free').toLowerCase();
    const m={
      admin:['bg-rose-500/15','text-rose-300','ADMIN'],
      premium:['bg-amber-500/15','text-amber-300','PREMIUM'],
      banned:['bg-rose-500/15','text-rose-300','BANNED'],
      free:['bg-slate-500/20','text-slate-300','FREE']
    }[status]||['bg-slate-500/20','text-slate-300','FREE'];
    return `<span class="badge ${m[0]} ${m[1]}">${m[2]}</span>`;
  }
  function userRow(u){
    const img = u.avatar || ('https://api.dicebear.com/7.x/shapes/svg?seed='+encodeURIComponent(u.username||('user'+u.id)));
    const name = esc(u.name||u.username||('user'+u.id));
    const uname = esc(u.username||('user'+u.id));
    const plan = esc(u.plan||'—');
    const expiry = u.expiry ? esc(u.expiry) : '—';
    const tg = esc(u.telegram_id || '—');
    const xcoin = nf(u.xcoin||0);
    const kcoin = (u.kcoin!=null) ? nf(u.kcoin) : '—';
    return `<tr>
      <td class="td">
        <div class="flex items-center gap-3 min-w-[260px]">
          <img src="${img}" class="w-9 h-9 rounded-lg object-cover" alt="">
          <div>
            <div class="font-medium">${name}</div>
            <div class="text-xs text-slate-400">@${uname} • ID ${u.id}${tg!=='—' ? ' • TG '+tg : ''}</div>
          </div>
        </div>
      </td>
      <td class="td">${badge(u.status)}</td>
      <td class="td">${xcoin}</td>
      <td class="td">${kcoin}</td>
      <td class="td">${nf(u.credits||0)}</td>
      <td class="td">${nf(u.hits||0)}</td>
      <td class="td">${plan}</td>
      <td class="td">${expiry}</td>
      <td class="td">
        <div class="flex justify-end gap-1">
          <button class="act" data-act="edit" data-id="${u.id}" data-name="${name}" data-tg="${tg}">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.8 9.95l-3.75-3.75L3 17.25zM20.7 7.05c.39-.39.39-1.02 0-1.41L18.36 3.3a.9959.9959 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
            Edit
          </button>
        </div>
      </td>
    </tr>`;
  }

  async function fetchUsers(goPage=uPage){
    try{
      const r = await fetch(`/api/admin/users.php?q=${encodeURIComponent(uQ)}&page=${goPage}&limit=${uLimit}`, {credentials:'same-origin'});
      const j = await r.json(); if(!j.ok) return;
      uPage = j.page||1; uPages = j.pages||1;
      $('#uPage').textContent = uPage;
      $('#uPages').textContent = uPages;
      $('#uBody').innerHTML = (j.users||[]).map(userRow).join('') || `<tr><td class="td" colspan="9"><div class="text-slate-400">No users found.</div></td></tr>`;
    }catch(e){}
  }
  const doSearch = debounce(()=>{ uQ = uSearch.value.trim(); fetchUsers(1); }, 350);
  uSearch.addEventListener('input', doSearch);
  $('#uRefresh').addEventListener('click', ()=>fetchUsers());
  $('#uPrev').addEventListener('click', ()=>{ if(uPage>1) fetchUsers(uPage-1); });
  $('#uNext').addEventListener('click', ()=>{ if(uPage<uPages) fetchUsers(uPage+1); });

  // Edit modal
  const uEditModal = $('#uEditModal');
  const uEditTitle = $('#uEditTitle');
  const uEditIdTg = $('#uEditIdTg');
  let currentUserId = 0;
  let currentTelegramId = '';

  function openEdit(uid, name, tg){
    currentUserId = uid;
    currentTelegramId = tg && tg !== '—' ? tg : '';
    uEditTitle.textContent = `#${uid} • ${name||''}`;
    uEditIdTg.innerHTML = `User ID: <b>${uid}</b>${currentTelegramId?` • Telegram ID: <b>${currentTelegramId}</b>`:''}`;
    uEditModal.classList.remove('hidden');
  }
  function closeEdit(){ uEditModal.classList.add('hidden'); }
  document.querySelectorAll('[data-close="uEditModal"]').forEach(b=>b.addEventListener('click', closeEdit));

  // row actions
  document.addEventListener('click', (e)=>{
    const b = e.target.closest('button.act'); if(!b) return;
    const id = parseInt(b.dataset.id||'0',10);
    const act = b.dataset.act;
    if(act==='edit'){ openEdit(id, b.dataset.name||'', b.dataset.tg||''); return; }
  });

  // Save edit
  $('#eSave').addEventListener('click', async ()=>{
    if(!currentUserId) return;
    const st = $('#eStatus').value;
    const dCredits = parseInt($('#eCredit').value||'0',10);
    const dXcoin   = parseInt($('#eXcoin').value||'0',10);
    const dKcoin   = parseInt($('#eKcoin').value||'0',10);

    // First call: status + credits + XCoin
    const fd1 = new FormData();
    fd1.append('user_id', currentUserId);
    fd1.append('status', st);
    fd1.append('delta_credits', dCredits);
    fd1.append('delta_xcoin', dXcoin);

    try{
      let okAll = true;
      let r = await fetch('/api/admin/user_update.php', {method:'POST', body:fd1, credentials:'same-origin'});
      let j = await r.json().catch(()=>({ok:false}));
      if(!j.ok) { okAll = false; toast(j.msg||'Update failed', true); }

      // Second call only if killer credits also need change
      if (dKcoin !== 0) {
        const fd2 = new FormData();
        fd2.append('user_id', currentUserId);
        fd2.append('adjust_kcoin', dKcoin);
        r = await fetch('/api/admin/user_update.php', {method:'POST', body:fd2, credentials:'same-origin'});
        j = await r.json().catch(()=>({ok:false}));
        if(!j.ok) { okAll = false; toast(j.msg||'Kcoin update failed', true); }
      }

      if(okAll){ toast('User updated'); closeEdit(); fetchUsers(uPage); }
    }catch(e){ toast('Server error', true); }
  });

  // Initial load
  fetchUsers(1);

  // ============== REDEEM ==============
  let rQ = '', rPage = 1, rPages = 1, rLimit = 50;
  let rItems = [];
  const rSearch = $('#rSearch');

  async function fetchCodes(goPage=rPage){
    try{
      const r = await fetch(`/api/admin/redeem_codes.php?q=${encodeURIComponent(rQ)}&page=${goPage}&limit=${rLimit}`, {credentials:'same-origin'});
      const j = await r.json(); if(!j.ok) return;
      rPage = j.page||1; rPages=j.pages||1;
      rItems = j.items||[];
      $('#rPage').textContent=rPage; $('#rPages').textContent=rPages;
      $('#rBody').innerHTML = rItems.map(codeRow).join('') || `<tr><td class="td" colspan="6"><div class="text-slate-400">No codes.</div></td></tr>`;
    }catch(e){}
  }
  function codeRow(x){
    const who = x.redeemed_by
      ? `#${x.redeemed_by}${x.username?(' @'+esc(x.username)) : ''}${x.telegram_id?(' • '+esc(x.telegram_id)) : ''}`
      : '—';
    const isPremium = String(x.status || '').toLowerCase() === 'premium';
    const statusBadge = isPremium
      ? `<span class="badge bg-amber-500/15 text-amber-300">PREMIUM</span>`
      : `<span class="badge bg-slate-500/20 text-slate-300">FREE</span>`;
    const exp = x.expiry_date ? esc(x.expiry_date) : '—';

    return `<tr>
      <td class="td"><code class="text-slate-200">${esc(x.code)}</code></td>
      <td class="td">${nf(x.credits||0)}</td>
      <td class="td">${exp}</td>
      <td class="td">${statusBadge}</td>
      <td class="td">${who}</td>
      <td class="td">
        <div class="flex justify-end gap-1">
          <button class="act" data-copy="${esc(x.code)}">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4a2 2 0 00-2 2v12h2V3h12V1zm3 4H8a2 2 0 00-2 2v13a2 2 0 002 2h11a2 2 0 002-2V7a2 2 0 00-2-2zm0 15H8V7h11v13z"/></svg>
            Copy
          </button>
        </div>
      </td>
    </tr>`;
  }

  const doRSearch = debounce(()=>{ rQ=rSearch.value.trim(); fetchCodes(1); }, 350);
  rSearch.addEventListener('input', doRSearch);
  $('#rRefresh').addEventListener('click', ()=>fetchCodes());
  $('#rPrev').addEventListener('click', ()=>{ if(rPage>1) fetchCodes(rPage-1); });
  $('#rNext').addEventListener('click', ()=>{ if(rPage<rPages) fetchCodes(rPage+1); });

  // Copy button (delegation)
  d.addEventListener('click', e=>{
    const btn = e.target.closest('[data-copy]'); if(!btn) return;
    const txt = btn.dataset.copy||'';
    navigator.clipboard.writeText(txt).then(()=>toast('Copied')).catch(()=>toast('Copy failed', true));
  });

  // Generate codes (credits-only, with status + expiry)
  $('#gCreate').addEventListener('click', async ()=>{
    const count = Math.max(1, Math.min(50, parseInt($('#gCount').value||'1',10)));
    const credits = Math.max(0, parseInt($('#gCredits').value||'0',10));
    const status  = $('#gStatus').value || 'FREE';
    const expiry_date = ($('#gExpiryDate').value||'').trim();
    const expiry_days = Math.max(0, parseInt($('#gExpiryDays').value||'0',10));

    const fd = new FormData();
    fd.append('credits', credits);
    fd.append('count', count);
    fd.append('status', status);
    if (expiry_date) fd.append('expiry_date', expiry_date);
    else if (expiry_days>0) fd.append('expiry_days', String(expiry_days));

    $('#gCreate').disabled = true; $('#gMsg').textContent='';
    try{
      const r = await fetch('/api/admin/generate_code.php', {method:'POST', body:fd, credentials:'same-origin'});
      const j = await r.json();
      if(j.ok){ toast(`Generated ${j.count} code(s)`); $('#gMsg').textContent = (j.codes||[]).join(', '); fetchCodes(1); }
      else { toast(j.msg||'Generate failed', true); }
    }catch(e){ toast('Network error', true); }
    $('#gCreate').disabled = false;
  });

  // Export current page
  // Format:
  // BABACHECKER-XXXXXXXXX-CREDITS
  // {credits} + { expiry date }
// --- Export builder (REPLACE your old one) ---
    function buildExportText(items){
    // কোডের শেষে -FREE / -PREMIUM থাকলে -CREDITS করে দেই
    const toCreditsCode = (code) => {
    if (!code) return '';
    return code;
    };
    
    const toInt = (v) => {
    const n = parseInt(v, 10);
    return Number.isFinite(n) ? n : 0;
    };
    
    const lines = [];
    (items || []).forEach(it => {
    const raw = (it.code || '').trim();
    if (!raw) return;
    
    const code   = toCreditsCode(raw);                 // ← এখানে আসল কোড বসবে
    const credit = toInt(it.credits);                  // ক্রেডিট সংখ্যা
    const expiry = (it.expiry_date && String(it.expiry_date).trim() !== '')
                    ? String(it.expiry_date)           // যেমন: 2025-08-26 00:00:00
                    : '—';                             // না থাকলে ড্যাশ
    
    lines.push(code);
    lines.push(`${credit} + ${expiry}`);
    });
    
    return lines.join('\n');
    }
  $('#rExportCopy').addEventListener('click', ()=>{
    const txt = buildExportText(rItems);
    if(!txt){ toast('Nothing to export', true); return; }
    navigator.clipboard.writeText(txt).then(()=>toast('Export copied')).catch(()=>toast('Copy failed', true));
  });
  $('#rExportTxt').addEventListener('click', ()=>{
    const txt = buildExportText(rItems);
    if(!txt){ toast('Nothing to export', true); return; }
    const blob = new Blob([txt], {type:'text/plain;charset=utf-8'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url; a.download = `credits_export_page_${rPage}.txt`;
    document.body.appendChild(a); a.click(); a.remove();
    setTimeout(()=>URL.revokeObjectURL(url), 1000);
  });

  // initial codes load
  fetchCodes(1);

  // ============== System Settings ==============
  const sysIds = [
    'TELEGRAM_BOT_USERNAME', 'TELEGRAM_BOT_TOKEN', 'TELEGRAM_ADMIN_USERNAME', 
    'TELEGRAM_ANNOUNCE_CHAT_ID', 'TELEGRAM_ALLOWED_IDS',
    'PAYMENT_UPI_ID', 'PAYMENT_QR_LINK',
    'PAYMENT_BINANCE_ID', 'PAYMENT_USDT_TRC20', 'PAYMENT_USDT_BEP20',
    'PAYMENT_BTC_ADDR', 'PAYMENT_LTC_ADDR', 'PAYMENT_TRX_ADDR'
  ];

  async function loadSysSettings(){
    try{
        const r = await fetch('/api/admin/settings.php', {credentials:'same-origin'});
        const j = await r.json(); 
        if(j.ok && j.data){
            sysIds.forEach(key => {
                const el = document.getElementById('sys_'+key);
                if(el) el.value = j.data[key] || '';
            });
        }
    }catch(e){}
  }
  
  if($('#tabSystem')) {
     // Load on tab switch if needed, or just initial load
     loadSysSettings();
  }

  $('#sysSave')?.addEventListener('click', async () => {
      const btn = $('#sysSave'); 
      btn.disabled = true; btn.textContent = 'Saving...';
      const fd = new FormData();
      sysIds.forEach(key => {
          const el = document.getElementById('sys_'+key);
          if(el) fd.append(key, el.value.trim());
      });

      try{
          const r = await fetch('/api/admin/settings.php', {method:'POST', body:fd, credentials:'same-origin'});
          const j = await r.json();
          if(j.ok) toast('Settings Saved');
          else toast('Save failed', true);
      }catch(e){ toast('Network error', true); }
      btn.disabled = false; btn.textContent = 'Save System Settings';
  });

  // Admin Creds Update
  $('#admSaveCreds')?.addEventListener('click', async () => {
      const u = $('#adm_username').value.trim();
      const p = $('#adm_password').value.trim();
      
      if (!u && !p) { toast('Enter new username or password', true); return; }
      if (p && p.length < 6) { toast('Password must be 6+ chars', true); return; }

      const btn = $('#admSaveCreds');
      btn.disabled = true; btn.textContent = 'Updating...';
      
      const fd = new FormData();
      if(u) fd.append('username', u);
      if(p) fd.append('password', p);

      try{
          const r = await fetch('/api/admin/update_profile.php', {method:'POST', body:fd, credentials:'same-origin'});
          const j = await r.json();
          if(j.ok) {
              toast('Credentials Updated!');
              $('#adm_password').value = ''; 
          } else {
              toast(j.error || 'Update failed', true);
          }
      }catch(e){ toast('Network error', true); }
      btn.disabled = false; btn.textContent = 'Update Credentials';
  });

  $('#sysCache')?.addEventListener('click', ()=>toast('Cache cleared (demo)'));
  $('#sysPing')?.addEventListener('click', ()=>toast('DB OK (demo)'));

  // Toast
  let tdiv;
  function toast(msg, err=false){
    if(!tdiv){
      tdiv = document.createElement('div');
      tdiv.className = 'fixed bottom-4 left-1/2 -translate-x-1/2 z-50';
      document.body.appendChild(tdiv);
    }
    const el = document.createElement('div');
    el.className = 'mt-2 px-3 py-2 rounded-xl text-sm '+(err?'bg-rose-500/90':'bg-emerald-500/90')+' text-white';
    el.textContent = msg;
    tdiv.appendChild(el);
    setTimeout(()=>{ el.style.opacity='0'; el.style.transform='translateY(6px)'; el.style.transition='all .2s'; setTimeout(()=>el.remove(),200); }, 1600);
  }
})();
</script>
