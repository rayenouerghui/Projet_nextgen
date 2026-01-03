#!/bin/bash
# Docker Installation Script for Kali Linux / Debian
# Run this FIRST if you don't have Docker

set -e

echo "🐳 Installing Docker on Kali Linux"
echo "==================================="

# Update system
echo "[1/4] Updating system..."
sudo apt update

# Install Docker
echo "[2/4] Installing Docker..."
sudo apt install -y docker.io

# Install Docker Compose
echo "[3/4] Installing Docker Compose..."
sudo apt install -y docker-compose

# Start Docker service
echo "[4/4] Starting Docker service..."
sudo systemctl start docker
sudo systemctl enable docker

# Add current user to docker group (no sudo needed for docker commands)
sudo usermod -aG docker $USER

echo ""
echo "✅ Docker installed successfully!"
echo ""
echo "⚠️  IMPORTANT: You must logout and login again"
echo "   (or reboot) for group changes to take effect."
echo ""
echo "After re-login, test with: docker --version"
echo "Then run: ./scripts/deploy.sh"
