#!/bin/sh
# Backup database jadwal_lab dari container Podman/Docker ke file SQL bertimestamp.
# Jalankan dari root project: sh database/backups/backup.sh
#
# Untuk restore:
#   podman exec -i jadwal-lab-db sh -c 'mariadb -u root -p"$MARIADB_ROOT_PASSWORD" jadwal_lab' < database/backups/<file>.sql

set -e

CONTAINER_NAME="${JADWAL_LAB_DB_CONTAINER:-jadwal-lab-db}"
BACKUP_DIR="$(dirname "$0")"
TIMESTAMP=$(date +%Y%m%d-%H%M%S)
OUTPUT_FILE="${BACKUP_DIR}/jadwal_lab-${TIMESTAMP}.sql"

echo "Membuat backup dari container: ${CONTAINER_NAME}"
podman exec -i "$CONTAINER_NAME" sh -c 'mysqldump -u root -p"$MARIADB_ROOT_PASSWORD" --single-transaction --routines --triggers --add-drop-table jadwal_lab' > "$OUTPUT_FILE"

echo "Backup selesai: $OUTPUT_FILE ($(du -h "$OUTPUT_FILE" | cut -f1))"

# Simpan hanya 14 backup terbaru supaya tidak memenuhi disk
ls -t "${BACKUP_DIR}"/jadwal_lab-*.sql 2>/dev/null | tail -n +15 | xargs -r rm -f
