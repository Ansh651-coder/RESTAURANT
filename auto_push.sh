#!/bin/bash
# auto_push.sh - Auto commit & push to GitHub

cd /c/xampp/htdocs/RESTAURANT || exit 1

# Stage all changes
git add -A

# If no changes, exit
if git diff --cached --quiet; then
  echo "No changes to commit."
  exit 0
fi

# Commit with timestamp
git commit -m "Auto-commit: $(date '+%Y-%m-%d %H:%M:%S')"

# Push to GitHub
git push origin main
