#!/bin/bash
# Stop NextGen containers

echo "🛑 Stopping NextGen containers..."
docker-compose down

echo ""
echo "✅ All containers stopped"
echo ""
echo "To start again: ./scripts/deploy.sh"
