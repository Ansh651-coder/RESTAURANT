#!/bin/bash
# auto_push.sh  — run with Git Bash

cd /c/xampp/htdocs/RESTAURANT || exit 1

# Stage all changes
git add -A

# If no staged changes, exit
if git diff --cached --quiet; then
  echo "No changes to commit."
  exit 0
fi

# Commit with a timestamp
git commit -m "Auto-commit: $(date '+%Y-%m-%d %H:%M:%S')"

# Push to main
git push origin main
