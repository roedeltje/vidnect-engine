#!/usr/bin/env bash

echo "Starting VidNect Engine dev server..."
echo "URL: http://localhost:8080"
echo "Press CTRL+C to stop"

php -S localhost:8080 -t public
