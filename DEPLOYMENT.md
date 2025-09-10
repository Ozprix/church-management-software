# Church Management System - Deployment Guide

This guide will help you deploy the Church Management System to a production environment.

## Prerequisites

- PHP 8.0 or higher
- Composer
- Node.js 14+ and npm
- MySQL/MariaDB
- Web server (Apache/Nginx)
- Git

## Windows Deployment

1. Open Command Prompt as Administrator
2. Navigate to your project directory
3. Run the deployment script:
   ```
   deploy.bat
   ```
   Or if you prefer PowerShell:
   ```
   .\deploy.ps1
   ```

## Linux Deployment

1. Open a terminal
2. Navigate to your project directory
3. Make the script executable:
   ```bash
   chmod +x deploy.sh
   ```
4. Run the deployment script:
   ```bash
   ./deploy.sh
   ```
   Note: You might need to run it with sudo depending on your permissions.

## Manual Steps After Deployment

1. Configure your web server to point to the `public` directory
2. Set up your `.env` file with production settings
3. Set up a task scheduler/cron job to run `php artisan schedule:run` every minute

## Production Recommendations

### Queue Workers
Set up queue workers for better performance:

**Linux (with Supervisor):**
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/application/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/your/application/storage/logs/worker.log
```

### Log Rotation
Set up log rotation to prevent log files from growing too large:

```bash
sudo nano /etc/logrotate.d/laravel
```

Add:
```
/path/to/your/application/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 640 www-data www-data
    sharedscripts
    postrotate
        kill -USR1 $(cat /run/php/php8.1-fpm.pid 2>/dev/null) 2>/dev/null || true
    endscript
}
```

### Backups
Set up regular database backups:

```bash
# Create backup script
nano /usr/local/bin/backup-db
```

Add:
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/path/to/backups"
DB_USER="your_db_user"
DB_PASS="your_db_password"
DB_NAME="your_database"

mkdir -p $BACKUP_DIR
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > "$BACKUP_DIR/backup_$DATE.sql.gz"
find $BACKUP_DIR -type f -name "backup_*.sql.gz" -mtime +7 -delete
```

Make it executable:
```bash
chmod +x /usr/local/bin/backup-db
```

Add to crontab:
```bash
crontab -e
```

Add:
```
0 2 * * * /usr/local/bin/backup-db
```

## Security

1. Set proper file permissions:
   ```bash
   chown -R www-data:www-data /path/to/your/application
   find /path/to/your/application -type d -exec chmod 755 {} \;
   find /path/to/your/application -type f -exec chmod 644 {} \;
   chmod -R 775 storage bootstrap/cache
   ```

2. Configure your web server to block access to sensitive files:

**Nginx:**
```nginx
location ~* \.(env|log|lock|git|svn|cvs|DS_Store|config|yaml|yml|json)$ {
    deny all;
    return 404;
}
```

**Apache (.htaccess):**
```
<FilesMatch "^\.|^composer\.(json|lock)|^\.env|^\.git|^\.svn|^\.cvs|^package(-lock)\.json|^package\.json|^gulpfile\.js|^webpack\.mix\.js|^phpunit\.xml$|^_ide_helper\.php$|^.+_migration\.php$|^composer\.phar$|^Procfile$|^Procfile\.dev$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

## Monitoring

Consider setting up monitoring tools:

1. **Laravel Telescope** for local debugging
2. **Laravel Horizon** for queue monitoring
3. **Sentry** or **Bugsnag** for error tracking
4. **New Relic** or **Blackfire** for performance monitoring

## Troubleshooting

If you encounter issues during deployment:

1. Check the error messages in the console
2. Look at the Laravel log files in `storage/logs`
3. Make sure all environment variables are set correctly in `.env`
4. Verify file and directory permissions
5. Check if all required PHP extensions are installed

## Support

For support, please contact your system administrator or open an issue in the repository.
