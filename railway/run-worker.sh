#!/bin/bash
echo "Starting Laravel Queue Worker Daemon..."
while true; do
    php artisan queue:work --queue=upload,email,default --verbose --tries=3 --timeout=300 --memory=128
    echo "Worker exited with status $?. Restarting in 2 seconds..."
    sleep 2
done
