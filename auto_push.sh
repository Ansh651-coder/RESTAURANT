#!/bin/bash
# auto_push.sh - Auto commit & push

cd /c/xampp/htdocs/RESTAURANT || exit 1

git add -A

if git diff --cached --quiet; then
  echo "No changes to commit."
  exit 0
fi

git commit -m "Auto-commit: $(date '+%Y-%m-%d %H:%M:%S')"
git push origin main
