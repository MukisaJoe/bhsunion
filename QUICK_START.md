# Quick Start: Deploy to InfinityFree (5 Minutes)

## Fast Deployment Steps

### 1. Get Database Credentials
- Go to InfinityFree cPanel → MySQL Databases
- Create database: `bhs_union`
- Create user and grant privileges
- **Note down**: DB_HOST, DB_NAME, DB_USER, DB_PASS

### 2. Import Database
- cPanel → phpMyAdmin → Select your database
- Click "Import" → Choose `database.sql` → Go

### 3. Upload Files
- cPanel → File Manager → Navigate to `public_html/`
- Create folder `api/`
- Upload ALL files from `Hosting/api/` to `public_html/api/`

### 4. Configure
- Edit `api/config/config.php`
- Update:
  ```php
  const DB_HOST = 'localhost';  // or sqlXXX.infinityfree.com
  const DB_NAME = 'yourusername_bhs_union';
  const DB_USER = 'yourusername_bhs_user';
  const DB_PASS = 'your_password';
  const ALLOWED_ORIGINS = 'https://yourdomain.infinityfreeapp.com';
  ```

### 5. Create Admin
- phpMyAdmin → SQL tab → Run:
```sql
INSERT INTO users (email, password_hash, name, role, status)
VALUES (
  'admin@bhs.local',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- Generate new hash!
  'Admin',
  'admin',
  'active'
);
```

**Generate password hash**: Create temp file `hash.php`:
```php
<?php echo password_hash('YourPassword', PASSWORD_DEFAULT); ?>
```
Access via browser, copy hash, delete file.

### 6. Test
Visit: `https://yourdomain.infinityfreeapp.com/api/`
Should see API info.

### 7. Update Flutter App
Change API URL to: `https://yourdomain.infinityfreeapp.com/api`

**Done!** 🎉

For detailed steps, see `INFINITYFREE_SETUP_GUIDE.md`

