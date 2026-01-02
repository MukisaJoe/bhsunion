# 🚀 Deploy to Render - Quick Start

Your code is ready and pushed to GitHub: `https://github.com/MukisaJoe/bhsunion`

## Step-by-Step Deployment

### 1. Go to Render Dashboard
Visit: https://dashboard.render.com

### 2. Create New Web Service
- Click **"New +"** → **"Web Service"**
- Connect your GitHub account (if not already connected)
- Select repository: **`MukisaJoe/bhsunion`**
- Click **"Connect"**

### 3. Configure Service
Render should auto-detect settings from `render.yaml`:
- **Name:** `bhs-union-api` (or choose your own)
- **Environment:** `Docker` (auto-detected)
- **Region:** Choose closest to your users
- **Branch:** `main`
- **Root Directory:** Leave empty (uses root of repo)
- **Build Command:** Leave empty (Docker handles this)
- **Start Command:** Leave empty (Docker handles this)

### 4. Set Environment Variables
Click **"Advanced"** → **"Add Environment Variable"** for each:

```
DB_HOST = sql306.infinityfree.com
DB_NAME = if0_40807765_bhsUnion
DB_USER = if0_40807765
DB_PASS = yc3480kj
ALLOWED_ORIGINS = *
```

⚠️ **Important:** The password is sensitive. Make sure to set it correctly.

### 5. Choose Plan
- Select **"Free"** plan (or paid if preferred)
- Click **"Create Web Service"**

### 6. Wait for Deployment
- Build takes 2-5 minutes
- Watch the **"Logs"** tab for progress
- You'll see build output and Apache starting

### 7. Get Your API URL
Once deployed, your API will be available at:
```
https://bhs-union-api.onrender.com/api/
```
(or `https://your-service-name.onrender.com/api/` if you used a different name)

### 8. Test the API
```bash
curl https://your-service-name.onrender.com/api/
```

Expected response:
```json
{"success":true,"message":"Bhs Union API"}
```

### 9. Update Flutter App
Once you have your Render URL, update:
```dart
// lib/config/api_config.dart
static const String baseUrl = 'https://your-service-name.onrender.com/api';
```

## ✅ Deployment Checklist

- [x] Code pushed to GitHub
- [x] Dockerfile configured
- [x] Apache setup ready
- [x] Database credentials prepared
- [ ] Render service created
- [ ] Environment variables set
- [ ] Service deployed successfully
- [ ] API URL tested
- [ ] Flutter app updated with API URL

## 🔍 Troubleshooting

### Build Fails
- Check logs in Render dashboard
- Ensure Dockerfile syntax is correct
- Verify all files are in the repository

### Connection Refused
- Wait for full deployment (can take 5+ minutes)
- Check service status in Render dashboard
- Verify environment variables are set

### Database Connection Error
- Double-check DB credentials in environment variables
- Ensure InfinityFree MySQL is accessible
- Test MySQL connection separately

### 404 Not Found
- Verify URL includes `/api/` path
- Check `.htaccess` is working
- Test health endpoint: `/api/health.php`

## 📝 Next Steps After Deployment

1. **Test Login:**
   ```bash
   curl -X POST https://your-service-name.onrender.com/api/auth/login \
     -H "Content-Type: application/json" \
     -d '{"email":"james.smith@bhs.local","password":"yc3480kj"}'
   ```

2. **Create Admin User:**
   - Run SQL: `create_admin_james_smith.sql` in your InfinityFree database

3. **Update Flutter App:**
   - Change `api_config.dart` baseUrl
   - Rebuild and test login

4. **Monitor:**
   - Check Render logs regularly
   - Monitor database connections
   - Watch for errors in Flutter app

---

**Ready to deploy!** Follow steps 1-9 above. 🚀

