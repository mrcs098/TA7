@echo off
setlocal
cd /d "%~dp0"
where php >nul 2>nul
if errorlevel 1 (
    echo PHP is not on PATH. See START_HERE.txt.
    pause
    exit /b 1
)
where composer >nul 2>nul
if errorlevel 1 (
    echo Composer is not on PATH. See START_HERE.txt.
    pause
    exit /b 1
)
php -r "exit(version_compare(PHP_VERSION, '8.2.0', '>=') ? 0 : 1);"
if errorlevel 1 (
    echo PHP 8.2 or newer is required.
    pause
    exit /b 1
)
php -r "foreach (['fileinfo', 'gd', 'mbstring', 'pdo_sqlite'] as $ext) { if (!extension_loaded($ext)) { fwrite(STDERR, 'Enable the PHP extension: '.$ext.PHP_EOL); exit(1); } }"
if errorlevel 1 (
    echo Run php --ini to locate php.ini. See START_HERE.txt for details.
    pause
    exit /b 1
)
if not exist .env copy .env.example .env >nul
call composer install --prefer-dist --no-interaction
if errorlevel 1 goto failed
php scripts/setup.php
if errorlevel 1 goto failed
echo.
echo Open http://127.0.0.1:8000 after the server starts.
echo Keep this window open while using the application.
php artisan serve --host=127.0.0.1 --port=8000
if errorlevel 1 goto failed
exit /b 0

:failed
echo.
echo Setup or startup failed. Copy the error above when asking for help.
pause
exit /b 1
