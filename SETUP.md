# SETUP.md

# OTT Search Module — Local Development Setup

This document explains how to set up and run the OTT Search Module locally using Docker.

---

# Requirements

Make sure the following tools are installed:

- Docker Desktop
- WSL2 enabled (for Windows users)
- Git

Recommended:
- VSCode
- Postman

---

# Tech Stack

- Laravel 13
- PostgreSQL
- Docker Compose

---

# Clone Repository

```bash
git clone https://github.com/Pavank2983/ott_search.git
cd ott_search
```

---

# Environment Setup

Copy the environment file:

```bash
cp .env.example .env
```

---

# Start Docker Containers

Build and start all services:

```bash
docker compose up -d --build
```

This will start:

- Laravel App
- PostgreSQL
- Nginx

---

# Install PHP Dependencies

Run Composer install inside the app container:

```bash
docker compose exec app composer install
```

---

# Generate Laravel Application Key

```bash
docker compose exec app php artisan key:generate
```

---

# Run Database Migrations

```bash
docker compose exec app php artisan migrate
```

This will create the required database schema.

---

# Run Database Seeders

```bash
docker compose exec app php artisan db:seed
```

This will generate sample OTT content data.

---

# Access Application

Application:

```txt
http://localhost:8000
```

---

# Development Workflow

## Run Laravel Commands

Access app container:

```bash
docker compose exec app bash
```

Examples:

```bash
php artisan migrate
php artisan optimize:clear
```

---

# Troubleshooting

## Reset Everything

```bash
docker compose down -v
docker compose up -d --build
```

Then rerun:

```bash
docker compose exec app composer install
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

---

# Notes

- Containers are Linux-based but can be accessed from Windows/macOS normally
- Database data is recreated using migrations and seeders
- Docker ensures environment consistency across machines