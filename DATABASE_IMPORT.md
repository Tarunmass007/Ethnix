# Database Setup Guide for Railway

You have **3 options** to set up your database on Railway:

---

## Option 1: Use Railway's Web Interface (Easiest)

### Step 1: Access Railway MySQL
1. Go to your Railway project
2. Click on the **MySQL service**
3. Click **"Data"** tab
4. You'll see a query interface

### Step 2: Run the Schema
1. Copy the contents of `schema.sql`
2. Paste into the query box
3. Click **"Run"**
4. Tables will be created!

---

## Option 2: Use Railway CLI (Recommended)

### Step 1: Install Railway CLI
```bash
npm i -g @railway/cli
```

### Step 2: Login and Connect
```bash
# Login to Railway
railway login

# Link to your project
railway link

# Connect to MySQL
railway connect MySQL
```

### Step 3: Run Schema
Once connected to MySQL shell:
```sql
SOURCE /path/to/schema.sql;
```

Or copy-paste the schema.sql contents directly.

---

## Option 3: Run setup_db.php on Railway

### Step 1: Make it Accessible
Create a temporary route to run setup:

1. Visit: `https://your-app.up.railway.app/setup_db.php`
2. It will create all tables automatically
3. **Delete or rename the file after** for security

### Step 2: Secure It (Optional)
Add this at the top of `setup_db.php`:
```php
// Only allow in production setup
if (($_ENV['ALLOW_SETUP'] ?? 'false') !== 'true') {
    die('Setup disabled. Set ALLOW_SETUP=true to run.');
}
```

Then set `ALLOW_SETUP=true` in Railway variables, run setup, then remove it.

---

## Verify Database Setup

### Check Tables Created
```sql
SHOW TABLES;
```

Should show:
- `users`
- `user_proxies`
- `settings`
- `redeemcodes`

### Check Admin User
```sql
SELECT * FROM users WHERE username='admin';
```

Should return the admin user with 99999 credits.

---

## Quick Setup Commands

If using Railway CLI:
```bash
# Connect to MySQL
railway connect MySQL

# Once in MySQL shell, run:
SHOW TABLES;
SELECT * FROM users;
```

---

## Files Created

- ✅ `schema.sql` - SQL schema file (ready to import)
- ✅ `setup_db.php` - Updated to work with Railway env vars

Choose the option that works best for you!
