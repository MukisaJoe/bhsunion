# Deploy to Render - Step by Step

## ✅ Git Repository Ready

Your code is committed and ready. Now deploy to Render:

---

## 🚀 Method 1: Connect GitHub Repository (Recommended)

### Step 1: Push to GitHub

1. **Create a GitHub repository:**
   - Go to [github.com](https://github.com)
   - Click "New repository"
   - Name: `bhs-union-api` (or any name)
   - Make it **Public** or **Private**
   - Don't initialize with README
   - Click "Create repository"

2. **Push your code:**
   ```bash
   cd /home/s9/Bhs/Hosting
   git remote add origin https://github.com/YOUR_USERNAME/bhs-union-api.git
   git branch -M main
   git push -u origin main
   ```

### Step 2: Connect to Render

1. **Go to [render.com](https://render.com)** and login
2. **Click "New +" → "Web Service"**
3. **Connect GitHub account** (if not connected)
4. **Select repository:** `bhs-union-api`
5. **Configure:**
   - **Name:** `bhs-union-api`
   - **Environment:** `PHP`
   - **Region:** Choose closest to you
   - **Branch:** `main`
   - **Root Directory:** (leave empty - files are in root)
   - **Build Command:** (leave empty)
   - **Start Command:** `php -S 0.0.0.0:$PORT -t public`
6. **Click "Advanced"** → Add Environment Variables:
   ```
   DB_HOST = sql306.infinityfree.com
   DB_NAME = if0_40807765_bhsUnion
   DB_USER = if0_40807765
   DB_PASS = yc3480kj
   ALLOWED_ORIGINS = *
   PHP_VERSION = 8.1
   ```
7. **Click "Create Web Service"**

---

## 🚀 Method 2: Manual Deploy (Without GitHub)

1. **Go to [render.com](https://render.com)** and login
2. **Click "New +" → "Web Service"**
3. **Select "Public Git repository"** or **"Deploy from public URL"**
4. **Or use Render CLI** (if installed):
   ```bash
   render deploy
   ```

---

## 📋 After Deployment

### 1. Get Your API URL
After deployment (2-5 minutes), Render will provide:
```
https://bhs-union-api.onrender.com
```

Your API endpoints:
```
https://bhs-union-api.onrender.com/api/
https://bhs-union-api.onrender.com/api/auth/login
```

### 2. Test API
```bash
# Test base endpoint
curl https://bhs-union-api.onrender.com/api/

# Test login
curl -X POST https://bhs-union-api.onrender.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"james.smith@bhs.local","password":"yc3480kj"}'
```

### 3. Update Flutter App

Edit `lib/config/api_config.dart`:
```dart
class ApiConfig {
  static const String baseUrl = 'https://bhs-union-api.onrender.com/api';
}
```

---

## 🔍 Check Deployment Status

1. Go to Render dashboard
2. Click on your service
3. Check **"Logs"** tab for deployment progress
4. Check **"Events"** tab for build status

---

## ⚠️ First Request May Be Slow

- Render free tier services **sleep after 15 minutes** of inactivity
- First request after sleep takes 30-60 seconds to wake up
- Subsequent requests are fast
- Upgrade to paid plan to avoid sleeping

---

## ✅ Success Indicators

- ✅ Build completes without errors
- ✅ Service shows "Live" status
- ✅ API endpoint responds (check Logs tab)
- ✅ curl test works

---

**Your API will be live without anti-bot protection!** 🎉

