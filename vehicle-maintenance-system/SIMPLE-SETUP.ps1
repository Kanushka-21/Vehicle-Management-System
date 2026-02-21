# Simple Setup Script for VMS
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Vehicle Maintenance System - Auto Setup" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "IMPORTANT: Your PHP version is 8.0.30" -ForegroundColor Yellow
Write-Host "Laravel 11 requires PHP 8.2+" -ForegroundColor Yellow
Write-Host "Laravel 10 requires PHP 8.1+" -ForegroundColor Yellow
Write-Host ""
Write-Host "Recommended Solution:" -ForegroundColor Green
Write-Host "1. Update XAMPP to get PHP 8.2+" -ForegroundColor White
Write-Host "   Download from: https://www.apachefriends.org/download.html" -ForegroundColor Cyan
Write-Host ""
Write-Host "OR" -ForegroundColor Yellow
Write-Host ""
Write-Host "2. For quick testing, use simplified version:" -ForegroundColor White
Write-Host "   - Frontend only (mock API data)" -ForegroundColor White
Write-Host ""
Write-Host "Would you like to:" -ForegroundColor Yellow
Write-Host "[1] Continue with frontend only (works now)" -ForegroundColor Green
Write-Host "[2] Get instructions to upgrade PHP" -ForegroundColor Cyan
Write-Host "[3] Try alternative setup (may have issues)" -ForegroundColor Red  
Write-Host ""
$choice = Read-Host "Enter choice (1-3)"

if ($choice -eq "1") {
    Write-Host "`nStarting frontend only..." -ForegroundColor Green
    cd "D:\new databse work\vehicle-maintenance-system\frontend"
    npm run dev
} elseif ($choice -eq "2") {
    Write-Host "`nPHP Upgrade Instructions:" -ForegroundColor Cyan
    Write-Host "1. Download latest XAMPP: https://www.apachefriends.org/download.html" -ForegroundColor White
    Write-Host "2. Install it (will include PHP 8.2+)" -ForegroundColor White
    Write-Host "3. Add to PATH: C:\xampp\php" -ForegroundColor White
    Write-Host "4. Restart PowerShell" -ForegroundColor White
    Write-Host "5. Run this script again" -ForegroundColor White
    Write-Host ""
    pause
} else {
    Write-Host "`nAttempting setup with current PHP version..." -ForegroundColor Yellow
    Write-Host "This may encounter compatibility issues" -ForegroundColor Red
    pause
}
