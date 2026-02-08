# Ethnix — Local Deployment

Run Ethnix locally with Docker or PHP + MySQL.

---

## Option 1: Docker Compose (Recommended)

### Prerequisites
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Windows/Mac)

### Steps

1. **Start services**
   ```powershell
   cd c:\Users\shree\OneDrive\Documents\Ethnix
   docker compose up -d
   ```

2. **Initialize database**
   ```powershell
   docker compose exec app php setup_db.php
   ```

3. **Open in browser**
   - App: http://localhost:8080
   - Health: http://localhost:8080/health

4. **Login (local dev)**
   - Click **Login as Admin (Dev)** on the login page, or
   - Visit: http://localhost:8080/dev_login.php?user=admin&key=baba_secret_123

### Stop
```powershell
docker compose down
```

---

## Option 2: PHP + Local MySQL

### Prerequisites
- PHP 8.1+ with extensions: pdo_mysql, mbstring, curl, gd, zip
- MySQL 8.0+ (XAMPP, WAMP, or standalone)

### Steps

1. **Create database**
   ```sql
   CREATE DATABASE ethnix CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Configure `.env`**
   ```powershell
   copy .env.local.example .env
   ```
   Edit `.env`:
   ```env
   APP_ENV=local
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=ethnix
   DB_USER=root
   DB_PASS=your_mysql_password
   ```

3. **Install dependencies**
   ```powershell
   composer install
   ```

4. **Initialize database**
   ```powershell
   php setup_db.php
   ```

5. **Start server**
   ```powershell
   php -S localhost:8080 -t . router.php
   ```

6. **Open** http://localhost:8080 and click **Login as Admin (Dev)**

---

## Default Users (from setup_db.php)

| Username | Role   | Credits |
|----------|--------|---------|
| admin    | ADMIN  | 99999   |
| testuser | FREE   | 500     |

---

## Troubleshooting

**Database connection failed**
- Ensure MySQL is running
- Check DB_HOST, DB_USER, DB_PASS in `.env`
- For Docker: `DB_HOST=mysql` (service name)

**Login redirect loop**
- Ensure `APP_ENV=local` in `.env` for dev login button
- Use dev_login.php directly: `?user=admin&key=baba_secret_123`

**Port 8080 in use**
- Change port: `php -S localhost:3000 -t . router.php`
- Or in docker-compose: `ports: ["3000:8080"]`
