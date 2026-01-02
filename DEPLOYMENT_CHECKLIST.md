# Deployment Checklist for InfinityFree

Use this checklist to ensure everything is set up correctly.

## Pre-Deployment

- [ ] InfinityFree account created
- [ ] Domain/subdomain assigned
- [ ] Backend code ready in `/home/s9/Bhs/Hosting/`
- [ ] All API files checked for completeness

## Database Setup

- [ ] MySQL database created in InfinityFree cPanel
- [ ] Database user created
- [ ] User granted ALL privileges on database
- [ ] Database credentials noted down:
  - [ ] DB_HOST: `_________________`
  - [ ] DB_NAME: `_________________`
  - [ ] DB_USER: `_________________`
  - [ ] DB_PASS: `_________________`
- [ ] `database.sql` imported successfully via phpMyAdmin
- [ ] Verified tables created (users, contributions, withdrawals, etc.)

## File Upload

- [ ] Connected to InfinityFree via FTP or File Manager
- [ ] Created `api/` folder in `public_html/`
- [ ] Uploaded all files from `api/` folder:
  - [ ] `.htaccess`
  - [ ] `index.php`
  - [ ] `config/` folder and files
  - [ ] `controllers/` folder and files
  - [ ] `lib/` folder and files
- [ ] Verified folder structure matches original
- [ ] File permissions set correctly (755 for folders, 644 for files)

## Configuration

- [ ] Edited `api/config/config.php`
- [ ] Updated DB_HOST
- [ ] Updated DB_NAME
- [ ] Updated DB_USER
- [ ] Updated DB_PASS
- [ ] Updated ALLOWED_ORIGINS with your domain
- [ ] Saved and verified file

## Admin User Creation

- [ ] Generated password hash (using `generate_admin_hash.php` or PHP script)
- [ ] Created admin user in database via SQL
- [ ] Verified admin user exists in `users` table
- [ ] Deleted `generate_admin_hash.php` file (security)

## SSL/HTTPS

- [ ] Enabled SSL certificate in cPanel
- [ ] Verified HTTPS works (https://yourdomain.com/api/)

## Testing

- [ ] API base endpoint accessible: `https://yourdomain.com/api/`
- [ ] Tested login endpoint
- [ ] Verified admin can log in
- [ ] Tested CORS headers work
- [ ] Tested sample API endpoints

## Flutter App Configuration

- [ ] Updated API base URL in Flutter app
- [ ] Updated WebSocket URL (if applicable, note: InfinityFree doesn't support WebSockets)
- [ ] Tested login from Flutter app
- [ ] Tested major features from app

## Security

- [ ] Changed default admin password
- [ ] CORS configured to only allow your app domain
- [ ] `.htaccess` file in place
- [ ] Removed any temporary/hash generation files
- [ ] Error logging enabled (check in cPanel)

## Documentation

- [ ] API base URL documented: `https://yourdomain.com/api`
- [ ] Database credentials stored securely
- [ ] Admin credentials stored securely
- [ ] Team members informed of new API URL

## Post-Deployment

- [ ] Monitor error logs for first 24 hours
- [ ] Test all features thoroughly
- [ ] Set up database backup schedule
- [ ] Document any issues encountered
- [ ] Share API documentation with team

---

## Quick Reference

**API Base URL:**
```
https://yourdomain.infinityfreeapp.com/api
```

**Database Info:**
- Host: `_________________`
- Name: `_________________`
- User: `_________________`
- Pass: `_________________`

**Admin Credentials:**
- Email: `admin@bhs.local`
- Password: `_________________` (keep secure!)

---

## Troubleshooting Notes

Record any issues and solutions here:

```
Issue: 
Solution: 

Issue: 
Solution: 
```

---

## Important Reminders

1. ⚠️ **WebSocket Limitation**: InfinityFree free hosting does NOT support WebSocket connections. Consider alternative solutions (Cloudflare Workers, Pusher, or polling).

2. 🔒 **Security**: Always use HTTPS. Never commit passwords or credentials to version control.

3. 💾 **Backups**: Regularly backup your database via phpMyAdmin export.

4. 📊 **Monitoring**: Check error logs regularly in cPanel for any issues.

5. 🔄 **Updates**: Keep PHP version updated if possible (check compatibility).

---

**Status**: ☐ Not Started | ☐ In Progress | ☐ Completed

**Deployment Date**: _______________

**Deployed By**: _______________

