@echo off
REM Credence e-TVCMS Startup Script for Windows
REM This script helps you get started with the e-TVCMS system

echo ===========================================
echo Credence e-TVCMS - TV Content Procurement Management System
echo ===========================================
echo.

REM Check if Docker is running
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker is not running. Please start Docker Desktop first.
    pause
    exit /b 1
)

echo ✅ Docker is running

REM Check if docker-compose is available
docker-compose --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ docker-compose is not available. Please install Docker Desktop with docker-compose.
    pause
    exit /b 1
)

echo ✅ docker-compose is available

REM Start the containers
echo.
echo 🚀 Starting e-TVCMS containers...
docker-compose up -d

REM Wait for containers to be ready
echo.
echo ⏳ Waiting for containers to be ready...
timeout /t 10 /nobreak >nul

REM Check if containers are running
docker-compose ps | findstr "Up" >nul
if %errorlevel% equ 0 (
    echo ✅ Containers are running successfully!
    echo.
    echo 🌐 Access your e-TVCMS system:
    echo    Main Website: http://localhost:8080
    echo    Database Admin: http://localhost:8081
    echo.
    echo 📋 Next Steps:
    echo    1. Visit http://localhost:8080 to install Drupal
    echo    2. Use these database settings during installation:
    echo       - Database name: tender_management
    echo       - Username: drupal
    echo       - Password: drupal123
    echo       - Host: db
    echo       - Port: 5432
    echo    3. After installation, enable custom modules:
    echo       - Go to /admin/modules
    echo       - Enable 'User Management' and 'Content Management'
    echo    4. Visit /content/dashboard for the main system
    echo.
    echo 🎯 e-TVCMS Features Ready:
    echo    ✅ Content Procurement Management
    echo    ✅ Producer Registration ^& Certification
    echo    ✅ Proposal Submission ^& Evaluation
    echo    ✅ Production Contract Management
    echo    ✅ Document Management System
    echo    ✅ Workflow Management
    echo    ✅ Reporting ^& Analytics
    echo.
    echo 📚 Content Types Supported:
    echo    • Swasta Baharu (Local New Private^)
    echo    • Sambung Siri (Series Continuation^)
    echo    • Program Luar Negara (International^)
    echo    • Produk Siap Tempatan (Local Finished^)
    echo.
) else (
    echo ❌ Some containers failed to start. Check with:
    echo    docker-compose ps
    echo    docker-compose logs
)

echo ===========================================
pause