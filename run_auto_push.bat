@echo off
echo =====================================
echo   Auto Sync Script (Git Bash)
echo =====================================
"C:\Program Files\Git\bin\bash.exe" -lc "cd /c/xampp/htdocs/RESTAURANT && ./auto_push.sh"
echo.
echo ✅ Script finished! Press any key to exit...
pause >nul
