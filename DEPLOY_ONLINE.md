# Ethnix — Online Deployment Guide

## Railway vs Vercel: Recommendation

| Feature | **Railway** ✅ | Vercel |
|---------|----------------|--------|
| PHP runtime | Full PHP 8.2, always-on | Serverless (cold starts) |
| MySQL | Native, same project | External DB required |
| Sessions | Persistent | Stateless, unreliable |
| File storage | Volumes supported | External storage needed |
| Build | Docker / Nixpacks | Community PHP runtime |
| Cost | Usage-based, free trial | Free tier, usage limits |

**Recommendation: Use Railway** — Ethnix is built for a traditional PHP stack with MySQL, sessions, and file storage. Railway provides native MySQL, persistent storage, and a full PHP runtime. Vercel’s serverless PHP is not a good fit for this application.

---

# Railway Deployment — Step-by-Step

## Prerequisites

- [GitHub](https://github.com) account
- [Railway](https://railway.app) account (free trial)
- [Telegram Bot](https://t.me/BotFather) for login (see [TELEGRAM_SETUP.md](TELEGRAM_SETUP.md))

---

## Step 1: Push Code to GitHub

1. Create a new repository on GitHub (e.g. `ethnix-checker`).
2. Push your code:

   ```powershell
   cd c:\Users\shree\OneDrive\Documents\Ethnix
   git init
   git add .
   git commit -m "Initial Ethnix deployment"
   git branch -M main
   git remote add origin https://github.com/YOUR_USERNAME/ethnix-checker.git
   git push -u origin main
   ```

3. Ensure `.env` is in `.gitignore` — do not commit secrets.

---

## Step 2: Create Railway Project

1. Go to [railway.app](https://railway.app) → **Login** (GitHub).
2. Click **New Project**.
3. Choose **Deploy from GitHub repo**.
4. Select your repository.
5. Railway will detect the repo and create a service.

---

## Step 3: Add MySQL Database

1. In the project, click **+ New**.
2. Choose **Database** → **Add MySQL**.
3. Wait for the MySQL service to be ready.
4. Railway exposes MySQL variables (e.g. `MYSQLHOST`, `MYSQLURL`) automatically.

---

## Step 4: Configure Environment Variables

1. Open your app service (not MySQL).
2. Go to **Variables**.
3. Click **RAW Editor** or **New Variable**.
4. Add the variables below.

**Database (replace `MySQL` with your MySQL service name if different):**

| Variable | Value |
|----------|-------|
| `DB_HOST` | `${{MySQL.MYSQLHOST}}` |
| `DB_PORT` | `${{MySQL.MYSQLPORT}}` |
| `DB_NAME` | `${{MySQL.MYSQLDATABASE}}` |
| `DB_USER` | `${{MySQL.MYSQLUSER}}` |
| `DB_PASS` | `${{MySQL.MYSQLPASSWORD}}` |

> **Tip:** In Variables, use the autocomplete dropdown to reference the MySQL service. Railway shows available variables as you type `${{`.

**Application:**

| Variable | Value |
|----------|-------|
| `APP_ENV` | `production` |
| `APP_HOST` | `${{RAILWAY_PUBLIC_DOMAIN}}` |
| `SESSION_COOKIE_DOMAIN` | *(empty)* |
| `SESSION_SAMESITE` | `Lax` |
| `SESSION_NAME` | `ETHNIXSESSID` |

**Telegram (required for login):**

| Variable | Value |
|----------|-------|
| `TELEGRAM_BOT_TOKEN` | Your bot token from @BotFather |
| `TELEGRAM_BOT_USERNAME` | Your bot username (e.g. `EthnixRobot`) |
| `TELEGRAM_ANNOUNCE_CHAT_ID` | Your group/channel ID (e.g. `-1002552641928`) |
| `TELEGRAM_REQUIRE_ALLOWLIST` | `false` |
| `TELEGRAM_ALLOWED_IDS` | *(optional)* Comma-separated user IDs |

5. **Reference MySQL** if required:  
   In Variables, click **Variables** next to the MySQL service and reference it.

6. Click **Deploy** to trigger a redeploy.

---

## Step 5: Add Volumes (Recommended)

1. Open your app service.
2. Go to **Settings** → **Volumes**.
3. Click **+ New Volume**.
4. Add these mounts:

| Mount Path | Purpose |
|------------|---------|
| `/app/_sessions` | PHP session files |
| `/app/storage` | Logs and uploads |

---

## Step 6: Initialize Database

1. Open your app service → **Deployments**.
2. Click the latest deployment.
3. Open **Three dots** → **Settings** → **Deploy**.
4. Or use **Railway CLI**:

   ```powershell
   npm install -g @railway/cli
   railway login
   railway link
   railway run php setup_db.php
   ```

5. **Alternative:** Use the app’s built-in setup if available.
6. Or connect to MySQL via Railway’s **Data** tab and run `schema.sql`.

---

## Step 7: Generate Domain

1. Open your app service.
2. Go to **Settings** → **Networking**.
3. Under **Public Networking**, click **Generate Domain**.
4. You’ll get a URL like `https://ethnix-production.up.railway.app`.

---

## Step 8: Configure Telegram (fix "Bot domain invalid")

1. In Telegram, open [@BotFather](https://t.me/BotFather).
2. Send `/setdomain`.
3. Select your bot.
4. Enter your Railway domain **exactly**: `ethnix-production.up.railway.app` (no `https://`).
5. BotFather will confirm the domain is set.

> **Test Login:** Before configuring Telegram, you can use **Login as Admin (Test)** on the login page to access the app. Set `ENABLE_TEST_LOGIN=true` in Variables (enabled by default). Disable it when Telegram is configured.

---

## Step 9: Verify Deployment

1. **Health check:**  
   `https://your-app.up.railway.app/health`

   Expected response:

   ```json
   {
     "status": "ok",
     "timestamp": 1738435200,
     "php_version": "8.2.x",
     "server": "PHP Built-in Server"
   }
   ```

2. **Login:**  
   Visit your app URL and sign in with Telegram.

3. **Database:**  
   Confirm users and data are created and persisted.

---

## Step 10: Custom Domain (Optional)

1. Add your domain (e.g. `ethnix.net`):
   - Service → **Settings** → **Domains**.
   - **+ Custom Domain**.
   - Enter `ethnix.net`.

2. Add DNS records:

   | Type | Name | Value |
   |------|------|-------|
   | CNAME | `www` | `your-app.up.railway.app` |
   | A | `@` | Railway’s IP (if shown) |

3. **APP_HOST:**  
   Update `APP_HOST` to `ethnix.net` in Variables.

4. **Telegram:**  
   Update `@BotFather` /setdomain to `ethnix.net`.

---

## Quick Reference

| Item | Value |
|------|-------|
| App URL | Railway-generated or custom domain |
| Health | `/health` |
| Login | Telegram OAuth |
| Database | MySQL via Railway |

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| **Bot domain invalid** | In @BotFather: `/setdomain` → enter `ethnix-production.up.railway.app` (no https). Or use **Login as Admin (Test)** meanwhile. |
| Connection closes | Check env vars and DB config |
| Session not working | `SESSION_COOKIE_DOMAIN` empty; `SESSION_SAMESITE=Lax` |
| DB connection fails | Ensure MySQL service is running and variables are correct |
| 404 on routes | Use `router.php` as entry point |
| Logs not visible | Check **Deployments** → **View Logs** |

---

## Environment Summary

```env
# Database (reference MySQL service)
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_NAME=${{MySQL.MYSQLDATABASE}}
DB_USER=${{MySQL.MYSQLUSER}}
DB_PASS=${{MySQL.MYSQLPASSWORD}}

# App
APP_ENV=production
APP_HOST=${{RAILWAY_PUBLIC_DOMAIN}}
SESSION_COOKIE_DOMAIN=
SESSION_SAMESITE=Lax

# Telegram
TELEGRAM_BOT_TOKEN=your-token
TELEGRAM_BOT_USERNAME=EthnixRobot
TELEGRAM_ANNOUNCE_CHAT_ID=-1002552641928
TELEGRAM_REQUIRE_ALLOWLIST=false

# Testing: allows "Login as Admin (Test)" when Telegram domain not yet set (default: true)
ENABLE_TEST_LOGIN=true
```

---

## Post-Deployment

1. Import schema: `schema.sql` or `php setup_db.php`.
2. Test Telegram login.
3. Add custom domain (e.g. `ethnix.net`).
4. Set up monitoring and backups.
5. Review and adjust Railway usage limits.
