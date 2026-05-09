@echo off
echo Starting Queue Worker...
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
pause
