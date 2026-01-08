# Critical Fix: Docker Service Names & Paths

**Context:**
I successfully moved my Symfony project files from a nested `~/shop/shop` folder up to `~/shop`.
I currently have `compose.yaml`, `Dockerfile`, and `src/` directly in `~/shop`.

**The Problems:**
1. **Wrong Service Name:** I tried running `docker compose run symfony_app ...` but got `no such service: symfony_app`. The service name in `compose.yaml` is likely different (e.g., `app`, `php`, or `database`), even though the container name might be `symfony_app`.
2. **HTTP 500 Error:** accessing `localhost` gives a 500 error. This is likely because the `vendor` folder is missing or paths in Nginx/PHP config still point to the old `shop/` subfolder.
3. **Nginx/PHP Paths:** Since I moved the files, the volume mappings in `compose.yaml` and the `root` directive in Nginx config (`docker/nginx/default.conf` or similar) might still reference `./shop`.

**YOUR TASK:**
1. **Identify Service Names:** Check my `compose.yaml` to find the EXACT name of the PHP service.
2. **Fix Config Paths:** Review `compose.yaml` and `docker/nginx/default.conf`. If any path points to `./shop/`, change it to `.` (current directory) or the correct absolute path inside the container.
3. **Install Dependencies:** Once the service name and paths are fixed, execute the following command using the CORRECT service name (replace `SERVICE_NAME` below):
   ```bash
   docker compose run --rm SERVICE_NAME composer require easycorp/easyadmin-bundle:^4.20 symfony/framework-bundle:6.4.* --with-all-dependencies