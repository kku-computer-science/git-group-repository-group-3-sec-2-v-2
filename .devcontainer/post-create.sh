#!/bin/bash

# Exit on error
set -e

echo "Starting post-create setup..."

# 1. Git config
git config --global --add safe.directory /var/www/html

# 2. Install dependencies
echo "Installing Composer dependencies..."
composer install

# 3. Environment setup
if [ ! -f .env ]; then
    echo "Copying .env.example to .env..."
    cp .env.example .env
fi

echo "Generating application key..."
php artisan key:generate

# 4. Wait for MySQL to be ready
# Doubled from 30 to 60 tries (2s sleep each = up to 120s) to handle slow container startup
echo "Waiting for MySQL to be ready..."
max_tries=60
count=0
while ! mysql --skip-ssl -h db -u root -e "SELECT 1" >/dev/null 2>&1; do
    echo "Waiting for database connection... ($count/$max_tries)"
    sleep 2
    count=$((count+1))
    if [ $count -ge $max_tries ]; then
        echo "Error: Could not connect to database after $max_tries attempts."
        exit 1
    fi
done
echo "Database is ready!"

# 5. Reset and Import database.sql
echo "Recreating database to ensure clean state..."
mysql --skip-ssl -h db -u root -e "DROP DATABASE IF EXISTS laravel; CREATE DATABASE laravel;"

echo "Importing database.sql..."
{ echo "SET FOREIGN_KEY_CHECKS=0;"; cat database.sql; echo "SET FOREIGN_KEY_CHECKS=1;"; } | mysql --skip-ssl -h db -u root laravel

# 6. Fix migration history
#
# The database.sql was exported from a fully-migrated database, so all tables and
# columns already exist in the schema. However, the migrations table inside
# database.sql is missing records for the following migrations:
#
#   - 2022_03_14_160716_create_degrees_table
#       → Schema::create('degrees') with no guard  → would crash: "Table already exists"
#   - 2022_03_15_141518_create_programs_table
#       → Schema::create('programs') with no guard → would crash: "Table already exists"
#   - 2022_03_15_164730_add_programs_to_users_table
#       → adds program_id to users with no guard   → would crash: "Duplicate column name"
#
# The other missing migrations are either fully guarded with Schema::hasTable /
# Schema::hasColumn checks (safe to run), or create tables not yet in the dump
# (courses, blocked_ips) and must actually run.
#
# We use INSERT IGNORE so this step is idempotent — safe to re-run.
echo "Fixing missing migration records in the migrations table..."
mysql --skip-ssl -h db -u root laravel <<'ENDSQL'
INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES
('2022_03_14_160716_create_degrees_table',        99),
('2022_03_15_141518_create_programs_table',        99),
('2022_03_15_164730_add_programs_to_users_table',  99);
ENDSQL

# 7. Run only the genuinely new migrations
#
# After the fix above, Laravel will skip the three already-applied migrations and
# only execute the ones whose records are absent AND whose schema is not yet in the
# database:
#   - 2022_03_31_222122_create_courses_table            (courses table is missing from SQL)
#   - 2025_03_08_200000_create_blocked_ips_table        (guarded; blocked_ips missing from SQL)
#   - 2025_03_11_170527_add_academic_fields_to_authors  (guarded; columns missing from authors)
#
# The remaining un-recorded migrations are all fully guarded and will safely no-op:
#   - 2024_03_08_000000_add_indexes_for_performance     (checks index existence before adding)
#   - 2025_02_11_214413_add_link_to_research_groups     (Schema::hasColumn guard)
#   - 2025_02_24_193201_add_author_id_to_work_of_*      (Schema::hasTable guard)
echo "Running new migrations..."
php artisan migrate --force

echo "Post-create setup complete!"
