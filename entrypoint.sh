#!/bin/sh
# Entrypoint script for Docker container

# Default to port 8080 if PORT is not set
export PORT="${PORT:-8080}"

echo "Starting server on port $PORT..."
exec php -S 0.0.0.0:"$PORT" -t . router.php
