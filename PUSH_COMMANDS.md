# Commands to Push to Render

## Option 1: Push to GitHub, then Connect to Render (Recommended)

### Step 1: Create GitHub Repository
1. Go to [github.com/new](https://github.com/new)
2. Repository name: `bhs-union-api`
3. Description: `Bhs Union API Backend`
4. Make it **Public** (or Private)
5. **Don't** check "Initialize with README"
6. Click **"Create repository"**

### Step 2: Push Code to GitHub
Run these commands (replace YOUR_USERNAME):

```bash
cd /home/s9/Bhs/Hosting
git remote add origin https://github.com/YOUR_USERNAME/bhs-union-api.git
git branch -M main
git push -u origin main
```

**Note:** You'll need to authenticate with GitHub (use personal access token or SSH key)

### Step 3: Connect to Render
1. Go to [render.com](https://render.com)
2. Click **"New +"** → **"Web Service"**
3. Connect GitHub account
4. Select repository: `bhs-union-api`
5. Configure as shown in RENDER_QUICK_START.md

---

## Option 2: Direct Deploy to Render (If You Have Repository URL)

If you already have a Git repository URL, just connect it in Render dashboard.

---

## Quick Setup Script

Run this to prepare for GitHub push:

```bash
cd /home/s9/Bhs/Hosting
# Add GitHub remote (replace YOUR_USERNAME)
git remote add origin https://github.com/YOUR_USERNAME/bhs-union-api.git
git branch -M main

# Then push (you'll need GitHub credentials):
# git push -u origin main
```

