# Telegram Login Setup Guide


## Problem: "Username invalid" Error

This happens when Telegram OAuth is not properly configured. Here's how to fix it:

---

## Step 1: Create a Telegram Bot

1. Open Telegram and search for **@BotFather**
2. Send `/newbot` command
3. Follow the prompts:
   - **Bot name**: `BabaChecker Bot` (or any name)
   - **Bot username**: Must end with "bot" (e.g., `babachecker_bot`)
4. **Copy the Bot Token** - looks like: `123456789:ABCdefGHIjklMNOpqrsTUVwxyz`

---

## Step 2: Enable Telegram Login Widget

1. Still in BotFather, send: `/setdomain`
2. Select your bot
3. Enter your domain: `babachecker.com`

4. BotFather will confirm the domain is set

---

## Step 3: Configure Environment Variables

### For Local Development (.env file):

```env
TELEGRAM_BOT_TOKEN=123456789:ABCdefGHIjklMNOpqrsTUVwxyz
TELEGRAM_BOT_USERNAME=babachecker_bot
TELEGRAM_ADMIN_USERNAME=your_telegram_username
TELEGRAM_ANNOUNCE_CHAT_ID=-1001234567890
TELEGRAM_ALLOWED_IDS=123456789,987654321
```

### For Railway Deployment:

Go to Railway → Your App → **Variables** tab, add:


```
TELEGRAM_BOT_TOKEN=123456789:ABCdefGHIjklMNOpqrsTUVwxyz
TELEGRAM_BOT_USERNAME=babachecker_bot
TELEGRAM_ADMIN_USERNAME=your_telegram_username
TELEGRAM_ANNOUNCE_CHAT_ID=-1001234567890
TELEGRAM_ALLOWED_IDS=123456789,987654321
```

---

## Step 4: Get Your Telegram User ID

### Method 1: Use @userinfobot
1. Search for **@userinfobot** in Telegram
2. Send `/start`
3. Bot will reply with your User ID (e.g., `123456789`)

### Method 2: Use @raw_data_bot
1. Search for **@raw_data_bot**
2. Send any message
3. Look for `"id": 123456789` in the response

---

## Step 5: Get Channel/Group Chat ID (for announcements)

1. Add your bot to the channel/group
2. Make the bot an **admin**
3. Send a message in the channel
4. Visit: `https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates`
5. Look for `"chat":{"id":-1001234567890}`
6. Copy the negative number (e.g., `-1001234567890`)

---

## Step 6: Update Admin Panel Settings

After deployment:

1. Login to your admin panel
2. Go to **System** tab
3. Fill in Telegram settings:
   - **Bot Username**: `babachecker_bot` (without @)
   - **Bot Token**: Your bot token
   - **Admin Username**: Your Telegram username (without @)
   - **Announce Channel ID**: `-1001234567890`
   - **Allowed Admin IDs**: `123456789,987654321` (comma-separated)
4. Click **Save System Settings**

---

## Step 7: Test Telegram Login

1. Go to your website login page
2. Click **"Login with Telegram"** button
3. Telegram widget should appear
4. Click to authorize
5. You should be logged in!

---

## 🐛 Troubleshooting


### "Username invalid" Error
- **Cause**: Bot token or domain not configured
- **Fix**: Complete Steps 1-3 above

### "Bot domain invalid"
- **Cause**: Domain not set in BotFather
- **Fix**: Run `/setdomain` in BotFather

### Telegram button doesn't appear
- **Cause**: Missing `TELEGRAM_BOT_USERNAME` in environment
- **Fix**: Add the variable in Railway/`.env`

### "Unauthorized" error
- **Cause**: Wrong bot token
- **Fix**: Double-check token from BotFather

### Can't send messages to channel
- **Cause**: Bot is not admin in channel
- **Fix**: Make bot admin with "Post Messages" permission

---

## 📋 Quick Checklist


- [ ] Created bot with @BotFather
- [ ] Got bot token
- [ ] Set domain with `/setdomain`
- [ ] Got your Telegram user ID
- [ ] Got channel chat ID (if using announcements)
- [ ] Added all variables to Railway/`.env`
- [ ] Configured settings in Admin Panel
- [ ] Tested Telegram login

---

## 🔐 Security Notes


- **Never share your bot token** publicly
- **Store it only in environment variables**
- **Revoke and regenerate** if exposed
- **Use HTTPS** for Telegram login (Railway provides this)

---

## Example Configuration

```env
# Your actual values will be different
TELEGRAM_BOT_TOKEN=6123456789:AAHdqTcvCH1vGWJxfSeofSAs0K5PALDsaw
TELEGRAM_BOT_USERNAME=babachecker_bot
TELEGRAM_ADMIN_USERNAME=dipendr98
TELEGRAM_ANNOUNCE_CHAT_ID=-1002890276135
TELEGRAM_ALLOWED_IDS=123456789
```

