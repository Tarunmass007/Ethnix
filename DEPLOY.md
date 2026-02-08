# Railway Deployment - Quick Start

## ✅ What's Been Fixed


1. **Removed Docker** - No more Apache MPM errors
2. **Enhanced Router** - Better error handling and logging
3. **Health Check** - `/health` endpoint for Railway monitoring
4. **Session Cookies** - Fixed to work with Railway domains

---

## 🚀 Deploy to Railway


### Step 1: Push Your Code
```bash
git add .
git commit -m "Fix Railway deployment configuration"
git push
```

### Step 2: Create Railway Project
1. Go to [railway.app](https://railway.app)
2. Click **"New Project"**
3. Select **"Deploy from GitHub repo"**
4. Choose your repository
5. Railway will auto-detect `nixpacks.toml` and deploy

### Step 3: Add MySQL Database
1. In your Railway project, click **"+ New"**
2. Select **"Database"** → **"Add MySQL"**
3. Railway will create a database and provide connection details

### Step 4: Set Environment Variables
Click on your service → **"Variables"** → Add these:


```bash
# Database (copy from Railway MySQL service)
DB_HOST=${{MySQL.MYSQL_HOST}}
DB_NAME=${{MySQL.MYSQL_DATABASE}}
DB_USER=${{MySQL.MYSQL_USER}}
DB_PASS=${{MySQL.MYSQL_PASSWORD}}

# Telegram
TELEGRAM_BOT_TOKEN=your-bot-token-here
TELEGRAM_BOT_USERNAME=YourBotUsername
TELEGRAM_CHAT_ID=your-chat-id
TELEGRAM_ANNOUNCE_CHAT_ID=-1002552641928
TELEGRAM_REQUIRE_ALLOWLIST=false

# App Config
APP_ENV=production
SESSION_COOKIE_DOMAIN=
SESSION_SAMESITE=Lax
```

### Step 5: Add Volume for Uploads
1. In your service, go to **"Settings"** → **"Volumes"**

2. Click **"+ New Volume"**
3. Set **Mount Path**: `/app/uploads`
4. Click **"Add"**

### Step 6: Check Deployment
1. Click **"Deployments"** tab
2. Watch the build logs
3. Once deployed, click the generated URL
4. Test `/health` endpoint first: `https://your-app.up.railway.app/health`

---

## 🔍 Troubleshooting

### Check Logs
In Railway dashboard → Your service → **"Deployments"** → Click latest deployment → **"View Logs"**


Look for:
- `[ROUTER]` logs showing requests
- Any PHP errors
- Database connection issues

### Common Issues

**1. Connection closes immediately**
- Check environment variables are set
- Verify database credentials
- Check `/health` endpoint works

**2. Session issues**
- Ensure `SESSION_COOKIE_DOMAIN` is empty or not set
- Check `SESSION_SAMESITE` is set to `Lax`

**3. Database connection fails**
- Verify MySQL service is running
- Check `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` variables
- Ensure database is in the same Railway project

**4. File uploads don't persist**
- Add a volume mounted at `/app/uploads`
- Check folder permissions

---

## 📊 Monitoring


### Health Check
```bash
curl https://your-app.up.railway.app/health
```

Should return:
```json
{
  "status": "ok",
  "timestamp": 1738435200,
  "php_version": "8.2.x",
  "server": "PHP Built-in Server"
}
```

### View Logs
Railway provides real-time logs. Look for:
- `[ROUTER] Request URI: /` - Router is working
- `[ROUTER] Routing to index.php` - Requests being routed
- `[ROUTER] Error: ...` - Any errors

---

## 🌐 Custom Domain

Once deployed and working:

1. Go to your service → **"Settings"** → **"Domains"**
2. Click **"+ Custom Domain"**
3. Enter `babachecker.com`
4. Add the DNS records shown by Railway
5. Update environment variable: `APP_HOST=babachecker.com`

---

## ✨ Next Steps


After successful deployment:
1. Import your database schema
2. Test Telegram login
3. Test file uploads
4. Configure custom domain
5. Set up SSL (automatic with Railway)

---

**The connection closing issue should now be resolved!** The enhanced router will log all requests and errors, making debugging much easier.

