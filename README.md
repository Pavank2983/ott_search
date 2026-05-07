# MUVI OTT SaaS Search Module

A scalable multi-tenant OTT search platform built using Laravel, PostgreSQL, Redis, Docker, and Elasticsearch-ready architecture.

This project demonstrates:

* Production-oriented backend architecture
* Scalable search system design
* Multi-tenant SaaS implementation
* Full text search optimization
* Redis caching
* Dockerized infrastructure
* Queue-ready architecture
* Performance-focused engineering

---

# Features

## Implemented

* Multi-tenant OTT content system
* PostgreSQL Full Text Search
* Redis cache layer
* Search ranking using `ts_rank`
* Search vector indexing using GIN indexes
* Filter-based search
* Pagination
* Eager loading optimization
* Dockerized environment
* Large-scale seeders (50K+ records)
* WSL2 optimized Linux development workflow

---

# Tech Stack

| Layer               | Technology                  |
| ------------------- | --------------------------- |
| Backend             | Laravel 13                  |
| Language            | PHP 8.3                     |
| Database            | PostgreSQL 16               |
| Cache               | Redis 7                     |
| Search              | PostgreSQL Full Text Search |
| Infrastructure      | Docker + Docker Compose     |
| Web Server          | Nginx                       |
| Future Search Layer | Elasticsearch 8.x           |

---

# System Architecture

```text
Client Request
      ↓
Laravel API
      ↓
Redis Cache Check
      ↓
PostgreSQL Full Text Search
      ↓
Paginated Response
```

Future Elasticsearch Architecture:

```text
Client Request
      ↓
Laravel API
      ↓
Elasticsearch Query
      ↓
Search Results
      ↓
JSON Response
```

---

# Project Structure

```text
app/
 ├── Console/
 ├── Http/
 │    ├── Controllers/
 │    ├── Requests/
 │    └── Resources/
 ├── Models/
 ├── Services/
 ├── Providers/
 └── Jobs/

config/
database/
routes/
docker/
```

---

# Requirements

## Recommended Environment

* Windows + WSL2 Ubuntu 24.04
* Docker Desktop
* Docker Compose

OR

* Native Linux

---

# Important Performance Note

This project is optimized for Linux filesystem performance.

For best performance:

* Use WSL2 Ubuntu
* Store project inside Linux filesystem

Recommended path:

```bash
~/projects/muvi_ott_search
```

Avoid running Docker bind mounts from:

```text
D:\ or C:\ drives
```

because Windows NTFS bind mounts cause major Laravel filesystem latency.

---

# Installation Guide

## 1. Clone Project

```bash
git clone <repository-url>
```

---

## 2. Open WSL Ubuntu

```bash
wsl
```

---

## 3. Move Project Into Linux Filesystem

Recommended:

```bash
mkdir -p ~/projects
cd ~/projects
```

If project exists on Windows drive:

```bash
cp -r /mnt/d/muvi_ott_search .
```

---

## 4. Open Project

```bash
cd ~/projects/muvi_ott_search
```

---

# Docker Setup

## Start Containers

```bash
docker compose up -d
```

---

## Verify Containers

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

# Laravel Setup

## Install Dependencies

```bash
docker compose exec app composer install
```

---

## Create Environment File

```bash
cp .env.example .env
```

---

## Generate App Key

```bash
docker compose exec app php artisan key:generate
```

---

# Database Setup

## Run Migrations

```bash
docker compose exec app php artisan migrate
```

---

## Run Seeders

```bash
docker compose exec app php artisan db:seed
```

This generates:

* Multiple tenants
* 50K+ OTT contents
* Cast relationships
* Search-ready data

---

# Generate Search Vectors

After seeding, generate PostgreSQL search vectors:

```bash
docker compose exec app php artisan search:generate-vectors
```

This populates:

```text
search_vector
```

for optimized full text search.

---

# Redis Cache Setup

Current cache store:

```env
CACHE_STORE=redis
QUEUE_CONNECTION=redis
```

Redis database usage:

| Purpose | DB |
| ------- | -- |
| Default | 0  |
| Cache   | 1  |

---

# Running the Project

## Start Containers

```bash
docker compose up -d
```

---

## Open API

```text
http://localhost:8000
```

---

# Search API

## Basic Search

```http
GET /search?q=action&tenant_id=1
```

Example:

```text
http://localhost:8000/search?q=action&tenant_id=1
```

---

# Search Filters

## Content Type

```text
&content_type=movie
```

---

## Release Year

```text
&release_year=2020
```

---

## Language

```text
&language=english
```

---

## Minimum Rating

```text
&min_rating=8
```

---

# Full Search Example

```text
http://localhost:8000/search?q=action&tenant_id=1&content_type=movie&min_rating=8
```

---

# Search Implementation Details

## PostgreSQL Full Text Search

Search uses:

```sql
plainto_tsquery
```

and:

```sql
ts_rank
```

for:

* keyword matching
* relevance ranking
* scalable querying

---

# Database Optimization

Implemented indexes:

```sql
GIN(search_vector)
```

Additional indexes:

* tenant_id
* status
* release_year

---

# Cache Strategy

Search responses are cached using Redis.

Benefits:

* Faster repeated searches
* Lower DB load
* Reduced latency
* Better scalability

Example cache key:

```text
search:tenant:1:q:action
```

---

# Checking Redis Cache

## Open Redis CLI

```bash
docker compose exec redis redis-cli
```

---

## Select Cache Database

```bash
SELECT 1
```

---

## View Cache Keys

```bash
KEYS *
```

Expected output:

```text
laravel-database-laravel-cache-search:tenant:1:q:action
```

---

# Performance Benchmarks

## Windows NTFS Bind Mounts

Observed:

| Operation        | Time |
| ---------------- | ---- |
| API request      | 2–7s |
| Artisan commands | 5–7s |

---

## WSL2 Native Linux Filesystem

Observed:

| Operation          | Time  |
| ------------------ | ----- |
| Cached API request | ~85ms |
| Artisan commands   | ~0.8s |

---

# Queue System

Current queue driver:

```env
QUEUE_CONNECTION=redis
```

Future queue usage:

* Elasticsearch indexing
* Async processing
* Search synchronization

---

# Elasticsearch (Planned Phase)

Future improvements include:

* Elasticsearch indexing
* Fuzzy search
* Multi-field weighted search
* Autocomplete
* Search suggestions
* Async indexing jobs

---

# Development Workflow

## Recommended Daily Workflow

### Open Ubuntu

```bash
wsl
```

---

### Open Project

```bash
cd ~/projects/muvi_ott_search
```

---

### Start Docker

```bash
docker compose up -d
```

---

### Open VS Code

```bash
code .
```

---

# Useful Commands

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

## Laravel Optimize Clear

```bash
docker compose exec app php artisan optimize:clear
```

---

## Open Laravel Tinker

```bash
docker compose exec app php artisan tinker
```

---

# Future Improvements

Planned improvements:

* Elasticsearch integration
* Autocomplete
* Search analytics
* Trending searches
* Popular searches
* Query suggestions
* Async indexing workers
* Search metrics
* Rate limiting
* Observability dashboards

---

# Engineering Focus

This assignment focuses heavily on:

* Backend engineering quality
* Scalable architecture
* Search engineering
* Multi-tenant SaaS design
* Performance optimization
* Infrastructure awareness
* Production-oriented thinking

---

# Current Status

## Completed

* Docker infrastructure
* PostgreSQL schema
* Large-scale seeders
* PostgreSQL full text search
* Search ranking
* Redis caching
* Pagination
* Search filters
* Eager loading
* WSL2 optimization
* Production-like local environment

---

# Author Notes

The system is intentionally designed to demonstrate:

* scalable backend engineering
* production architecture
* search optimization strategies
* real-world SaaS design patterns
* infrastructure-aware development workflow
