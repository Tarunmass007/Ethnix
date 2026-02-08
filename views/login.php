<?php
/**
 * Login page - shown when user visits / and is not authenticated
 * Local dev: use dev_login.php?user=admin&key=baba_secret_123
 */
$isLocal = ($_ENV['APP_ENV'] ?? 'production') === 'local';
$devLoginUrl = '/dev_login.php?user=admin&key=baba_secret_123';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login • Ethnix</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
  <style>
    :root { --neon-primary: #39ff14; --neon-secondary: #0eff80; }
    body { font-family: 'Inter', system-ui, sans-serif; background: #050505; color: #e5e7eb; min-height: 100vh; }
    .glass { backdrop-filter: blur(12px); background: rgba(20, 20, 20, 0.6); border: 1px solid rgba(57, 255, 20, 0.3); }
  </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
  <div class="w-full max-w-md">
    <div class="rounded-2xl glass p-8 shadow-2xl">
      <div class="text-center mb-8">
        <img src="/assets/ethnix-logo.png" alt="Ethnix" class="h-16 w-auto mx-auto mb-4 object-contain" style="mix-blend-mode: screen; filter: drop-shadow(0 0 12px rgba(57, 255, 20, 0.5));">
        <h1 class="text-2xl font-bold text-white">Ethnix</h1>
        <p class="text-slate-400 text-sm mt-1">HQ Checker Platform</p>
      </div>

      <?php if (!empty($_GET['error'])): ?>
      <div class="mb-4 p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm">
        <?= htmlspecialchars($_GET['error'] === 'unauthorized' ? 'Authorization required. Please join our Telegram group.' : 'Login failed.', ENT_QUOTES) ?>
      </div>
      <?php endif; ?>

      <?php if ($isLocal): ?>
      <!-- Local Dev: Quick login -->
      <div class="space-y-3">
        <p class="text-slate-400 text-sm text-center">Local development mode</p>
        <a href="<?= htmlspecialchars($devLoginUrl, ENT_QUOTES) ?>" 
           class="block w-full py-3 px-4 rounded-xl font-semibold text-center transition-all"
           style="background: var(--neon-primary); color: #000; box-shadow: 0 0 20px rgba(57, 255, 20, 0.4);">
          Login as Admin (Dev)
        </a>
        <p class="text-xs text-slate-500 text-center">Uses dev_login.php with secret key</p>
      </div>
      <?php else: ?>
      <!-- Production: Telegram Login -->
      <script async src="https://telegram.org/js/telegram-widget.js?22" 
              data-telegram-login="<?= htmlspecialchars($_ENV['TELEGRAM_BOT_USERNAME'] ?? 'EthnixRobot', ENT_QUOTES) ?>" 
              data-size="large" 
              data-auth-url="/telegram_auth.php" 
              data-request-access="write"></script>
      <p class="text-slate-400 text-sm text-center mt-4">Sign in with your Telegram account</p>
      <?php endif; ?>

      <div class="mt-8 pt-6 border-t border-white/10 text-center text-xs text-slate-500">
        <p>ethnix.net • Checkers & Multi APIs</p>
      </div>
    </div>
  </div>
</body>
</html>
