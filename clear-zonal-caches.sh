#!/usr/bin/env bash
# Run this AFTER every zonal-data import (local OR production) so the dropdowns/
# facets reflect the new data. Clears the two SERVER-side cache layers:
#   1) Laravel Cache::remember (city/barangay/classification lists)
#   2) the durable facet_cache table (Next.js facet cache)
# (Browser caches just need a hard refresh — Ctrl+Shift+R.)
set -e
cd "$(dirname "$0")"

echo "1/2  Clearing Laravel cache..."
php artisan cache:clear

echo "2/2  Emptying facet_cache table..."
DB=$(grep -E '^DB_DATABASE=' .env | cut -d= -f2 | tr -d '"')
U=$(grep -E '^DB_USERNAME=' .env | cut -d= -f2 | tr -d '"')
P=$(grep -E '^DB_PASSWORD=' .env | cut -d= -f2 | tr -d '"')
# Adjust the mysql path if needed (XAMPP default below)
MYSQL="${MYSQL_BIN:-/c/xampp/mysql/bin/mysql.exe}"
"$MYSQL" -u"${U:-root}" ${P:+-p"$P"} "$DB" -e "DELETE FROM facet_cache;"

echo "Done. Now hard-refresh the browser (Ctrl+Shift+R)."
