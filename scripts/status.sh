#!/bin/bash
# Check status of NextGen containers

echo "🎮 NextGen Container Status"
echo "============================"
echo ""

# Check if containers are running
echo "📦 Containers:"
docker-compose ps

echo ""
echo "💾 Database Volume:"
docker volume ls | grep nextgen || echo "No volumes found"

echo ""
echo "🌐 Access URLs:"
echo "   App:        http://localhost:8080"
echo "   phpMyAdmin: http://localhost:8081"

echo ""
echo "📊 Resource Usage:"
docker stats --no-stream --format "table {{.Name}}\t{{.CPUPerc}}\t{{.MemUsage}}" 2>/dev/null || echo "Containers not running"
