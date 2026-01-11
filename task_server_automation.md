# Task: Automate Symfony Messenger with Supervisor for Production

**Goal:**
Configure the Docker container to automatically run the `messenger:consume` worker in the background. This ensures emails are sent automatically when deployed to the server (Koyeb), without manual intervention.

**Context:**
* [cite_start]The `Dockerfile` already installs `supervisor`.
* [cite_start]The `Dockerfile` expects `supervisord.conf` at `/etc/supervisor/conf.d/supervisord.conf`[cite: 27].
* [cite_start]The `Dockerfile` uses `docker-entrypoint.sh` as the CMD[cite: 27].

**EXECUTION PLAN:**

### 1. Create `supervisord.conf`
Create this file in the project root. It must manage three processes:
1.  **Apache** (Web Server).
2.  **PHP-FPM** (or just handle Apache if running mod_php).
3.  **Messenger Consume** (The Email Worker).
    * Command: `php bin/console messenger:consume async --time-limit=3600 --memory-limit=128M`
    * User: `www-data` (or appropriate user).
    * Autostart/Autorestart: true.

### 2. Update/Create `docker-entrypoint.sh`
This script serves as the container entrypoint.
* It should run standard Symfony setup commands (cache:clear, migrations if needed).
* **Crucial:** It must end by executing `/usr/bin/supervisord` (not just apache), so that Supervisor takes control and starts the workers.

**Deliverables:**
Provide the full content for:
1.  `supervisord.conf`
2.  `docker-entrypoint.sh`