#!/bin/bash
# NextGen Gaming Platform - Deployment Script
# Run this on your Kali Linux VM

set -e  # Exit on any error

echo "🎮 NextGen Gaming Platform - Deployment"
echo "========================================"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Step 1: Check if Docker is installed
echo -e "\n${YELLOW}[1/5] Checking Docker...${NC}"
if ! command -v docker &> /dev/null; then
    echo "Docker not found. Installing..."
    sudo apt update
    sudo apt install -y docker.io docker-compose
    sudo systemctl start docker
    sudo systemctl enable docker
    sudo usermod -aG docker $USER
    echo -e "${GREEN}✓ Docker installed${NC}"
    echo "⚠️  Please logout and login again, then re-run this script"
    exit 0
else
    echo -e "${GREEN}✓ Docker is installed${NC}"
fi

# Step 2: Check if docker-compose is installed
echo -e "\n${YELLOW}[2/5] Checking Docker Compose...${NC}"
if ! command -v docker-compose &> /dev/null; then
    echo "Installing Docker Compose..."
    sudo apt install -y docker-compose
fi
echo -e "${GREEN}✓ Docker Compose is ready${NC}"

# Step 3: Stop any existing containers
echo -e "\n${YELLOW}[3/5] Stopping existing containers...${NC}"
docker-compose down 2>/dev/null || true
echo -e "${GREEN}✓ Clean slate${NC}"

# Step 4: Pull latest images and build
echo -e "\n${YELLOW}[4/5] Building and starting containers...${NC}"
docker-compose up -d --build

# Step 5: Wait for database to be ready
echo -e "\n${YELLOW}[5/5] Waiting for database...${NC}"
sleep 10

# Show status
echo -e "\n${GREEN}========================================"
echo "🎉 Deployment Complete!"
echo "========================================"
echo -e "${NC}"
echo "📍 Application:  http://localhost:8080"
echo "📍 phpMyAdmin:   http://localhost:8081"
echo ""
echo "📋 Useful commands:"
echo "   docker-compose logs -f     # View logs"
echo "   docker-compose down        # Stop all"
echo "   docker-compose restart     # Restart"
echo ""
