#!/bin/bash
# Auto sync script: Pull latest changes, then push local ones

cd /c/xampp/htdocs/RESTAURANT || exit

echo "🔄 Pulling latest changes from GitHub..."
git pull origin main

echo "📤 Adding local changes..."
git add .

echo "💬 Committing..."
git commit -m "Auto commit on $(date)" || echo "⚠️ Nothing to commit."

echo "⬆️ Pushing to GitHub..."
git push origin main

echo "✅ Sync complete!"
