# 🚀 READY TO DEPLOY TO RENDER

## ✅ Your Code is Ready!

All files are committed and prepared for Render deployment.

---

## 📋 DEPLOYMENT CHECKLIST

### ✅ Completed
- [x] All API files in `api/` folder
- [x] `public/` folder with entry point
- [x] `render.yaml` configuration file
- [x] `api/config/config.php` reads environment variables
- [x] Git repository initialized
- [x] All files committed

### ⏳ To Do (Next Steps)

1. **Create GitHub Repository** (if not done)
2. **Push code to GitHub**
3. **Connect to Render**
4. **Set environment variables**
5. **Deploy**

---

## 🔥 DEPLOY NOW - Step by Step

### Step 1: Create GitHub Repository

1. Go to: **https://github.com/new**
2. Repository name: `bhs-union-api`
3. Visibility: **Public** or **Private**
4. **Do NOT** check "Initialize with README"
5. Click **"Create repository"**

### Step 2: Push Code to GitHub

**In your terminal, run:**

```bash
cd /home/s9/Bhs/Hosting

# Add your GitHub repository (replace YOUR_USERNAME)
git remote add origin https://github.com/YOUR_USERNAME/bhs-union-api.git

# Push code
git push -u origin main
```

**If you need GitHub authentication:**
- Create Personal Access Token: https://github.com/settings/tokens
- Use token instead of password when prompted

### Step 3: Deploy on Render

1. **Go to:** https://dashboard.render.com
2. **Click:** "New +" → **"Web Service"**
3. **Connect GitHub** (authorize if needed)
4. **Select repository:** `bhs-union-api`
5. **Configuration:**
   - Name: `bhs-union-api` ✓
   - Environment: `PHP` ✓ (auto-detected)
   - Region: Choose closest
   - Branch: `main` ✓
   - Root Directory: **(empty)**
   - Build Command: **(empty)**
   - Start Command: `php -S 0.0.0.0:$PORT -t public` ✓

6. **Environment Variables** (Click "Advanced"):
   ```
   DB_HOST = sql306.infinityfree.com
   DB_NAME = if0_40807765_bhsUnion
   DB_USER = if0_40807765
   DB_PASS = yc3480kj
   ALLOWED_ORIGINS = *
   PHP_VERSION = 8.1
   ```

7. **Click:** "Create Web Service"

8. **Wait 2-5 minutes** for deployment

### Step 4: Get Your API URL

After deployment, your API will be at:
```
https://bhs-union-api.onrender.com/api/
```

### Step 5: Test

```bash
# Test base endpoint
curl https://bhs-union-api.onrender.com/api/

# Test login
curl -X POST https://bhs-union-api.onrender.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"james.smith@bhs.local","password":"yc3480kj"}'
```

### Step 6: Update Flutter App

**Edit:** `lib/config/api_config.dart`

```dart
class ApiConfig {
  static const String baseUrl = 'https://bhs-union-api.onrender.com/api';
}
```

---

## ✅ DONE!

Your API is now live and will work with your Flutter app! 🎉

**No more anti-bot protection blocking requests!**

