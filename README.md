<p align="center">
  <img src="resources/nextgen.png" alt="NextGen Logo" width="200"/>
</p>

<h1 align="center">🎮 NextGen Gaming Platform</h1>

<p align="center">
  <strong>A full-stack gaming e-commerce platform with real-time delivery tracking</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white"/>
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white"/>
  <img src="https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white"/>
  <img src="https://img.shields.io/badge/CI%2FCD-GitHub_Actions-2088FF?style=for-the-badge&logo=github-actions&logoColor=white"/>
</p>

---

## 📸 Screenshots

### 🏠 Homepage
<img src="screenshots/home.png" alt="Homepage" width="100%"/>

### 🎯 Game Catalogue
<img src="screenshots/catalogue.png" alt="Game Catalogue" width="100%"/>

### 🚚 Real-Time Delivery Tracking
<img src="screenshots/delivery.png" alt="Delivery Tracking" width="100%"/>

### �‍d💼 Admin Dashboard
<img src="screenshots/admin-dashboard.png" alt="Admin Dashboard" width="100%"/>

---

## � DevOAps & Deployment

### Docker Containers Running
<img src="screenshots/docker-running.png" alt="Docker Running" width="100%"/>

### Deployed on Linux VM (Kali)
<img src="screenshots/vm-deployment.png" alt="VM Deployment" width="100%"/>

### Database Management (phpMyAdmin)
<img src="screenshots/database.png" alt="Database" width="100%"/>

---

## ✨ Features

| Feature | Description |
|---------|-------------|
| 🛒 **E-Commerce** | Game catalogue, categories, purchase system |
| 👤 **User System** | Authentication, profiles, game library |
| 🚚 **Delivery Tracking** | Real-time map tracking with status updates |
| 👨‍💼 **Admin Panel** | Full CRUD for users, games, categories, deliveries |
| 🎨 **Modern UI** | Responsive gaming-themed design |
| 🐳 **Dockerized** | One-command deployment with Docker Compose |
| 🔄 **CI/CD** | Automated testing and builds with GitHub Actions |

---

## �️ Tecch Stack

<table>
<tr>
<td align="center"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg" width="40"/><br/>PHP 8.2</td>
<td align="center"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original.svg" width="40"/><br/>MySQL</td>
<td align="center"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/docker/docker-original.svg" width="40"/><br/>Docker</td>
<td align="center"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/apache/apache-original.svg" width="40"/><br/>Apache</td>
<td align="center"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/javascript/javascript-original.svg" width="40"/><br/>JavaScript</td>
</tr>
</table>

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
# 🌐 App:        http://localhost:8080
# 🗄️ phpMyAdmin: http://localhost:8081
```

### Option 2: XAMPP

1. Clone to `C:\xampp\htdocs\`
2. Import `database.sql` into MySQL
3. Start Apache & MySQL
4. Visit `http://localhost/Projet_nextgen`

---

## 📁 Project Structure

```
Projet_nextgen/
├── 📂 api/              # REST API endpoints
├── 📂 config/           # Database configuration
├── 📂 controller/       # MVC Controllers
├── 📂 models/           # Data models
├── �D view/
│   ├── frontoffice/     # User pages
│   ├── backoffice/      # Admin pages
│   ├── css/             # Stylesheets
│   └── js/              # JavaScript
├── 📂 games/            # Mini-games collection
├── 📂 resources/        # Media uploads
├── 📂 scripts/          # Deployment scripts
├── 🐳 docker-compose.yml
├── 🐳 Dockerfile
└── �️ rdatabase.sql
```

---

## 🐳 Docker Services

| Service | Port | Description |
|---------|------|-------------|
| `nextgen-app` | 8080 | PHP Application |
| `nextgen-db` | 3307 | MySQL Database |
| `nextgen-phpmyadmin` | 8081 | Database Admin UI |

---

## 📜 Deployment Scripts

```bash
./scripts/install-docker.sh  # Install Docker on Debian/Kali
./scripts/deploy.sh          # Build and start containers
./scripts/status.sh          # Check container status
./scripts/logs.sh            # View application logs
./scripts/stop.sh            # Stop all containers
```

---

## 🔄 CI/CD Pipeline

The project includes a GitHub Actions workflow that:

1. ✅ Runs PHP syntax checks on every push
2. 🐳 Builds Docker image
3. 📦 Pushes to Docker Hub (when configured)
4. 📢 Notifies when deployment is ready

---

## 👤 Author

<table>
<tr>
<td align="center">
<strong>Rayen Ouerghui</strong><br/>
<a href="https://github.com/rayenouerghui">@rayenouerghui</a>
</td>
</tr>
</table>

---

## 📄 License

This project is licensed under the MIT License.

---

<p align="center">
  <strong>⭐ Star this repo if you found it useful!</strong>
</p>
