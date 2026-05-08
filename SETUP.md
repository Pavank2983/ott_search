# SETUP.md

# MUVI OTT SaaS Search Platform — Local Setup Guide

This document provides complete step-by-step instructions to set up and run the MUVI OTT SaaS Search Platform locally using Docker.

The setup process initializes:

- Laravel application
- PostgreSQL database
- Redis cache
- Elasticsearch search engine
- Nginx web server
- Large-scale OTT seed data
- Elasticsearch indexing pipeline

---

# Prerequisites

Install the following tools before starting setup:

| Tool | Version |
|---|---|
| Docker Desktop | Latest |
| WSL2 (Windows Users) | Enabled |
| Git | Latest |
| Node.js | 20+ Recommended |

Recommended tools:

- VS Code
- Postman
- Docker Extension for VS Code

---

# Recommended Development Environment

Recommended OS setup:

- Windows 11 + WSL2 Ubuntu 24.04
- Native Linux

Recommended project location:

```bash
~/projects/muvi_ott_search
```

Avoid running the project directly from:

```text
C:\ or D:\
```

because Docker bind mounts on Windows NTFS filesystems may significantly reduce Laravel filesystem performance.

---

# Clone Repository

Clone the repository:

```bash
git clone https://github.com/Pavank2983/ott_search.git
```

Move into the project directory:

```bash
cd muvi_ott_search
```

---

# Environment Configuration

Copy the environment configuration file:

```bash
cp .env.example .env
```

---

# Build Docker Containers

Build and start all required services:

```bash
docker compose up -d --build
```

---

# Verify Running Containers

Check container status:

```bash
docker compose ps
```

Expected services:

```text
app
nginx
postgres
redis
elasticsearch
```

---

# Install PHP Dependencies

Install Laravel dependencies inside the application container:

```bash
docker compose exec app composer install
```

---

# Generate Laravel Application Key

Generate the Laravel application key:

```bash
docker compose exec app php artisan key:generate
```

---

# Run Database Migrations

Initialize the PostgreSQL database schema:

```bash
docker compose exec app php artisan migrate
```

---

# Run Database Seeders

Generate OTT sample data:

```bash
docker compose exec app php artisan db:seed
```

The seeder generates:

- 2 OTT tenants
- 50,000+ contents per tenant
- 100,000+ total OTT contents
- Actor relationships
- Search-ready metadata

---

# Create Elasticsearch Index

Open Laravel Tinker:

```bash
docker compose exec app php artisan tinker
```

Inside Tinker:

```php
$service = app(App\Services\ElasticSearchService::class);

$service->deleteIndex();

$service->createIndex();
```

Exit Tinker:

```bash
exit
```

---

# Import Elasticsearch Data

Import all OTT contents into Elasticsearch:

```bash
docker compose exec app php artisan search:import-content
```

This command indexes:

- Titles
- Descriptions
- Actors
- Genres
- Languages
- Ratings
- Content metadata

---

# Frontend Assets

Install frontend dependencies:

```bash
docker compose exec app npm install
```

Start the Vite development server:

```bash
docker compose exec app npm run dev
```

---

# Access Application

Open the application:

```text
http://localhost:8000
```

---

# Available Services

| Service | URL |
|---|---|
| Laravel Application | http://localhost:8000 |
| Elasticsearch | http://localhost:9200 |
| Vite Dev Server | http://localhost:5173 |

---

# Search Examples

## Basic Search

```text
http://localhost:8000/?q=action&tenant_id=1
```

---

## Actor Search

```text
http://localhost:8000/?q=Tom%20Cruise&tenant_id=1
```

---

## Filtered Search

```text
http://localhost:8000/?q=thriller&tenant_id=1&content_type=movie&min_rating=8
```

---

# Common Docker Commands

## Start Containers

```bash
docker compose up -d
```

---

## Stop Containers

```bash
docker compose down
```

---

## Restart Containers

```bash
docker compose restart
```

---

## View Logs

```bash
docker compose logs -f
```

---

# Common Laravel Commands

## Open App Container

```bash
docker compose exec app bash
```

---

## Clear Laravel Cache

```bash
docker compose exec app php artisan optimize:clear
```

---

## Run Migrations Again

```bash
docker compose exec app php artisan migrate:fresh
```

---

## Reseed Database

```bash
docker compose exec app php artisan db:seed
```

---

# Full Environment Reset

Completely rebuild the environment:

```bash
docker compose down -v
```

Rebuild containers:

```bash
docker compose up -d --build
```

Reinstall dependencies:

```bash
docker compose exec app composer install
```

Generate application key:

```bash
docker compose exec app php artisan key:generate
```

Run migrations:

```bash
docker compose exec app php artisan migrate
```

Seed database:

```bash
docker compose exec app php artisan db:seed
```

Recreate Elasticsearch index:

```bash
docker compose exec app php artisan tinker
```

Inside Tinker:

```php
$service = app(App\Services\ElasticSearchService::class);

$service->deleteIndex();

$service->createIndex();
```

Import Elasticsearch data:

```bash
docker compose exec app php artisan search:import-content
```

Start Vite:

```bash
docker compose exec app npm run dev
```

---

# Troubleshooting

## Elasticsearch Not Responding

Check Elasticsearch logs:

```bash
docker compose logs elasticsearch
```

Verify Elasticsearch health:

```bash
curl http://localhost:9200
```

---

## Laravel Permission Issues

Run:

```bash
docker compose exec app chmod -R 777 storage bootstrap/cache
```

---

## Rebuild Composer Autoload

```bash
docker compose exec app composer dump-autoload
```

---

# Notes

- All services run inside isolated Docker containers
- PostgreSQL stores relational OTT content data
- Elasticsearch powers full-text search and suggestions
- Redis is used for caching and queue-ready infrastructure
- The environment is optimized for scalable OTT SaaS search workflows
- Linux filesystem development is recommended for best Docker performance