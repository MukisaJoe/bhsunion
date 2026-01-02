# ⚡ Deploy to Render RIGHT NOW

Your code is ready! Follow these exact steps:

## 🔥 Quick Deploy (5 Minutes)

### Step 1: Create GitHub Repository

1. **Open:** [https://github.com/new](https://github.com/new)
2. **Repository name:** `bhs-union-api`
3. **Visibility:** Public (or Private)
4. **DO NOT** check "Initialize with README"
5. **Click "Create repository"**

### Step 2: Push Code

**Copy and paste these commands:**

```bash
cd /home/s9/Bhs/Hosting
git remote add origin https://github.com/YOUR_GITHUB_USERNAME/bhs-union-api.git
git branch -M main
git push -u origin main
```

**Replace `YOUR_GITHUB_USERNAME` with your actual GitHub username!**

**If prompted for credentials:**
- Use a **Personal Access Token** (not password)
- Create one at: [github.com/settings/tokens](https://github.com/settings/tokens)
- Select scope: `repo` (all)

### Step 3: Deploy on Render

1. **Open:** [dashboard.render.com](https://dashboard.render.com)
2. **Click:** "New +" → **"Web Service"**
3. **Connect GitHub** (if not connected)
4. **Select repository:** `bhs-union-api`
5. **Configure:**
   - Name: `bhs-union-api` (auto-filled)
   - Environment: `PHP` (should auto-detect)
   - Region: Choose closest
   - Branch: `main`
   - Root Directory: **(leave empty)**
   - Build Command: **(leave empty)**
   - Start Command: `php -S 0.0.0.0:$PORT -t public`
6. **Click "Advanced"** → Add Environment Variables:
   
   Click **"Add Environment Variable"** for each:
   ```
   Key: DB_HOST
   Value: sql306.infinityfree.com
   
   Key: DB_NAME  
   Value: if0_40807765_bhsUnion
   
   Key: DB_USER
   Value: if0_40807765
   
   Key: DB_PASS
   Value: yc3480kj
   
   Key: ALLOWED_ORIGINS
   Value: *
   
   Key: PHP_VERSION
   Value: 8.1
   ```

7. **Click "Create Web Service"**
8. **Wait 2-5 minutes** for deployment

### Step 4: Test Your API

After deployment, your API will be at:
```
https://bhs-union-api.onrender.com/api/
```

Test it:
```bash
curl https://bhs-union-api.onrender.com/api/
```

---

## ✅ That's It!

Your API is now live! Update your Flutter app:

**Edit:** `lib/config/api_config.dart`
```dart
class ApiConfig {
  static const String baseUrl = 'https://bhs-union-api.onrender.com/api';
}
```

---

## 🎉 Benefits

- ✅ No anti-bot protection blocking requests
- ✅ Automatic HTTPS/SSL
- ✅ Works perfectly with Flutter apps
- ✅ Free tier available

**Your Flutter app login will work now!** 🚀

