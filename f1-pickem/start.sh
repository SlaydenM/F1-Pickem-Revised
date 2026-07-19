#!/usr/bin/env bash
set -e

MYSQL_DATA=/home/runner/mysql-data
MYSQL_SOCK=/tmp/mysql.sock
MYSQL_PID=/tmp/mysql.pid
MYSQL_LOG=/tmp/mysql.log

# ── 1. Start MariaDB if not already running ──────────────────────────────────
if ! mysqladmin --socket="$MYSQL_SOCK" ping --silent 2>/dev/null; then
    echo "[start.sh] Starting MariaDB..."
    mysqld \
        --datadir="$MYSQL_DATA" \
        --socket="$MYSQL_SOCK" \
        --pid-file="$MYSQL_PID" \
        --port=3306 \
        --bind-address=127.0.0.1 \
        --user=runner \
        --silent-startup \
        2>>"$MYSQL_LOG" &

    # Wait up to 30 s for MySQL to accept connections
    for i in $(seq 1 30); do
        if mysqladmin --socket="$MYSQL_SOCK" ping --silent 2>/dev/null; then
            echo "[start.sh] MariaDB ready."
            break
        fi
        sleep 1
    done
fi

# ── 2. Run migrations ────────────────────────────────────────────────────────
cd /home/runner/workspace/f1-pickem
echo "[start.sh] Running migrations..."
php artisan migrate --force

# ── 3. Clear & warm caches ───────────────────────────────────────────────────
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ── 4. Serve on port 5000 ────────────────────────────────────────────────────
echo "[start.sh] Starting Laravel on port 5000..."
exec php artisan serve --host=0.0.0.0 --port=5000
