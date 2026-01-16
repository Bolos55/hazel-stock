@echo off
set DB_HOST=localhost
set DB_PORT=3306
set DB_NAME=hazel_stock
set DB_USER=root
set DB_PASS=

echo Starting Hazel Stock Management Server...
echo Database: %DB_NAME% @ %DB_HOST%:%DB_PORT%
echo.
php -S localhost:8000
