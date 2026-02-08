<?php
declare(strict_types=1);
/**
 * Center content for Dashboard
 * Uses helper funcs b(), roleBadge() provided by router.php
 * Expects variables from router: $u_username,$u_name,$u_pic,$u_status,$u_credits,$u_cash,$u_lives,$u_charges,$u_hits,$u_lastlogin,$u_expiry,$expDays
 */
?>
<style>
  .tile {
    border-radius:16px; padding:16px; color:#e5e7eb;
    background: rgba(20, 20, 20, 0.4);
    border: 1px solid rgba(57, 255, 20, 0.2);
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    transition: all 0.3s ease;
    animation: fadeInUp 0.6s ease-out backwards;
  }
  .tile:hover {
    border-color: var(--neon-primary);
    box-shadow: 0 0 15px rgba(57, 255, 20, 0.25);
    transform: translateY(-5px);
  }
  /* Unified Neon Tile Color */
  .tile-green, .tile-red, .tile-blue, .tile-purple { background: transparent; }

  .tile .k{font-size:30px;font-weight:700;line-height:1.1;margin-top:6px; color: #fff; text-shadow: 0 0 5px var(--neon-primary);}
  .tile .sub{font-size:12px;opacity:.8; color: #aaa;}
  .icon-pill{
    width:32px;height:32px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;
    background: rgba(57, 255, 20, 0.15); border: 1px solid var(--neon-primary); color: var(--neon-primary);
    box-shadow: 0 0 5px var(--neon-primary);
  }
</style>

<!-- WELCOME -->
<div class="card p-5 animate-enter" style="background: linear-gradient(135deg, rgba(20,20,20,0.9), rgba(10,10,10,0.95)); border: 1px solid var(--neon-primary); box-shadow: 0 0 15px rgba(57, 255, 20, 0.15);">
  <div class="flex items-start gap-4 flex-wrap">
    <?php
      $unameSafe = htmlspecialchars($u_username ?? '', ENT_QUOTES);
      $nameSafe  = htmlspecialchars($u_name ?? $u_username ?? '', ENT_QUOTES);
      $picSrc = !empty($u_pic)
        ? htmlspecialchars($u_pic, ENT_QUOTES)
        : 'https://api.dicebear.com/7.x/identicon/svg?seed='.urlencode($u_username ?? 'user');
    ?>
    <div class="relative">
        <img src="<?= $picSrc ?>" class="w-16 h-16 rounded-2xl object-cover border border-white/10 shadow-lg" alt=""
             onerror="this.onerror=null;this.src='https://api.dicebear.com/7.x/identicon/svg?seed=<?=urlencode($u_username??'user')?>'">
        <div class="absolute inset-0 rounded-2xl ring-1 ring-inset ring-white/10"></div>
    </div>
    <div class="flex-1 min-w-[220px]">
      <div class="flex items-center justify-between gap-2">
        <div class="flex items-center gap-2 flex-wrap">
          <h2 class="text-2xl font-bold truncate max-w-[260px] sm:max-w-none text-transparent bg-clip-text bg-gradient-to-r from-white to-slate-400" style="text-shadow: 0 0 10px rgba(255,255,255,0.1);"><?= $nameSafe ?></h2>
          <?= roleBadge($u_status ?? 'free') ?>
          <?php if(($u_status??'')==='admin'): ?>
            <a href="/app/admin" class="ml-2 px-3 py-1 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs font-bold uppercase tracking-wider hover:bg-rose-500/20 transition-all flex items-center gap-1 shadow-[0_0_10px_rgba(244,63,94,0.15)]">
              <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
              Admin Panel
            </a>
          <?php endif; ?>
          <!-- DEBUG: Current status is: [<?= htmlspecialchars($u_status ?? 'null') ?>] -->
        </div>
      </div>
      <div class="text-sm text-slate-300 mt-1">Welcome system ready. <span style="color:var(--neon-primary)">● System Online</span></div>
      <div class="mt-2 text-xs text-slate-500 font-mono">LAST_LOGIN: <span class="text-slate-400"><?= htmlspecialchars($u_lastlogin ?? '—', ENT_QUOTES) ?></span></div>
    </div>

    <!-- Credits / Cash -->
    <div class="grid grid-cols-3 gap-3 ml-auto">
      <div class="rounded-xl border border-white/5 bg-white/5 px-4 py-3 text-center transition hover:bg-white/10">
        <div class="text-[10px] uppercase tracking-wider text-slate-400 mb-1">Credits</div>
        <div id="meCredits" class="font-bold text-lg text-emerald-400" style="text-shadow: 0 0 10px rgba(52, 211, 153, 0.5);"><?= b($u_credits ?? 0) ?></div>
      </div>
      <div class="rounded-xl border border-white/5 bg-white/5 px-4 py-3 text-center transition hover:bg-white/10">
        <div class="text-[10px] uppercase tracking-wider text-slate-400 mb-1">KCoin</div>
        <div id="meKcoin" class="font-bold text-lg text-rose-400" style="text-shadow: 0 0 10px rgba(251, 113, 133, 0.5);"><?= b($u_kcoin ?? 0) ?></div>
      </div>
      <div class="rounded-xl border border-white/5 bg-white/5 px-4 py-3 text-center transition hover:bg-white/10">
        <div class="text-[10px] uppercase tracking-wider text-slate-400 mb-1">XCoin</div>
        <div id="meCash" class="font-bold text-lg text-amber-400" style="text-shadow: 0 0 10px rgba(251, 191, 36, 0.5);">$<?= b($u_cash ?? 0) ?></div>
      </div>
    </div>
  </div>
</div>


<!-- DAILY CLAIM CARD -->
<!-- DAILY CLAIM CARD -->
<style>
  .claim-card {
    border-radius:18px; overflow:hidden;
    border:1px solid var(--neon-primary);
    background: linear-gradient(135deg, rgba(10,20,10,0.95), rgba(5,5,5,1));
    box-shadow: 0 0 20px rgba(57, 255, 20, 0.1);
    animation: fadeInUp 0.7s ease-out backwards;
    animation-delay: 0.1s;
  }
  .claim-glow {
    position:absolute; inset:0;
    background: radial-gradient(circle at 50% -20%, rgba(57, 255, 20, 0.15), transparent 70%);
    pointer-events:none;
  }
  .claim-btn {
    transition: all 0.2s ease;
    border: 1px solid var(--neon-primary);
    background: rgba(57, 255, 20, 0.1);
    color: var(--neon-primary);
    box-shadow: 0 0 10px rgba(57, 255, 20, 0.2);
  }
  .claim-btn:hover:not(:disabled) {
    background: var(--neon-primary);
    color: #000;
    box-shadow: 0 0 20px var(--neon-primary);
    transform: translateY(-1px);
  }
  .claim-btn:disabled { opacity: 0.5; cursor: not-allowed; filter: grayscale(1); }
  .claim-chip{font-size:12px;padding:.25rem .5rem;border-radius:.5rem;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.03)}
</style>

<div class="mt-6 relative claim-card p-6">
  <div class="claim-glow"></div>

  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 relative z-10">
    <div class="flex items-start gap-4">
      <div class="shrink-0 w-12 h-12 rounded-xl border border-white/10 flex items-center justify-center" style="background: rgba(57, 255, 20, 0.05);">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--neon-primary)" stroke-width="2"><path d="M12 2l3 7h7l-5.5 4 2 7-6.5-4.5L5.5 19l2-7L2 8h7z" stroke-linejoin="round"/></svg>
      </div>
      <div>
        <div class="text-xl font-bold text-white">Daily Credit — Claim <span style="color:var(--neon-primary)">+50</span></div>
        <div id="claimMsg" class="text-sm text-slate-400 mt-0.5">You can claim once every calendar day.</div>
        <div class="mt-3 flex flex-wrap items-center gap-2">
          <span class="claim-chip text-emerald-300 border-emerald-500/30">Reward: <b>+50</b> credits</span>
          <span id="chipState" class="claim-chip text-slate-300">Status: <b>Checking…</b></span>
          <span id="chipReset" class="claim-chip hidden text-slate-400">Resets in: <b class="claim-count font-mono text-white" id="resetTimer">—</b></span>
        </div>
      </div>
    </div>

    <div class="flex items-center gap-3">
      <button id="btnClaim" class="claim-btn rounded-xl px-6 py-3 font-bold text-sm uppercase tracking-wide">
        Claim now
      </button>
    </div>
  </div>
</div>

<!-- SUMMARY TILES -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mt-6">
  <div class="tile animate-delay-2">
    <div class="flex items-center gap-3">
      <span class="icon-pill">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 7a5 5 0 105 5 5.006 5.006 0 00-5-5zm0-5a10 10 0 11-10 10A10 10 0 0112 2zm1 9h3v2h-5V6h2z"/>
        </svg>
      </span>
      <span class="text-sm font-semibold text-slate-200">Total Hits</span>
    </div>
    <div id="meHits" class="k"><?= b($u_hits ?? 0) ?></div>
    <div class="sub">All time activity</div>
  </div>

  <div class="tile animate-delay-2">
    <div class="flex items-center gap-3">
      <span class="icon-pill">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
          <path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/>
        </svg>
      </span>
      <span class="text-sm font-semibold text-slate-200">Total Charge Cards</span>
    </div>
    <div id="meCharges" class="k"><?= b($u_charges ?? 0) ?></div>
    <div class="sub">Successful charges</div>
  </div>

  <div class="tile animate-delay-3">
    <div class="flex items-center gap-3">
      <span class="icon-pill">
         <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 13h3l2-6 4 12 2-6h5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </span>
      <span class="text-sm font-semibold text-slate-200">Total Live Cards</span>
    </div>
    <div id="meLives" class="k"><?= b($u_lives ?? 0) ?></div>
    <div class="sub">Active valid cards</div>
  </div>

  <div class="tile animate-delay-3">
    <div class="flex items-center gap-3">
      <span class="icon-pill">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M7 11h5V6h2v5h5v2h-5v5h-2v-5H7z"/></svg>
      </span>
      <span class="text-sm font-semibold text-slate-200">Expiry Date</span>
    </div>
    <div class="k" style="font-size:24px">
      <?= !empty($u_expiry) ? htmlspecialchars((new DateTime($u_expiry))->format('d/m/Y'), ENT_QUOTES) : '—' ?>
    </div>
    <div class="sub"><?= ($expDays!==null)? b(max(0,(int)$expDays)) : '0' ?> days remaining</div>
  </div>
</div>

<?php if(($u_status??'')==='admin'): ?>
<!-- GLOBAL STATISTICS (Admin Only) -->
<div class="gs-panel mt-8 animate-delay-4">
  <div class="gs-head">
    <div class="gs-chip">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M5 3h2v18H5V3zm6 6h2v12h-2V9zm6-4h2v16h-2V5z"/></svg>
    </div>
    <div>
      <div class="gs-title">Global Statistics</div>
      <div class="gs-sub">Platform-wide performance metrics (Visible to Admin only)</div>
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
<?php endif; ?>




<script>
(() => {
  const $ = s=>document.querySelector(s);
  const nf = n=>new Intl.NumberFormat().format(n|0);
  const btn   = $('#btnClaim');
  const msg   = $('#claimMsg');
  const chipS = $('#chipState');
  const chipR = $('#chipReset');
  const timer = $('#resetTimer');
  const meCredits = $('#meCredits');

  // Stats for Admin only
  async function fillAdminStats(){
    if(!$('#gTotalUsers')) return; // not admin or element missing
    try{
      const r = await fetch('/api/stats.php?ts='+Date.now(),{credentials:'same-origin', cache:'no-store'});
      const j = await r.json(); if(!j.ok) return;
      const map = { gTotalUsers:'total_users', gLiveCards:'live_cards', gChargeCards:'charge_cards', gTotalHits:'total_hits' };
      Object.entries(map).forEach(([id,key])=>{ const el=$('#'+id); if (el) el.textContent = nf(j.data[key]); });
    }catch(e){}
  }
  fillAdminStats(); setInterval(fillAdminStats, 10000);

  let nextReset = null;
  let tickTimer = null;

  function setState({claimed, credits, next_reset}){
    if (claimed) {
      chipS.innerHTML = 'Status: <b>Claimed</b> ✅';
      msg.textContent = 'You have already claimed today. Come back after reset.';
      btn.disabled = true;
      chipR.classList.remove('hidden');
    } else {
      chipS.innerHTML = 'Status: <b>Available</b> ✨';
      msg.textContent = 'Tap the button to add +50 credits to your balance.';
      btn.disabled = false;
      chipR.classList.remove('hidden');
    }
    if (typeof credits === 'number' && meCredits) meCredits.textContent = nf(credits);

    nextReset = next_reset || null;
    startCountdown();
  }

  function startCountdown(){
    if (!nextReset) return;
    if (tickTimer) clearInterval(tickTimer);

    function render(){
      const end = new Date(nextReset).getTime();
      const now = Date.now();
      const diff = Math.max(0, Math.floor((end - now)/1000));
      const h = String(Math.floor(diff/3600)).padStart(2,'0');
      const m = String(Math.floor((diff%3600)/60)).padStart(2,'0');
      const s = String(diff%60).padStart(2,'0');
      if (timer) timer.textContent = `${h}:${m}:${s}`;
      if (diff===0) loadState();
    }
    render();
    tickTimer = setInterval(render, 1000);
  }

  async function loadState(){
    try{
      const r = await fetch('/api/claim_daily.php?fn=state',{credentials:'same-origin'});
      const j = await r.json();
      if(!j.ok) throw new Error(j.error||'Failed');
      setState(j);
    }catch(e){
      chipS.innerHTML = 'Status: <b>Error</b>';
      msg.textContent = 'Could not fetch claim state.';
      btn.disabled = true;
    }
  }

  async function doClaim(){
    btn.disabled = true;
    btn.textContent = 'Claiming…';
    try{
      const r = await fetch('/api/claim_daily.php',{method:'POST',credentials:'same-origin'});
      const j = await r.json();
      if(!j.ok){
        if (j.error === 'BANNED') {
          msg.textContent = j.message || "You're banned from BabaChecker";
        } else if (j.error === 'ALREADY') {
          msg.textContent = 'Already claimed for today.';
        } else {
          msg.textContent = 'Failed to claim.';
        }
        setState(j);
        return;
      }
      msg.textContent = `Claim successful! +${j.amount} credits added.`;
      if (meCredits) meCredits.textContent = nf(j.credits);
      setState(j);
    }catch(_){
      msg.textContent = 'Network error. Try again.';
      btn.disabled = false;
      btn.textContent = 'Claim now';
      return;
    }finally{
      btn.textContent = 'Claim now';
    }
  }

  btn?.addEventListener('click', doClaim);
  loadState();
})();
</script>
