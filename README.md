# 🎮 NextGen Gaming Platform

A full-stack gaming e-commerce platform with real-time delivery tracking, built with PHP and containerized with Docker.

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?logo=docker&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green)

---

## 📸 Screenshots

> Add your screenshots here:
> - `screenshots/home.png` - Homepage
> - `screenshots/catalogue.png` - Game catalogue
> - `screenshots/delivery.png` - Delivery tracking
> - `screenshots/admin.png` - Admin dashboard

---

## ✨ Features

### 🛒 E-Commerce
- Game catalogue with categories
- User authentication & profiles
- Purchase history tracking
- Game library management

### 🚚 Real-Time Delivery
- Live delivery tracking with map
- Order status updates
- Admin delivery management
- Animated progress indicators

### 👨‍💼 Admin Dashboard
- User management (CRUD)
- Game management (CRUD)
- Category management
- Delivery oversight

### 🎨 Modern UI/UX
- Responsive design
- Gaming-themed aesthetics
- Smooth animations
- Mobile-friendly

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|------------|
| Frontend | HTML5, CSS3, JavaScript |
| Backend | PHP 8.2 |
| Database | MySQL 8.0 |
| Server | Apache |
| Container | Docker & Docker Compose |
| CI/CD | GitHub Actions |

---

## 🚀 Quick Start

### Option 1: Docker (Recommended)

```bash
# Clone the repository
git clone https://github.com/rayenouerghui/Projet_nextgen.git
cd Projet_nextgen

# Start all services
docker-compose up -d

# Access the application
# App: http://localhost:8080
# phpMyAdmin: http://localhost:8081
```

### Option 2: XAMPP

1. Clone to `htdocs` folder
2. Import `database.sql` into MySQL
3. Access via `http://localhost/Projet_nextgen`

---

## 📁 Project Structure

```
Projet_nextgen/
├── api/                 # API endpoints
├── config/              # Database & app configuration
├── controller/          # PHP controllers (MVC)
├── models/              # Data models
├── view/
│   ├── frontoffice/     # User-facing pages
│   ├── backoffice/      # Admin pages
│   ├── css/             # Stylesheets
│   └── js/              # JavaScript
├── games/               # Mini-games collection
├── resources/           # Uploaded images & media
├── scripts/             # Deployment scripts
├── docker-compose.yml   # Docker configuration
├── Dockerfile           # Container build
└── database.sql         # Database schema
```

---

## 🐳 Docker Services

| Service | Port | Description |
|---------|------|-------------|
| `nextgen-app` | 8080 | PHP Application |
| `nextgen-db` | 3307 | MySQL Database |
| `nextgen-phpmyadmin` | 8081 | Database Admin |

---

## 📜 Scripts

```bash
# Install Docker (Debian/Kali)
./scripts/install-docker.sh

# Deploy application
./scripts/deploy.sh

# View logs
./scripts/logs.sh

# Check status
./scripts/status.sh

# Stop all containers
./scripts/stop.sh
```

---

## 👤 Author

**Rayen Ouerghui**

- GitHub: [@rayenouerghui](https://github.com/rayenouerghui)

---

## 📄 License

This project is licensed under the MIT License.
