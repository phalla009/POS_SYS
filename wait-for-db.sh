#!/bin/sh
# Simple check loop using nc (netcat) or php connection check to verify DB is up
echo "Waiting for database..."
while ! nc -z -v -w3 "$DB_HOST" "$DB_PORT"; do
  sleep 2
done
echo "Database is up and running!"