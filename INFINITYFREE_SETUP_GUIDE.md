# Complete Guide: Hosting Bhs Union Backend on InfinityFree

This guide will walk you through deploying your PHP/MySQL backend API on InfinityFree hosting.

## 📋 Prerequisites

1. An InfinityFree account (free at [infinityfree.net](https://infinityfree.net))
2. Your backend code ready in `/home/s9/Bhs/Hosting/`
3. Access to InfinityFree control panel (cPanel or DirectAdmin)

---

## 🚀 Step-by-Step Setup

### Step 1: Sign Up for InfinityFree

1. Go to [https://infinityfree.net](https://infinityfree.net)
2. Click "Sign Up Free"
3. Complete the registration process
4. Verify your email address

---

### Step 2: Create a Website/Subdomain

1. Log into your InfinityFree account
2. Go to **"Create Account"** or **"Add Website"**
3. Choose one of these options:
   - **Free Subdomain**: e.g., `yoursite.infinityfreeapp.com`
   - **Use Your Domain**: If you have a custom domain (recommended)
4. Note your assigned domain/subdomain (e.g., `bhsunion.infinityfreeapp.com`)

---

### Step 3: Access Control Panel

1. In InfinityFree dashboard, find your website
2. Click **"Login"** or **"Manage"** to access the control panel
3. You'll be redirected to either **cPanel** or **DirectAdmin**

---

### Step 4: Create MySQL Database

#### Option A: Using cPanel

1. In cPanel, find **"MySQL Databases"** section
2. Scroll down to **"Create New Database"**
   - Enter database name: `bhs_union` (or any name you prefer)
   - Click **"Create Database"**
3. Scroll to **"MySQL Users"** section
   - Enter username: `bhs_user` (or any name you prefer)
   - Enter password: **Generate a strong password** (use the password generator)
   - Click **"Create User"**
4. Scroll to **"Add User to Database"** section
   - Select your user from dropdown
   - Select your database from dropdown
   - Click **"Add"**
5. Check **"ALL PRIVILEGES"** checkbox
   - Click **"Make Changes"**
6. **IMPORTANT**: Note down these details:
   - Database Name: `yourusername_bhs_union` (usually prefixed with your username)
   - Database User: `yourusername_bhs_user` (usually prefixed)
   - Database Password: (the one you created)
   - Database Host: Usually `localhost` or `sqlXXX.infinityfree.com` (check in cPanel)

#### Option B: Using DirectAdmin

1. Click **"MySQL Management"** in DirectAdmin
2. Click **"Create Database"**
   - Enter database name
   - Enter username and password
   - Click **"Create"**
3. Note down database credentials shown

---

### Step 5: Import Database Schema

1. In cPanel/DirectAdmin, find **"phpMyAdmin"**
2. Click to open phpMyAdmin
3. Select your database from the left sidebar
4. Click **"Import"** tab at the top
5. Click **"Choose File"** and select `/home/s9/Bhs/Hosting/database.sql`
6. Click **"Go"** or **"Import"** button
7. Wait for import to complete - you should see "Import has been successfully finished"

---

### Step 6: Upload API Files

#### Option A: Using File Manager

1. In cPanel/DirectAdmin, find **"File Manager"**
2. Navigate to:
   - **cPanel**: `public_html/` folder
   - **DirectAdmin**: `domains/yourdomain.com/public_html/` folder
3. Create a new folder called `api` (if it doesn't exist)
4. Upload all files from `/home/s9/Bhs/Hosting/api/` to the `api/` folder
   - Select all files in `/home/s9/Bhs/Hosting/api/`
   - Upload to `public_html/api/`

#### Option B: Using FTP (Recommended)

1. In cPanel, find **"FTP Accounts"** or use FileZilla
2. Get your FTP credentials:
   - FTP Host: Usually `ftpupload.net` or check in cPanel
   - FTP Username: (your InfinityFree username)
   - FTP Password: (your account password)
   - Port: Usually `21` or `21`
3. Connect using FileZilla or any FTP client
4. Navigate to `/htdocs/api/` or `/public_html/api/`
5. Upload all files from `/home/s9/Bhs/Hosting/api/` folder
   - Make sure to preserve folder structure

---

### Step 7: Configure Database Connection

1. In File Manager or FTP, navigate to `api/config/` folder
2. Edit `config.php` file
3. Update these values with your database credentials:

```php
<?php

declare(strict_types=1);

// Update these values with your InfinityFree MySQL database credentials
const DB_HOST = 'localhost';  // Usually 'localhost' or 'sqlXXX.infinityfree.com'
const DB_NAME = 'yourusername_bhs_union';  // Your actual database name
const DB_USER = 'yourusername_bhs_user';   // Your actual database user
const DB_PASS = 'your_actual_password';    // Your actual database password

// Token settings (keep default)
const TOKEN_TTL_HOURS = 720; // 30 days

// Rate limiting (keep default)
const RATE_LIMIT_MAX = 120; // requests
const RATE_LIMIT_WINDOW_SECONDS = 60; // per minute

// CORS - Update with your domain
const ALLOWED_ORIGINS = 'https://yourdomain.infinityfreeapp.com';
// Or if using custom domain:
// const ALLOWED_ORIGINS = 'https://yourdomain.com';
```

**Important Notes:**
- Replace `yourusername_` with your actual InfinityFree username prefix
- Database host is usually `localhost` but check in cPanel if unsure
- For production, change `ALLOWED_ORIGINS` to your actual domain

---

### Step 8: Set File Permissions (if needed)

Some files might need write permissions:

1. In File Manager, navigate to `api/` folder
2. Right-click on `api/` folder → **"Change Permissions"**
3. Set to `755` for folders
4. Set to `644` for files
5. If you have any log or cache folders, set them to `777` (write permissions)

---

### Step 9: Create First Admin User

You need to create an admin user in the database. Here's how:

#### Method 1: Using phpMyAdmin (Easiest)

1. Open **phpMyAdmin** in your control panel
2. Select your database
3. Click on **"SQL"** tab
4. Run this SQL (replace values):

```sql
-- First, generate a password hash using this PHP code
-- You can use a temporary PHP file to generate it, or use online tool

-- Example: For password "Bhs2016"
-- Generate hash using: https://www.php.net/manual/en/function.password-hash.php
-- Or create a temp PHP file and run: <?php echo password_hash('Bhs2016', PASSWORD_DEFAULT); ?>

INSERT INTO users (email, password_hash, name, role, status, phone, created_at, updated_at)
VALUES (
  'admin@bhs.local',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- Replace with generated hash
  'Admin',
  'admin',
  'active',
  '1234567890',
  NOW(),
  NOW()
);
```

#### Method 2: Generate Password Hash

1. Create a temporary PHP file `generate_hash.php` in your `api/` folder:

```php
<?php
echo password_hash('YourAdminPassword', PASSWORD_DEFAULT);
?>
```

2. Access it via browser: `https://yourdomain.infinityfreeapp.com/api/generate_hash.php`
3. Copy the generated hash
4. Delete the file after use
5. Use the hash in the SQL INSERT statement above

---

### Step 10: Test Your API

1. Test the base endpoint:
   ```
   https://yourdomain.infinityfreeapp.com/api/
   ```
   Should return a JSON response with API information

2. Test login endpoint:
   ```bash
   curl -X POST https://yourdomain.infinityfreeapp.com/api/auth/login \
     -H "Content-Type: application/json" \
     -d '{"email":"admin@bhs.local","password":"YourPassword"}'
   ```

3. Or use a tool like Postman to test endpoints

---

### Step 11: Configure Flutter App

Update your Flutter app's API base URL:

1. Find where API base URL is configured in your Flutter app
2. Update to: `https://yourdomain.infinityfreeapp.com/api`
3. For example, if you have a config file:
   ```dart
   const String API_BASE_URL = 'https://yourdomain.infinityfreeapp.com/api';
   ```

---

## 🔧 Additional Configuration

### Enable .htaccess (if not working)

1. Some InfinityFree servers require `.htaccess` to be enabled
2. Your `api/.htaccess` file should already be there
3. If URLs aren't working, check that mod_rewrite is enabled:
   - Contact InfinityFree support if needed

### SSL/HTTPS

1. InfinityFree provides free SSL certificates
2. In cPanel, find **"SSL/TLS Status"**
3. Enable SSL for your domain
4. Your API will be accessible via HTTPS automatically

### WebSocket Support

⚠️ **Important**: InfinityFree's free hosting typically **does NOT support WebSocket connections**.

For WebSocket functionality, you have these options:
1. Use a separate WebSocket service (like Cloudflare Workers, Pusher, or Ably)
2. Use polling instead of WebSocket in your Flutter app
3. Upgrade to a hosting service that supports WebSockets (like Heroku, Railway, or a VPS)

---

## 📱 Testing Checklist

- [ ] Database connection works
- [ ] API endpoints are accessible
- [ ] Login endpoint works
- [ ] Admin can log in
- [ ] Member endpoints work
- [ ] CORS headers are correct
- [ ] SSL certificate is active (HTTPS works)
- [ ] File uploads work (if applicable)

---

## 🐛 Troubleshooting

### Issue: "Database connection failed"
- **Solution**: Double-check database credentials in `config.php`
- Ensure database user has all privileges
- Check database host (might not be `localhost`)

### Issue: "404 Not Found" on API endpoints
- **Solution**: 
  - Check `.htaccess` file exists in `api/` folder
  - Verify mod_rewrite is enabled
  - Check file permissions (755 for folders, 644 for files)

### Issue: "CORS error" in Flutter app
- **Solution**: Update `ALLOWED_ORIGINS` in `config.php` to match your app's origin
- Or set to `'*'` for development (not recommended for production)

### Issue: "500 Internal Server Error"
- **Solution**:
  - Check PHP error logs in cPanel
  - Verify all PHP files have correct syntax
  - Check file permissions
  - Ensure PHP version is 7.4+ (check in cPanel)

### Issue: "Permission denied" errors
- **Solution**: 
  - Check file/folder permissions
  - Folders should be 755
  - Files should be 644
  - Log files (if any) should be 666 or 777

---

## 📚 API Endpoints Reference

Your API will be available at: `https://yourdomain.infinityfreeapp.com/api/`

### Authentication
- `POST /api/auth/login` - Login
- `GET /api/auth/me` - Get current user
- `POST /api/auth/logout` - Logout

### Members
- `GET /api/member/profile` - Get member profile
- `PUT /api/member/profile` - Update profile
- `POST /api/member/contributions` - Submit contribution
- `GET /api/member/contributions` - Get contributions

### Admin
- `GET /api/admin/members` - List members
- `POST /api/admin/members` - Create member
- `POST /api/admin/contributions/{id}/confirm` - Confirm contribution
- `POST /api/admin/withdrawals` - Create withdrawal
- `GET /api/admin/audit` - Get audit logs

### Announcements & Chat
- `GET /api/announcements` - Get announcements
- `POST /api/admin/announcements` - Create announcement
- `GET /api/chat` - Get chat messages
- `POST /api/chat` - Send message

---

## 🔒 Security Best Practices

1. **Change default admin password** immediately after setup
2. **Update CORS settings** to only allow your Flutter app's domain
3. **Use HTTPS only** - InfinityFree provides free SSL
4. **Regular backups** - Download database backups regularly
5. **Keep PHP updated** - Check PHP version in cPanel
6. **Monitor error logs** - Check cPanel error logs regularly

---

## 📞 Support

- **InfinityFree Support**: [https://forum.infinityfree.net](https://forum.infinityfree.net)
- **cPanel Documentation**: [https://docs.cpanel.net](https://docs.cpanel.net)
- **PHP Documentation**: [https://www.php.net/docs.php](https://www.php.net/docs.php)

---

## ✅ Final Checklist

Before going live:
- [ ] Database imported successfully
- [ ] API files uploaded correctly
- [ ] Database credentials updated in `config.php`
- [ ] Admin user created
- [ ] API endpoints tested
- [ ] SSL certificate enabled
- [ ] CORS configured correctly
- [ ] Flutter app updated with new API URL
- [ ] Test login from Flutter app
- [ ] Test all major features

---

**Your API base URL will be:**
```
https://yourdomain.infinityfreeapp.com/api
```

Replace `yourdomain.infinityfreeapp.com` with your actual InfinityFree domain/subdomain!

