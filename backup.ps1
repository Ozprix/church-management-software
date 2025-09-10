# Windows Backup Script for Church Management System
# This script creates a backup of the database and files

# Configuration
$backupDir = "C:\backups\church-management"
$date = Get-Date -Format "yyyyMMdd_HHmmss"
$backupPath = "$backupDir\backup_$date"
$dbName = "church_management"
$dbUser = "root"
$dbPass = ""
$keepBackups = 30 # Number of backup sets to keep

# Create backup directory if it doesn't exist
if (-not (Test-Path $backupPath)) {
    New-Item -ItemType Directory -Path $backupPath | Out-Null
}

Write-Host "📂 Creating backup in $backupPath" -ForegroundColor Cyan

# Export database
Write-Host "💾 Backing up database..." -ForegroundColor Cyan
$dbBackupFile = "$backupPath\db_backup_$date.sql"
& "C:\xampp\mysql\bin\mysqldump.exe" --user=$dbUser --password=$dbPass --databases $dbName --result-file=$dbBackupFile --single-transaction --routines --triggers --events

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Database backup failed" -ForegroundColor Red
    exit 1
}

# Compress the database backup
Write-Host "🗜 Compressing database backup..." -ForegroundColor Cyan
Compress-Archive -Path $dbBackupFile -DestinationPath "$backupPath\db_backup_$date.zip" -Force
Remove-Item $dbBackupFile

# Backup files
Write-Host "📁 Backing up files..." -ForegroundColor Cyan
$filesToBackup = @(
    ".env",
    "storage/app/public",
    "storage/framework/cache",
    "storage/framework/sessions",
    "storage/framework/views",
    "storage/logs"
)

# Create a temporary directory for file backup
$tempDir = "$env:TEMP\church_backup_$date"
New-Item -ItemType Directory -Path $tempDir | Out-Null

# Copy files to temp directory
foreach ($file in $filesToBackup) {
    if (Test-Path $file) {
        $destDir = Join-Path $tempDir (Split-Path $file -Parent)
        if (-not (Test-Path $destDir)) {
            New-Item -ItemType Directory -Path $destDir | Out-Null
        }
        Copy-Item -Path $file -Destination $destDir -Recurse -Force
    }
}

# Create archive of files
Write-Host "📦 Creating archive of files..." -ForegroundColor Cyan
Compress-Archive -Path "$tempDir\*" -DestinationPath "$backupPath\files_backup_$date.zip" -Force

# Clean up temp directory
Remove-Item -Path $tempDir -Recurse -Force

# Clean up old backups
Write-Host "🧹 Cleaning up old backups..." -ForegroundColor Cyan
$backupDirs = Get-ChildItem -Path $backupDir -Directory | Sort-Object CreationTime -Descending | Select-Object -Skip $keepBackups
foreach ($dir in $backupDirs) {
    Remove-Item -Path $dir.FullName -Recurse -Force
}

Write-Host "✅ Backup completed successfully!" -ForegroundColor Green
Write-Host "Backup location: $backupPath" -ForegroundColor Cyan
