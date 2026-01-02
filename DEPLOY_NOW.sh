#!/bin/bash
# Deployment script for Render

echo "🚀 Deploying Bhs Union API to Render..."
echo ""

# Check if we have a remote
if ! git remote get-url origin &>/dev/null; then
    echo "⚠️  No GitHub remote configured."
    echo ""
    echo "To deploy, you need to:"
    echo "1. Create a GitHub repository at: https://github.com/new"
    echo "2. Then run:"
    echo "   git remote add origin https://github.com/YOUR_USERNAME/bhs-union-api.git"
    echo "   git push -u origin main"
    echo ""
    echo "Or deploy manually in Render dashboard."
    exit 0
fi

echo "✅ Remote repository found"
echo "📤 Pushing to GitHub..."
git push -u origin main

echo ""
echo "✅ Code pushed! Now go to Render dashboard to complete deployment."
echo "   URL: https://dashboard.render.com"
echo ""
echo "Steps:"
echo "1. Click 'New +' → 'Web Service'"
echo "2. Connect your GitHub repository"
echo "3. Select 'bhs-union-api'"
echo "4. Add environment variables (see RENDER_QUICK_START.md)"
echo "5. Deploy!"

