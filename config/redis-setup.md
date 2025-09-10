# Redis Configuration for Church Management System

## Environment Configuration

Add the following to your `.env` file to enable Redis for caching and session management:

```
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CLIENT=phpredis
REDIS_CACHE_CONNECTION=cache
```

## Redis Installation

### For Windows:
1. Download Redis for Windows from https://github.com/microsoftarchive/redis/releases
2. Install Redis as a Windows service
3. Verify Redis is running with `redis-cli ping` (should return "PONG")

### For Linux/Mac:
1. Install Redis using your package manager:
   - Ubuntu/Debian: `sudo apt install redis-server`
   - CentOS/RHEL: `sudo yum install redis`
   - Mac: `brew install redis`
2. Start Redis service:
   - `sudo systemctl start redis` (Linux)
   - `brew services start redis` (Mac)

## PHP Redis Extension

Install the PHP Redis extension:

### For Windows (XAMPP):
1. Download the appropriate phpredis DLL from https://windows.php.net/downloads/pecl/releases/redis/
2. Place the DLL in your PHP extensions directory (e.g., `C:\xampp\php\ext`)
3. Add `extension=redis` to your php.ini file
4. Restart Apache

### For Linux/Mac:
1. Install via PECL: `sudo pecl install redis`
2. Add `extension=redis.so` to your php.ini file
3. Restart web server

## Laravel Redis Package

Install the predis package (alternative to phpredis extension):

```bash
composer require predis/predis
```

Then set `REDIS_CLIENT=predis` in your `.env` file if you're using predis instead of the phpredis extension.
