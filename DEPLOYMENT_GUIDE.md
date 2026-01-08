# Deployment Guide - Server Automation with Supervisor

## Overview
This setup automatically runs Symfony Messenger workers (for email sending) alongside your web application using Supervisor. No manual intervention needed after deployment.

## Architecture

### Components Managed by Supervisor:
1. **Nginx** - Web server (listening on port 8080)
2. **PHP-FPM** - PHP processor
3. **Messenger Workers** - Email queue processing (2 workers)

### Files Created:
- `supervisord.conf` - Supervisor configuration managing all processes
- `docker-entrypoint.sh` - Container startup script
- `nginx.conf` - Nginx web server configuration
- `Dockerfile.production` - Production-ready Dockerfile

## How It Works

### Startup Sequence:
1. Container starts → `docker-entrypoint.sh` runs
2. Script waits for database connection
3. Runs cache clear and migrations
4. Starts Supervisor daemon
5. Supervisor launches:
   - PHP-FPM (backend)
   - Nginx (web server on port 8080)
   - 2x Messenger workers (email processing)

### Email Worker Details:
- **Command**: `messenger:consume async --time-limit=3600 --memory-limit=128M`
- **Workers**: 2 processes (can handle more emails simultaneously)
- **Auto-restart**: If worker crashes, Supervisor restarts it automatically
- **Memory limit**: 128MB per worker
- **Time limit**: 3600 seconds (1 hour) - worker restarts after processing for 1 hour

## Deployment to Koyeb

### Option 1: Use Production Dockerfile
Rename `Dockerfile.production` to `Dockerfile`:
```bash
mv Dockerfile Dockerfile.old
mv Dockerfile.production Dockerfile
```

### Option 2: Update Existing Dockerfile
Add these changes to your current Dockerfile:

1. Install supervisor and nginx:
```dockerfile
RUN apk add --no-cache supervisor nginx
```

2. Copy configuration files:
```dockerfile
COPY nginx.conf /etc/nginx/nginx.conf
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
```

3. Change CMD to ENTRYPOINT:
```dockerfile
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
```

### Environment Variables Required:
Make sure these are set in Koyeb:
```
APP_ENV=prod
APP_SECRET=your-secret-key
DATABASE_URL=mysql://user:pass@host:3306/dbname
MAILER_DSN=smtp://user:pass@smtp.gmail.com:587
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
```

## Testing Locally

### Build and run:
```bash
docker build -f Dockerfile.production -t symfony-shop:prod .

docker run -d \
  -p 8080:8080 \
  -e DATABASE_URL="mysql://root:root@host.docker.internal:3306/shop" \
  -e APP_ENV=prod \
  -e APP_SECRET=test-secret \
  -e MAILER_DSN="smtp://user:pass@smtp.gmail.com:587" \
  symfony-shop:prod
```

### Check supervisor status:
```bash
docker exec -it <container-id> supervisorctl status
```

Expected output:
```
messenger-worker:messenger-worker_00  RUNNING   pid 123, uptime 0:01:23
messenger-worker:messenger-worker_01  RUNNING   pid 124, uptime 0:01:23
nginx                                 RUNNING   pid 125, uptime 0:01:23
php-fpm                               RUNNING   pid 122, uptime 0:01:23
```

### Check logs:
```bash
# All supervisor logs
docker exec -it <container-id> tail -f /var/log/supervisor/supervisord.log

# Messenger worker logs
docker exec -it <container-id> tail -f /var/log/supervisor/messenger.log

# Nginx logs
docker logs <container-id>
```

## Monitoring Email Queue

### Check if emails are being processed:
```bash
docker exec -it <container-id> php bin/console messenger:stats
```

### Manually trigger a test email:
```bash
docker exec -it <container-id> php bin/console messenger:consume async -vv --limit=1
```

## Troubleshooting

### Workers not starting:
Check supervisor logs:
```bash
docker exec -it <container-id> cat /var/log/supervisor/messenger_error.log
```

### Database connection issues:
The entrypoint script waits for database. If it times out, check:
- DATABASE_URL is correct
- Database host is accessible from container

### Nginx not responding:
```bash
docker exec -it <container-id> nginx -t  # Test config
docker exec -it <container-id> supervisorctl restart nginx
```

### Clear cache in production:
```bash
docker exec -it <container-id> php bin/console cache:clear --env=prod
```

## Scaling Workers

To increase email processing capacity, edit `supervisord.conf`:

```ini
[program:messenger-worker]
numprocs=4  # Change from 2 to 4 workers
```

Rebuild and redeploy.

## Important Notes

1. **Port 8080**: Koyeb expects applications on port 8080 (configured in nginx.conf)
2. **Process Management**: Supervisor runs as root, but messenger workers run as `www-data`
3. **Auto-restart**: Workers restart every hour (time-limit) to prevent memory leaks
4. **Graceful shutdown**: Workers finish current job before stopping (stopwaitsecs=3600)

## Files Reference

### supervisord.conf
Manages 3 programs:
- `php-fpm`: PHP processor (priority 5)
- `nginx`: Web server (priority 10)
- `messenger-worker`: Email workers (priority 15, 2 processes)

### docker-entrypoint.sh
Startup script that:
1. Waits for database
2. Creates directories
3. Clears cache
4. Runs migrations
5. Starts supervisord

### nginx.conf
Web server configuration:
- Listens on port 8080
- Routes requests to PHP-FPM (127.0.0.1:9000)
- Serves static files with caching
- Security headers enabled

## Success Indicators

Your deployment is successful when:
- ✓ Web application loads on port 8080
- ✓ `supervisorctl status` shows all 4 processes RUNNING
- ✓ Emails are sent automatically after checkout
- ✓ Logs show messenger consuming messages
