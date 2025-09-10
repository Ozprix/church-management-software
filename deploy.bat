@echo off
echo Starting deployment...

REM Check if PowerShell is available
powershell -Command "& {Write-Host '🚀 Starting deployment...' -ForegroundColor Green}" 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ❌ PowerShell is required but not installed. Please install PowerShell 5.1 or later.
    pause
    exit /b 1
)

REM Run the PowerShell script
powershell -ExecutionPolicy Bypass -File "%~dp0deploy.ps1"

REM Pause to see the output
pause
