# Quick Start: Deploy to Render (10 Minutes)

## 🚀 Fast Deployment Steps

### 1. Sign Up for Render
- Go to [render.com](https://render.com)
- Sign up with GitHub (recommended) or email

### 2. Create New Web Service
1. Click **"New +"** → **"Web Service"**
2. Connect your GitHub repository OR use "Public Git repository"
3. Select repository: `/home/s9/Bhs` (your Hosting folder)

### 3. Configure Service
- **Name:** `bhs-union-api`
- **Environment:** `PHP`
- **Root Directory:** `Hosting` (if deploying from root) or leave empty
- **Build Command:** (leave empty)
- **Start Command:** `php -S 0.0.0.0:$PORT -t public`

### 4. Set Environment Variables
Click **"Advanced"** → **"Add Environment Variable"**:

```
DB_HOST = sql306.infinityfree.com
DB_NAME = if0_40807765_bhsUnion
DB_USER = if0_40807765
DB_PASS = yc3480kj
ALLOWED_ORIGINS = *
PHP_VERSION = 8.1
```

**Or use your own MySQL database credentials.**

### 5. Deploy
- Click **"Create Web Service"**
- Wait 2-5 minutes for deployment
- Your API will be at: `https://bhs-union-api.onrender.com`

### 6. Test API
```bash
curl https://bhs-union-api.onrender.com/api/
```

### 7. Update Flutter App
Edit `lib/config/api_config.dart`:
```dart
class ApiConfig {
  static const String baseUrl = 'https://bhs-union-api.onrender.com/api';
}
```

**Done!** Your API is live without anti-bot protection! 🎉

---

## 📝 Notes

- **Free tier:** Service sleeps after 15 min inactivity (wakes on first request)
- **Database:** Can use existing InfinityFree MySQL or set up new one
- **Auto-deploy:** Pushes to Git automatically redeploy
- **No anti-bot:** Clean API endpoints that work with Flutter!

---

## 🔧 Troubleshooting

**Service won't start?**
- Check Logs tab in Render
- Verify `public/index.php` exists
- Check start command is correct

**Database connection fails?**
- Verify environment variables in Render dashboard
- Check database allows external connections
- Test with your current InfinityFree MySQL first

