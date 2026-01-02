#!/bin/bash
# Run all PostgreSQL migrations for Bhs Union API

# Database connection details (from Render)
DB_HOST="dpg-d5bn5ppr0fns7393b8lg-a.oregon-postgres.render.com"
DB_NAME="bhsunion"
DB_USER="bhsunion"
DB_PASS="lLfPdFWBfBw3Y4nBARjroCVJRpecIVH3"

# Export password for psql
export PGPASSWORD="$DB_PASS"

echo "Running PostgreSQL migrations..."

# Run main schema (if not already run)
echo "1. Running main schema (database.sql)..."
psql -h "$DB_HOST" -U "$DB_USER" -d "$DB_NAME" -f database.sql

# Run trigger migrations
echo "2. Running trigger migrations..."
psql -h "$DB_HOST" -U "$DB_USER" -d "$DB_NAME" -f migrations/001_add_triggers.sql

echo "Migrations completed!"
echo ""
echo "Verifying database state..."
psql -h "$DB_HOST" -U "$DB_USER" -d "$DB_NAME" -c "\dt"

# Unset password
unset PGPASSWORD

