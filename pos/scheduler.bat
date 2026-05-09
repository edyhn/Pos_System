@echo off
echo Starting Laravel Scheduler (runs every minute)...
php artisan schedule:work
pause
