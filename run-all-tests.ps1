#!/usr/bin/env pwsh
# Comprehensive test script for Church Management System

Write-Host "======================================================="
Write-Host "   Running Comprehensive Tests for Church Management   "
Write-Host "======================================================="
Write-Host ""

# Function to check if a command exists
function Test-CommandExists {
    param ($command)
    $exists = $null -ne (Get-Command $command -ErrorAction SilentlyContinue)
    return $exists
}

# Function to run tests with colored output
function Run-Tests {
    param (
        [string]$TestType,
        [string]$Command,
        [string]$Description
    )
    
    Write-Host "Running $TestType Tests: $Description" -ForegroundColor Cyan
    Write-Host "Command: $Command" -ForegroundColor Gray
    Write-Host "-------------------------------------------------------"
    
    try {
        Invoke-Expression $Command
        if ($LASTEXITCODE -eq 0) {
            Write-Host "✅ $TestType Tests Passed" -ForegroundColor Green
        } else {
            Write-Host "❌ $TestType Tests Failed" -ForegroundColor Red
        }
    } catch {
        Write-Host "❌ Error running $TestType tests: $_" -ForegroundColor Red
    }
    
    Write-Host ""
}

# Check environment
Write-Host "Checking environment..." -ForegroundColor Yellow

$phpExists = Test-CommandExists "php"
$npmExists = Test-CommandExists "npm"
$composerExists = Test-CommandExists "composer"

if (-not $phpExists) {
    Write-Host "❌ PHP not found. Please install PHP and make sure it's in your PATH." -ForegroundColor Red
    exit 1
}

if (-not $npmExists) {
    Write-Host "❌ NPM not found. Please install Node.js and make sure it's in your PATH." -ForegroundColor Red
    exit 1
}

if (-not $composerExists) {
    Write-Host "❌ Composer not found. Please install Composer and make sure it's in your PATH." -ForegroundColor Red
    exit 1
}

# 1. Run PHP Unit Tests (Backend)
Run-Tests -TestType "PHP Unit" -Command "php artisan test" -Description "Testing backend functionality"

# 2. Run JavaScript Unit Tests (Frontend)
Run-Tests -TestType "JavaScript" -Command "npm test" -Description "Testing frontend components"

# 3. Run PHP Code Style Check
if (Test-CommandExists "php-cs-fixer") {
    Run-Tests -TestType "PHP Code Style" -Command "php-cs-fixer fix --dry-run --diff" -Description "Checking PHP code style"
} else {
    Write-Host "⚠️ php-cs-fixer not found. Skipping PHP code style check." -ForegroundColor Yellow
}

# 4. Run JavaScript Code Style Check
Run-Tests -TestType "JavaScript Code Style" -Command "npm run lint" -Description "Checking JavaScript code style"

# 5. Run Security Check
if (Test-CommandExists "composer") {
    Run-Tests -TestType "Security" -Command "composer audit" -Description "Checking for security vulnerabilities"
} else {
    Write-Host "⚠️ Composer not found. Skipping security check." -ForegroundColor Yellow
}

Write-Host "======================================================="
Write-Host "              All Tests Completed                      "
Write-Host "======================================================="
