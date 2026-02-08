<?php
declare(strict_types=1);
/**
 * Master layout: fixed Header + Left + Right
 * Center content comes from $viewFile (set in router.php)
 * Variables expected from router.php:
 *   $title, $u_* (u_username,u_name,u_pic,u_status,u_credits,u_cash,u_lives,u_charges,u_hits,u_lastlogin),
 *   $bannerHtml,$showBanner,$onlineSSR,$topSSR,$view (or $pageKey)
 */
$pageKey = $pageKey ?? ($view ?? 'dashboard');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($title)? htmlspecialchars($title, ENT_QUOTES) : 'BabaChecker' ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/@tailwindcss/browser@4"></script>

  <style>
    /* --- NEON GREEN THEME --- */
    :root {
      --neon-primary: #39ff14;
      --neon-secondary: #0eff80;
      --neon-accent: #ccff00;
      --glass-bg: rgba(20, 20, 20, 0.6);
      --glass-border: rgba(57, 255, 20, 0.3);
      --card-bg: rgba(10, 10, 10, 0.7);
    }
    html, body { height: 100%; font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial; }
    body { overflow: hidden; background: #050505; color: #e5e7eb; }

    /* Animations */
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes pulseGlow { 0% { box-shadow: 0 0 5px rgba(57, 255, 20, 0.2); } 50% { box-shadow: 0 0 20px rgba(57, 255, 20, 0.5); } 100% { box-shadow: 0 0 5px rgba(57, 255, 20, 0.2); } }
    @keyframes slideInRight { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

    .animate-enter { animation: fadeInUp 0.6s ease-out forwards; }
    .animate-delay-1 { animation-delay: 0.1s; }
    .animate-delay-2 { animation-delay: 0.2s; }
    .animate-delay-3 { animation-delay: 0.3s; }

    .glass { backdrop-filter: blur(12px); background: var(--glass-bg); border: 1px solid var(--glass-border); }
    .card  { border-radius: 1rem; padding: 1rem; box-shadow: 0 10px 30px rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.05); background: var(--card-bg); transition: transform 0.2s, box-shadow 0.2s; }
    .card:hover { transform: translateY(-2px); box-shadow: 0 15px 40px rgba(57, 255, 20, 0.15); border-color: var(--neon-primary); }

    .mainGrid { height: 100vh; }
    .scrollCenter { height: 100vh; overflow-y: auto; -webkit-overflow-scrolling: touch; }

    /* Hide scrollbars globally but keep scrolling */
    .no-scrollbar { overflow-y: auto; -webkit-overflow-scrolling: touch; -ms-overflow-style: none; scrollbar-width: none; }
    .no-scrollbar::-webkit-scrollbar { width: 0 !important; height: 0 !important; display: none !important; background: transparent !important; }
    .no-scrollbar::-webkit-scrollbar-thumb { background: transparent !important; }

    /* Right panel / modal lists explicit */
    #rightTopList, #rightOnlineList, #topModalList, #onlineModalList {
      overflow-y: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none; -ms-overflow-style: none;
    }
    #rightTopList::-webkit-scrollbar,
    #rightOnlineList::-webkit-scrollbar,
    #topModalList::-webkit-scrollbar,
    #onlineModalList::-webkit-scrollbar { width:0!important; height:0!important; background:transparent!important; display:none!important; }

    /* Outer cards never show their own scrollbar */
    #cardTopUsers, #cardOnlineUsers {
      overflow: hidden; -ms-overflow-style:none; scrollbar-width:none;
    }
    #cardTopUsers::-webkit-scrollbar, #cardOnlineUsers::-webkit-scrollbar { width:0!important;height:0!important;display:none!important;background:transparent!important; }

    /* Mobile dock (fixed bottom) */
    .mobile-dock {
      position: fixed; left: 0; right: 0; bottom: 0; z-index: 50;
      padding: 10px 14px calc(10px + env(safe-area-inset-bottom));
      background: rgba(5, 5, 5, 0.9);
      backdrop-filter: blur(20px);
      border-top: 1px solid var(--neon-primary);
      box-shadow: 0 -5px 20px rgba(57, 255, 20, 0.1);
    }
    .dock-btn { flex: 1 1 0; display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 8px 6px; border-radius: 14px; color: #cbd5e1; transition: color 0.3s; }
    .dock-btn:hover { color: var(--neon-primary); background: rgba(57, 255, 20, 0.05); }
    .dock-icon { width: 22px; height: 22px; opacity: .95; }

    /* User Tile */
    .user-tile {
      position: relative; border-radius: 12px; padding: 10px;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.05);
      transition: all 0.3s ease;
    }
    .user-tile:hover {
      background: rgba(57, 255, 20, 0.05);
      border-color: var(--neon-primary);
      box-shadow: 0 0 10px rgba(57, 255, 20, 0.2);
    }

    /* Mobile slide menu */
    #mSide { transform: translateX(-100%); transition: transform .28s ease; }
    #mSide.open { transform: translateX(0%); }
    #mOverlay { opacity: 0; transition: opacity .2s ease; }
    #mOverlay.show { opacity: .55; }

    /* Global Statistics styles */
    .gs-panel {
      border-radius:16px; padding:16px 16px 18px;
      background: linear-gradient(145deg, rgba(10,10,10,0.8), rgba(20,20,20,0.9));
      border: 1px solid var(--neon-primary);
      box-shadow: 0 0 15px rgba(57, 255, 20, 0.1);
      animation: fadeInUp 0.8s ease-out;
    }
    .gs-head{display:flex;align-items:center;gap:10px;margin-bottom:14px}
    .gs-chip{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;
      background: rgba(57, 255, 20, 0.1); border: 1px solid var(--neon-primary); color: var(--neon-primary); }
    .gs-title{font-weight:600;color: #fff; text-shadow: 0 0 5px var(--neon-primary);}
    .gs-sub{font-size:12px;color:#9aa4b2;margin-top:2px}
    .gs-grid{display:grid;gap:16px}
    @media (min-width:640px){.gs-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media (min-width:1280px){.gs-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}

    .gs-card{
      position:relative;border-radius:14px;padding:18px 16px;
      border:1px solid rgba(255,255,255,.08);
      background: rgba(30, 30, 30, 0.4);
      box-shadow: 0 5px 15px rgba(0,0,0,0.3);
      color:#e6e9ee; transition: all 0.3s;
    }
    .gs-card:hover {
        border-color: var(--neon-primary);
        box-shadow: 0 0 15px rgba(57, 255, 20, 0.3);
        transform: translateY(-3px);
    }
    .gs-card .gs-icon{
      width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;
      margin-bottom:10px;border:1px solid rgba(255,255,255,.1); background: rgba(255,255,255,0.05);
    }
    .gs-card .gs-icon svg{width:18px;height:18px;display:block;opacity:.95}
    .gs-num{font-weight:800;font-size:28px;line-height:1; color: var(--neon-primary); text-shadow: 0 0 8px rgba(57, 255, 20, 0.6);}
    .gs-label{font-size:12px;color:#cbd5e1;margin-top:6px}

    /* Common Gradients overridden for Neon */
    .gs-blue, .gs-green, .gs-red, .gs-purple { background: transparent; } /* Remove old gradients */

    /* Scrollbar Hide */
    html, body, .scrollCenter, #rightTopList, #rightOnlineList, #topModalList, #onlineModalList, aside, .no-scrollbar {
      -ms-overflow-style: none; scrollbar-width: none;
    }
    html::-webkit-scrollbar, body::-webkit-scrollbar, .scrollCenter::-webkit-scrollbar, aside::-webkit-scrollbar, .no-scrollbar::-webkit-scrollbar {
      width: 0 !important; height: 0 !important; display: none !important; background: transparent !important;
    }
    .no-scrollbar { overflow-y: auto; -webkit-overflow-scrolling: touch; }
  </style>
</head>
<body class="bg-slate-950 text-slate-100">

<!-- MOBILE DOCK -->
<nav class="mobile-dock lg:hidden">
  <div class="flex items-center gap-2">
    <button id="dockMenu" class="dock-btn" aria-label="Menu">
      <svg class="dock-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M3 6h18v2H3zM3 11h18v2H3zM3 16h18v2H3z"/></svg>
    </button>
    <button id="dockTop" class="dock-btn" aria-label="Top Users">
      <svg class="dock-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3 6 6 .9-4.5 4.2 1.1 6.1L12 16.8 6.4 19.2 7.5 13.1 3 8.9l6-.9z"/></svg>
    </button>
    <button id="dockOnline" class="dock-btn" aria-label="Online Users">
      <svg class="dock-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M9 12c2.21 0 4-1.79 4-4S11.21 4 9 4 5 5.79 5 8s1.79 4 4 4zm8 0c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3zM9 14c-2.67 0-8 1.34-8 4v2h10.09A7 7 0 009 14zm8 0c-1.3 0-2.53.2-3.6.56A6.97 6.97 0 0119 20h5v-2c0-2.66-5.33-4-8-4z"/></svg>
    </button>
  </div>
</nav>

<!-- MOBILE OVERLAY (for sidebar) -->
<div id="mOverlay" class="fixed inset-0 bg-black hidden z-40"></div>


<div class="mainGrid grid grid-cols-1">

  <!-- MOBILE SIDEBAR (all pages) -->
  <aside id="mSide" class="lg:hidden fixed inset-y-0 left-0 w-72 z-50 bg-slate-900/95 border-r border-white/10 p-4 no-scrollbar">
    <a href="/app/settings" class="flex items-center gap-3 mb-4">
      <?php
        $unameSafe = htmlspecialchars($u_username ?? '', ENT_QUOTES);
        $nameSafe  = htmlspecialchars($u_name ?? '', ENT_QUOTES);
        $picSrc = !empty($u_pic) ? htmlspecialchars($u_pic, ENT_QUOTES)
                : 'https://api.dicebear.com/7.x/identicon/svg?seed='.urlencode($unameSafe);
        $roleHtml = function($status){
          $m = [
            'admin'   => ['bg'=>'bg-rose-500/15','text'=>'text-rose-300','label'=>'ADMIN'],
            'premium' => ['bg'=>'bg-amber-500/15','text'=>'text-amber-300','label'=>'PREMIUM'],
            'free'    => ['bg'=>'bg-slate-500/20','text'=>'text-slate-300','label'=>'FREE'],
            'banned'  => ['bg'=>'bg-rose-500/15','text'=>'text-rose-300','label'=>'BANNED'],
          ][$GLOBALS['u_status'] ?? 'free'] ?? ['bg'=>'bg-slate-500/20','text'=>'text-slate-300','label'=>'FREE'];
          return "<span class=\"inline-flex items-center rounded-md px-2 py-[2px] text-[10px] font-semibold {$m['bg']} {$m['text']}\">{$m['label']}</span>";
        };
      ?>
      <img src="<?= $picSrc ?>" class="w-9 h-9 rounded-lg object-cover" alt=""
           onerror="this.onerror=null;this.src='https://api.dicebear.com/7.x/identicon/svg?seed=<?=urlencode($unameSafe)?>'">
      <div class="min-w-0">
        <div class="font-semibold truncate max-w-[130px]"><?= $nameSafe ?></div>
        <div class="text-xs text-slate-400 truncate">@<?= $unameSafe ?></div>
      </div>
      <?= $roleHtml($u_status ?? 'free') ?>
    </a>
    <nav class="px-1 text-sm space-y-1">
      <a class="block px-3 py-2 rounded-xl hover:bg-white/5 <?= $pageKey==='dashboard'?'bg-white/5':'' ?>" href="/app/dashboard">Dashboard</a>
      <a class="block px-3 py-2 rounded-xl hover:bg-white/5 <?= $pageKey==='deposit'?'bg-white/5':'' ?>" href="/app/deposit">Deposit XCoin</a>
      <a class="block px-3 py-2 rounded-xl hover:bg-white/5 <?= $pageKey==='buy'?'bg-white/5':'' ?>" href="/app/buy">Buy Premium</a>
      <a class="block px-3 py-2 rounded-xl hover:bg-white/5 <?= $pageKey==='redeem'?'bg-white/5':'' ?>" href="/app/redeem">Redeem</a>
      <a class="block px-3 py-2 rounded-xl hover:bg-white/5 <?= $pageKey==='checkers'?'bg-white/5':'' ?>" href="/app/checkers">Checkers</a>
      <a class="block px-3 py-2 rounded-xl hover:bg-white/5 <?= $pageKey==='autohitters'?'bg-white/5':'' ?>" href="/app/autohitters">Auto Hitters</a>
      <a class="block px-3 py-2 rounded-xl hover:bg-white/5 <?= $pageKey==='killers'?'bg-white/5':'' ?>" href="/app/killers">CC Killers</a>
      <a class="block px-3 py-2 rounded-xl hover:bg-white/5 <?= $pageKey==='settings'?'bg-white/5':'' ?>" href="/app/settings">Settings</a>
      <?php if (($u_status ?? '') === 'admin'): ?>
      <a class="block px-3 py-2 rounded-xl hover:bg-white/5 <?= $pageKey==='admin'?'bg-white/5':'' ?>" href="/app/admin">Admin Panel</a>
      <?php endif; ?>
      <div class="pt-6">
        <a href="/logout.php" class="w-full inline-flex items-center justify-center rounded-xl bg-rose-500/15 text-rose-300 px-3 py-2 hover:bg-rose-500/20">Logout</a>
      </div>
    </nav>
  </aside>

  <!-- CENTER -->
  <main class="scrollCenter relative">
    <!-- Header (fixed) -->
    <header class="sticky top-0 z-20 bg-slate-950/80 backdrop-blur border-b border-white/10">
      <div class="px-4 lg:px-6 py-3 flex items-center justify-between gap-4">
        
        <!-- Left: Logo & Nav -->
        <div class="flex items-center gap-6">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-500 to-emerald-400 flex items-center justify-center shadow">
              <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zM7.5 12l2.5 2.5L16.5 8l1.5 1.5-8 8L6 13.5 7.5 12z"/></svg>
            </div>
            <div class="font-semibold hidden sm:block">BabaChecker</div>
          </div>

          <!-- Desktop Horizontal Nav -->
          <nav class="hidden lg:flex items-center gap-1 text-sm bg-white/5 rounded-2xl p-1 border border-white/5">
             <a class="px-3 py-1.5 rounded-xl hover:bg-white/10 transition-colors <?= $pageKey==='dashboard'?'bg-white/10 text-emerald-300 shadow-[0_0_10px_rgba(57,255,20,0.15)]':'text-slate-400 hover:text-emerald-200' ?>" href="/app/dashboard">Dashboard</a>
             <a class="px-3 py-1.5 rounded-xl hover:bg-white/10 transition-colors <?= $pageKey==='deposit'?'bg-amber-500/20 text-amber-300':'text-slate-400 hover:text-amber-200' ?>" href="/app/deposit">Deposit</a>
             <a class="px-3 py-1.5 rounded-xl hover:bg-white/10 transition-colors <?= $pageKey==='buy'?'bg-amber-500/20 text-amber-300':'text-slate-400 hover:text-amber-200' ?>" href="/app/buy">Premium</a>
             <a class="px-3 py-1.5 rounded-xl hover:bg-white/10 transition-colors <?= $pageKey==='checkers'?'bg-white/10 text-emerald-300':'text-slate-400 hover:text-emerald-200' ?>" href="/app/checkers">Checkers</a>
             <a class="px-3 py-1.5 rounded-xl hover:bg-white/10 transition-colors <?= $pageKey==='autohitters'?'bg-white/10 text-emerald-300':'text-slate-400 hover:text-emerald-200' ?>" href="/app/autohitters">AutoHit</a>
             <div class="w-px h-4 bg-white/10 mx-1"></div>
             <?php if (($u_status ?? '') === 'admin'): ?>
               <a class="px-3 py-1.5 rounded-xl hover:bg-white/10 transition-colors <?= $pageKey==='admin'?'bg-rose-500/20 text-rose-300':'text-slate-400 hover:text-rose-200' ?>" href="/app/admin">Admin</a>
             <?php endif; ?>
             <a class="px-3 py-1.5 rounded-xl hover:bg-white/10 transition-colors <?= $pageKey==='settings'?'bg-white/10 text-emerald-300':'text-slate-400 hover:text-emerald-200' ?>" href="/app/settings">Settings</a>
          </nav>
        </div>

        <!-- Right: Clock & User -->
        <div class="flex items-center gap-4">
           <!-- live clock -->
          <div class="hidden xl:inline-flex items-center gap-2 rounded-full bg-white/5 border border-white/5 px-3 py-1.5 text-xs text-slate-300 font-mono shadow-inner">
            <svg class="w-4 h-4 opacity-70" viewBox="0 0 24 24" fill="currentColor"><path d="M12 20a8 8 0 1 1 0-16 8 8 0 0 1 0 16zm.5-12h-1v5l4 2 .5-.9-3.5-1.8V8z"/></svg>
            <span id="liveClock">--:--:--</span>
          </div>

           <!-- User Profile (Desktop) -->
           <div class="hidden lg:flex items-center gap-3 pl-4 border-l border-white/10">
              <a href="/app/settings" class="flex items-center gap-3 hover:opacity-80 transition">
                 <div class="text-right hidden xl:block">
                    <div class="text-sm font-medium text-slate-200"><?= $nameSafe ?></div>
                    <div class="text-xs text-slate-500">@<?= $unameSafe ?></div>
                 </div>
                 <img src="<?= $picSrc ?>" class="w-9 h-9 rounded-xl object-cover border border-white/10 shadow-sm" alt="">
              </a>
              <a href="/logout.php" class="p-2 rounded-xl bg-white/5 hover:bg-rose-500/10 hover:text-rose-300 text-slate-400 transition" title="Logout">
                 <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M16 13v-2H7V8l-5 4 5 4v-3h9zM20 3h-8v2h8v14h-8v2h8a2 2 0 002-2V5a2 2 0 00-2-2z"/></svg>
              </a>
           </div>

           <!-- Mobile Menu Button -->
           <button id="dockMenu" class="lg:hidden p-2 rounded-xl bg-white/10 text-slate-300">
              <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path d="M3 6h18v2H3zM3 11h18v2H3zM3 16h18v2H3z"/></svg>
           </button>
        </div>
      </div>

      <?php if (!empty($showBanner)): ?>
      <div class="px-4 lg:px-6 pb-3">
        <div class="rounded-2xl border border-amber-400/30 bg-gradient-to-r from-amber-500/10 to-rose-500/10 text-amber-200 px-4 py-3 flex items-start gap-3">
          <svg class="w-5 h-5 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
          <div class="text-sm leading-relaxed"><?= $bannerHtml ?></div>
        </div>
      </div>
      <?php endif; ?>
    </header>

    <!-- CENTER CONTENT -->
    <section class="p-4 lg:p-6 space-y-6 pb-28 lg:pb-6">
      <?php if (isset($viewFile) && is_file($viewFile)) { include $viewFile; } ?>
    </section>
  </main>
</div>

<!-- MODALS -->
<div id="topModal" class="fixed inset-0 z-50 hidden"><div class="absolute inset-0 bg-black/60"></div>
  <div class="absolute inset-x-4 md:inset-x-20 top-10 bottom-10 rounded-2xl bg-slate-900 border border-white/10 flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-white/10">
      <div class="font-semibold">Top Users</div>
      <button data-close="topModal" class="rounded-md bg-white/10 hover:bg-white/20 p-2" aria-label="Close">
        <!-- FIX: visible cross icon -->
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>
    <div class="p-4 flex-1 overflow-hidden">
      <div id="topModalList" class="no-scrollbar h-full space-y-2 pr-1"></div>
    </div>
  </div>
</div>

<div id="onlineModal" class="fixed inset-0 z-50 hidden"><div class="absolute inset-0 bg-black/60"></div>
  <div class="absolute inset-x-4 md:inset-x-20 top-10 bottom-10 rounded-2xl bg-slate-900 border border-white/10 flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-white/10">
      <div class="font-semibold">Online Users • <span id="modalOnlineNow">—</span></div>
      <button data-close="onlineModal" class="rounded-md bg-white/10 hover:bg-white/20 p-2" aria-label="Close">
        <!-- FIX: visible cross icon -->
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>
    <div class="p-4 flex-1 overflow-hidden">
      <div id="onlineModalList" class="no-scrollbar h-full space-y-2 pr-1"></div>
    </div>
  </div>
</div>

<script>
(function(w, d){
  if (w.__CX_INIT__) return; w.__CX_INIT__ = true;
  const $ = sel => d.querySelector(sel);
  const nf = n => new Intl.NumberFormat().format(n|0);

  // Live clock
  const tickClock = () => { const el=$("#liveClock"); if(el) el.textContent=new Date().toLocaleTimeString(); };
  tickClock(); setInterval(tickClock, 1000);

  // Mobile sidebar open/close
  const mSide = $("#mSide");
  const mOverlay = $("#mOverlay");
  function openMenu(){ if(!mSide) return; mSide.classList.add('open'); if(mOverlay){ mOverlay.classList.remove('hidden'); mOverlay.classList.add('show','z-40'); } }
  function closeMenu(){ if(!mSide) return; mSide.classList.remove('open'); if(mOverlay){ mOverlay.classList.remove('show'); setTimeout(()=>mOverlay.classList.add('hidden'), 180); } }
  $("#dockMenu")?.addEventListener('click', () => { if (mSide?.classList.contains('open')) closeMenu(); else openMenu(); });
  mOverlay?.addEventListener('click', closeMenu);
  w.addEventListener('keydown', (e)=>{ if(e.key==='Escape') closeMenu(); });

  // Heartbeat
  const heartbeat = () => fetch('/api/heartbeat.php',{credentials:'same-origin'}).catch(()=>{});
  heartbeat(); setInterval(heartbeat, 30000);
  
  // guard so it doesn't attach twice
  if (!window.__CX_IDLE__) {
    window.__CX_IDLE__ = true;

    (function () {
      const LIMIT = 2880 * 60 * 1000; // 10 minutes
      let tm;

      function resetTimer() {
        clearTimeout(tm);
        tm = setTimeout(() => {
          // call server logout, then go to login with expired flag
          fetch('/logout.php?timeout=1', { credentials: 'include' })
            .finally(() => { location.href = '/?expired=1'; });
        }, LIMIT);
      }

      ['click','mousemove','keydown','scroll','touchstart','visibilitychange']
        .forEach(ev => document.addEventListener(ev, resetTimer, { passive:true }));

      resetTimer();
    })();
  }

  // Stats (if present)
  async function fetchStats() {
    try {
      const r = await fetch('/api/stats.php', {credentials:'same-origin'}); if (!r.ok) return;
      const j = await r.json(); if (!j.ok) return;
      const map = { gTotalUsers:'total_users', gLiveCards:'live_cards', gChargeCards:'charge_cards', gTotalHits:'total_hits' };
      Object.entries(map).forEach(([id,key])=>{ const el=d.getElementById(id); if (el) el.textContent = nf(j.data[key]); });
      const rightNow = d.getElementById('rightOnlineNow'); if (rightNow) rightNow.textContent = j.data.online_now;
    } catch(e){}
  }
  fetchStats(); setInterval(fetchStats, 10000);

  // Tiles builder
  function badge(status){
    status=(status||'').toLowerCase();
    const m={admin:['bg-rose-500/15','text-rose-300','ADMIN'],premium:['bg-amber-500/15','text-amber-300','PREMIUM'],banned:['bg-rose-500/15','text-rose-300','BANNED'],free:['bg-slate-500/20','text-slate-300','FREE']}[status]||['bg-slate-500/20','text-slate-300','FREE'];
    return `<span class="inline-flex items-center rounded-md px-2 py-[2px] text-[10px] font-semibold ${m[0]} ${m[1]}">${m[2]}</span>`;
  }
  function tileOnline(u){
    const img = u.profile ? u.profile : 'https://api.dicebear.com/7.x/shapes/svg?seed='+encodeURIComponent(u.username||'user');
    return `
      <div class="user-tile flex items-center gap-3">
        <div class="relative shrink-0">
          <img src="${img}" class="w-10 h-10 rounded-lg object-cover" alt="">
          <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full ${u.is_online?'bg-emerald-400':'bg-slate-500'} ring-2 ring-slate-900"></span>
        </div>
        <div class="min-w-0 flex-1">
          <div class="text-sm font-medium truncate">${(u.full_name||u.username||'user')}</div>
          <div class="text-xs text-slate-400 truncate">@${(u.username||'user')}</div>
        </div>
        ${badge(u.status)}
      </div>`;
  }
  function tileTop(u){
    const img = u.profile ? u.profile : 'https://api.dicebear.com/7.x/shapes/svg?seed='+encodeURIComponent(u.username||'user');
    return `
      <div class="user-tile flex items-center gap-3">
        <div class="relative shrink-0">
          <img src="${img}" class="w-10 h-10 rounded-lg object-cover" alt="">
          ${u.is_online ? `<span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-emerald-400 ring-2 ring-slate-900"></span>` : ``}
        </div>
        <div class="min-w-0 flex-1">
          <div class="flex items-center gap-2">
            <div class="text-sm font-medium truncate">${(u.full_name||u.username||'user')}</div>
            ${badge(u.status)}
          </div>
          <div class="text-xs text-slate-400 truncate">@${(u.username||'user')}</div>
        </div>
        <div class="text-xs rounded-md px-2 py-0.5 bg-violet-500/15 text-violet-300 font-medium">${nf(u.hits||0)} Hits</div>
      </div>`;
  }

  async function fetchUsers(scope, targetId) {
    try {
      const r = await fetch('/api/online_users.php?scope='+encodeURIComponent(scope)+'&limit=100', {credentials:'same-origin'});
      const j = await r.json(); if (!j.ok) return;
      if (scope==='online') { const els=[d.getElementById('rightOnlineNow'), d.getElementById('modalOnlineNow')]; els.forEach(el=>{ if(el) el.textContent = j.count ?? (j.users?.length||0); }); }
      const box = d.getElementById(targetId); if (!box) return;
      const arr = Array.isArray(j.users)? j.users : [];
      box.innerHTML = arr.map(u => scope==='top' ? tileTop(u) : tileOnline(u)).join('') ||
        `<div class="text-sm text-slate-500">${scope==='top'?'No data.':'No one online.'}</div>`;
    } catch(e){}
  }

  if (d.getElementById('rightOnlineList')) {
    fetchUsers('online','rightOnlineList'); fetchUsers('top','rightTopList');
    setInterval(()=>{ fetchUsers('online','rightOnlineList'); fetchUsers('top','rightTopList'); },15000);
  }

  // Modals (mobile)
  const topModal = d.getElementById('topModal');
  const onlineModal = d.getElementById('onlineModal');
  d.getElementById('dockTop')?.addEventListener('click', () => { topModal?.classList.remove('hidden'); fetchUsers('top','topModalList'); });
  d.getElementById('dockOnline')?.addEventListener('click', () => { onlineModal?.classList.remove('hidden'); fetchUsers('online','onlineModalList'); });
  d.querySelectorAll('[data-close]')?.forEach(btn => btn.addEventListener('click', () => d.getElementById(btn.dataset.close)?.classList.add('hidden')));
  [topModal, onlineModal].forEach(m => m?.addEventListener('click', (e) => { if (e.target === m.firstElementChild) m.classList.add('hidden'); }));
})(window, document);
</script>
</body>
</html>
