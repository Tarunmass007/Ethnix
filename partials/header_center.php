<header class="sticky top-0 z-10 bg-slate-950/80 backdrop-blur border-b border-white/10">
  <div class="px-4 lg:px-6 py-3 flex items-center justify-between">
    <a href="/app/dashboard" class="flex items-center gap-3 group">
      <div class="w-10 h-10 flex items-center justify-center rounded-xl overflow-hidden" style="background: transparent;">
        <img src="/assets/ethnix-logo.png" alt="Ethnix" class="h-9 w-auto object-contain" style="mix-blend-mode: screen; filter: drop-shadow(0 0 8px rgba(57, 255, 20, 0.5));">
      </div>
      <div class="font-semibold text-slate-200 group-hover:text-[var(--neon-primary)] transition-colors">Ethnix</div>
    </a>

    <button id="btnNotif" class="relative rounded-xl px-3 py-2 bg-white/5 hover:bg-white/10">
      <svg class="w-5 h-5 text-slate-200" viewBox="0 0 24 24" fill="currentColor"><path d="M12 22a2 2 0 0 0 2-2h-4a2 2 0 0 0 2 2zm6-6v-5a6 6 0 1 0-12 0v5l-2 2v1h16v-1l-2-2z"/></svg>
      <span id="notifDot" class="hidden absolute -top-1 -right-1 w-2 h-2 bg-rose-400 rounded-full"></span>
    </button>
  </div>

  <?php if ($showBanner): ?>
  <div class="px-4 lg:px-6 pb-3">
    <div class="rounded-2xl border border-amber-400/30 bg-gradient-to-r from-amber-500/10 to-rose-500/10 text-amber-200 px-4 py-3 flex items-start gap-3">
      <svg class="w-5 h-5 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
      <div class="text-sm leading-relaxed"><?= $bannerHtml ?></div>
    </div>
  </div>
  <?php endif; ?>
</header>
