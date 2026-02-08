# Railway Environment Variables Configuration

## Required Environment Variables

Set these in your Railway project settings:

### Database Configuration
```bash
DB_HOST=gondola.proxy.rlwy.net
DB_NAME=railway
DB_USER=root
DB_PASS=HwrzpjTcHKJSJtBvdfCsxmdWFqGtURHH
DB_PORT=35602
```

### Telegram Configuration
```bash
TELEGRAM_BOT_TOKEN=8446922993:AAHKSq2QSmA8keUcL865Crjx8sFvgFz-FBI
TELEGRAM_BOT_USERNAME=Checker_deepubot
TELEGRAM_CHAT_ID=your-chat-id
TELEGRAM_ANNOUNCE_CHAT_ID=-1002552641928
TELEGRAM_REQUIRE_ALLOWLIST=false
```

### Application Configuration
```bash
APP_ENV=production
APP_HOST=${{RAILWAY_PUBLIC_DOMAIN}}
SESSION_COOKIE_DOMAIN=
SESSION_SAMESITE=Lax
```

### Important Notes:

1. **SESSION_COOKIE_DOMAIN** should be empty or set to your Railway domain
2. **APP_HOST** uses Railway's built-in variable for the public domain
3. **DB_HOST** will be provided by Railway when you add a MySQL database

## Railway-Specific Variables

Railway automatically provides these variables:
- `PORT` - The port your application should listen on
- `RAILWAY_PUBLIC_DOMAIN` - Your app's public domain
- `RAILWAY_ENVIRONMENT` - The environment name

## Health Check

Railway will check `/health` endpoint to verify your app is running.
