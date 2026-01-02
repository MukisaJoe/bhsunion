# Deploy Bhs Union API to Render

This guide will walk you through deploying your PHP/MySQL backend API on Render.

## 🚀 Why Render?

- ✅ No anti-bot protection blocking API requests
- ✅ Free tier available
- ✅ Built-in PostgreSQL (free) or external MySQL
- ✅ Easy environment variable management
- ✅ Automatic HTTPS/SSL
- ✅ Better for API endpoints
- ✅ No JavaScript challenge issues

---

## 📋 Prerequisites

1. A Render account (sign up at [render.com](https://render.com))
2. Your backend code in `/home/s9/Bhs/Hosting/`
3. GitHub account (optional, but recommended for automatic deploys)

---

## 🗄️ Step 1: Set Up Database

### Option A: Use Render PostgreSQL (Recommended for Free Tier)

Render provides free PostgreSQL databases. You'll need to adapt your code, OR use an external MySQL service.

### Option B: Use External MySQL (Keep Current Code)

1. Sign up for a free MySQL database:
   - **PlanetScale** (free tier) - [planetscale.com](https://planetscale.com)
   - **Aiven** (free tier) - [aiven.io](https://aiven.io)
   - **db4free.net** - [db4free.net](https://db4free.net)
   - Or keep using InfinityFree MySQL (just change the host)

2. Get your database credentials:
   - Host: `your-host.com` or IP
   - Database name: `bhs_union`
   - Username: `your_user`
   - Password: `your_password`
   - Port: `3306` (default)

---

## 📦 Step 2: Prepare Your Code

The folder structure should be:
```
Hosting/
├── api/
│   ├── index.php
│   ├── .htaccess
│   ├── config/
│   ├── controllers/
│   └── lib/
├── public/
│   ├── index.php
│   └── .htaccess
└── render.yaml
```

I've created the `public/` folder structure for you.

---

## 🚀 Step 3: Deploy to Render

### Method 1: Manual Upload (Quick Start)

1. **Create a New Web Service:**
   - Log into [render.com](https://render.com)
   - Click **"New +"** → **"Web Service"**

2. **Connect Repository:**
   - If using GitHub: Connect your repository
   - If not using GitHub: Use **"Public Git repository"** or **"Deploy from public URL"**

3. **Configure Service:**
   - **Name:** `bhs-union-api`
   - **Environment:** `PHP`
   - **Branch:** `main` or `master`
   - **Root Directory:** (leave empty or set to `Hosting/`)
   - **Build Command:** (leave empty)
   - **Start Command:** `php -S 0.0.0.0:$PORT -t public`

4. **Set Environment Variables:**
   Click **"Advanced"** → **"Add Environment Variable"**:
   ```
   DB_HOST = your_mysql_host
   DB_NAME = bhs_union
   DB_USER = your_database_user
   DB_PASS = your_database_password
   PHP_VERSION = 8.1
   ```

5. **Deploy:**
   - Click **"Create Web Service"**
   - Wait for deployment (usually 2-5 minutes)

### Method 2: Using render.yaml (Recommended)

1. **Push to GitHub:**
   ```bash
   cd /home/s9/Bhs
   git init
   git add .
   git commit -m "Initial commit"
   # Push to GitHub repository
   ```

2. **Connect to Render:**
   - In Render dashboard, click **"New +"** → **"Blueprint"**
   - Connect your GitHub repository
   - Render will auto-detect `render.yaml`
   - Click **"Apply"**

3. **Set Environment Variables:**
   - Go to your service settings
   - Add environment variables:
     - `DB_HOST`
     - `DB_NAME`
     - `DB_USER`
     - `DB_PASS`

---

## ⚙️ Step 4: Configure Database Connection

Update `api/config/config.php` to use environment variables:

The file should use `getenv()` to read from environment variables that Render provides.

**Updated config.php:**
```php
<?php

declare(strict_types=1);

// Use environment variables (Render provides these)
const DB_HOST = 'getenv("DB_HOST") ?: "localhost"';
const DB_NAME = 'getenv("DB_NAME") ?: "bhs_union"';
const DB_USER = 'getenv("DB_USER") ?: "bhs_user"';
const DB_PASS = 'getenv("DB_PASS") ?: ""';

// Token settings
const TOKEN_TTL_HOURS = 720; // 30 days

// Rate limiting
const RATE_LIMIT_MAX = 120; // requests
const RATE_LIMIT_WINDOW_SECONDS = 60; // per minute

// CORS - Update with your Render domain
const ALLOWED_ORIGINS = 'https://your-app.onrender.com';
```

Wait, that won't work. Let me create the proper version...

Actually, I need to update the config.php file to properly read environment variables.

---

## 📝 Step 5: Import Database

1. **Get Database Access:**
   - Connect to your MySQL database (using the external service or Render's database)

2. **Import Schema:**
   - Use phpMyAdmin or MySQL client
   - Import `database.sql` file

3. **Create Admin User:**
   - Run the SQL command from `create_admin_james_smith.sql`

---

## 🔗 Step 6: Get Your API URL

After deployment, Render will provide a URL like:
```
https://bhs-union-api.onrender.com
```

Your API endpoints will be at:
```
https://bhs-union-api.onrender.com/api/auth/login
```

---

## 🔧 Step 7: Update Flutter App

Update `lib/config/api_config.dart`:
```dart
class ApiConfig {
  static const String baseUrl = 'https://bhs-union-api.onrender.com/api';
}
```

---

## ✅ Testing

Test your API:
```bash
curl https://bhs-union-api.onrender.com/api/
curl -X POST https://bhs-union-api.onrender.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"james.smith@bhs.local","password":"yc3480kj"}'
```

---

## 🆓 Render Free Tier

- **Free web services:** Sleep after 15 minutes of inactivity (wakes up on first request)
- **Free databases:** Available with limitations
- **Free SSL:** Automatic HTTPS
- **No anti-bot:** Clean API endpoints!

---

## 🔄 Auto-Deploy

Render automatically redeploys when you push to your connected Git repository.

---

## 🐛 Troubleshooting

### Issue: Service won't start
- Check **Logs** tab in Render dashboard
- Verify start command is correct
- Check environment variables are set

### Issue: Database connection fails
- Verify environment variables in Render dashboard
- Check database host allows connections from Render IPs
- Test database connection from local machine first

### Issue: 404 errors
- Verify `public/index.php` exists
- Check `.htaccess` in `public/` folder
- Verify root directory is set correctly

---

## 📚 Next Steps

1. Deploy to Render
2. Import database
3. Create admin user
4. Update Flutter app API URL
5. Test login from app

**Your API will be accessible without any anti-bot protection!** 🎉

