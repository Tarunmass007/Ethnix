# Ethnix - Local Deployment Script
# Run: .\start-local.ps1

$ErrorActionPreference = "Stop"
$projectRoot = $PSScriptRoot

Write-Host "=== Ethnix Local Deployment ===" -ForegroundColor Cyan
Write-Host ""

# Check Docker
$dockerOk = $false
try {
    $null = docker info 2>$null
    $dockerOk = $true
} catch {}

if ($dockerOk) {
    Write-Host "[1/3] Starting Docker Compose..." -ForegroundColor Green
    Set-Location $projectRoot
    docker compose up -d
    
    Write-Host "[2/3] Waiting for MySQL..." -ForegroundColor Green
    Start-Sleep -Seconds 8
    
    Write-Host "[3/3] Initializing database..." -ForegroundColor Green
    docker compose exec -T app php setup_db.php
    
    Write-Host ""
    Write-Host "Done! Ethnix is running at:" -ForegroundColor Green
    Write-Host "  http://localhost:8080" -ForegroundColor White
    Write-Host ""
    Write-Host "Login: Click 'Login as Admin (Dev)' or visit:" -ForegroundColor Yellow
    Write-Host "  http://localhost:8080/dev_login.php?user=admin&key=baba_secret_123" -ForegroundColor White
    Write-Host ""
} else {
    Write-Host "Docker not found. Using PHP built-in server..." -ForegroundColor Yellow
    Write-Host ""
    
    if (-not (Test-Path "$projectRoot\.env")) {
        Write-Host "Creating .env from .env.local.example..." -ForegroundColor Yellow
        Copy-Item "$projectRoot\.env.local.example" "$projectRoot\.env"
        Write-Host "Please edit .env with your MySQL credentials, then run again." -ForegroundColor Yellow
        exit 1
    }
    
    if (-not (Test-Path "$projectRoot\vendor\autoload.php")) {
        Write-Host "Running composer install..." -ForegroundColor Yellow
        composer install
    }
    
    Write-Host "Start PHP server with:" -ForegroundColor Green
    Write-Host "  php -S localhost:8080 -t . router.php" -ForegroundColor White
    Write-Host ""
    Write-Host "Run setup_db.php first if database is not initialized." -ForegroundColor Yellow
}
