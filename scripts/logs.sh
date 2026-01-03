#!/bin/bash
# View logs from NextGen containers

echo "🎮 NextGen Logs (Ctrl+C to exit)"
echo "================================="
echo ""

# Check which service to show logs for
if [ "$1" == "app" ]; then
    docker-compose logs -f app
elif [ "$1" == "db" ]; then
    docker-compose logs -f db
else
    docker-compose logs -f
fi
