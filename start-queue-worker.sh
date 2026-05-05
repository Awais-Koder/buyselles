#!/bin/bash

###############################################################################
# BuySelles Queue Worker Starter Script
# 
# This script starts the Laravel queue worker for processing emails.
# Use this for testing/development. For production, use Supervisor.
###############################################################################

echo "=========================================="
echo "  BuySelles Queue Worker Starter"
echo "=========================================="
echo ""

# Change to project directory
cd /home/awais-koder/WorkStation/projects/buyselles_bundle/buyselles

# Check if Redis is running
echo "Checking Redis connection..."
if redis-cli ping > /dev/null 2>&1; then
    echo "✓ Redis is running"
else
    echo "✗ Redis is NOT running!"
    echo "  Starting Redis..."
    sudo systemctl start redis
    if redis-cli ping > /dev/null 2>&1; then
        echo "✓ Redis started successfully"
    else
        echo "✗ Failed to start Redis. Please check Redis installation."
        exit 1
    fi
fi

echo ""
echo "Starting queue worker..."
echo "Press Ctrl+C to stop the worker"
echo ""
echo "Emails will be processed while this is running."
echo "=========================================="
echo ""

# Start queue worker
php artisan queue:work --daemon --sleep=3 --tries=3 --max-time=0
