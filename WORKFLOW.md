# MUVI OTT SaaS Search Assignment — Workflow & Implementation Progress

## Project Goal

Build a scalable multi-tenant OTT search platform capable of handling:

* 50K+ contents per tenant
* Fast search responses
* Production-oriented architecture
* Async indexing pipelines
* Scalable backend design
* Optimized database querying
* Search caching

The assignment focuses heavily on:

* Backend architecture
* Scalability
* Search engineering
* Performance optimization
* Clean implementation
* Dockerized infrastructure

---

# Current Tech Stack

## Backend

* Laravel 13
* PHP 8.3

## Database

* PostgreSQL 16

## Search Layer

* PostgreSQL Full Text Search (Current Phase)
* Elasticsearch 8.x (Planned Next Phase)

## Cache & Queue

* Redis 7
* Laravel Cache
* Laravel Queue

## Infrastructure

* Docker
* Docker Compose
* Nginx
* WSL2 Ubuntu 24.04

---

# Architecture Overview

```text
Client Request
      ↓
Laravel API
      ↓
Redis Cache Check
      ↓
PostgreSQL Full Text Search
      ↓
Eager Loaded Relations
      ↓
Paginated JSON Response
```

Future Elasticsearch Flow:

```text
Search Request
      ↓
Laravel API
      ↓
Elasticsearch Query
      ↓
Search Results
      ↓
Optional DB Hydration
      ↓
JSON Response
```

---

# Multi-Tenant Design

The system is designed as a SaaS OTT platform.

Each content row belongs to a tenant.

```text
tenant_id
```

Benefits:

* Easy tenant isolation
* Scalable querying
* Simple architecture
* Production-friendly design

---

# Database Schema

## tenants

| Column     | Type      |
| ---------- | --------- |
| id         | bigint    |
| name       | string    |
| created_at | timestamp |

---

## contents

| Column        | Type      |
| ------------- | --------- |
| id            | bigint    |
| tenant_id     | bigint    |
| title         | string    |
| slug          | string    |
| description   | text      |
| content_type  | string    |
| release_year  | integer   |
| language      | string    |
| genres        | jsonb     |
| poster_url    | string    |
| imdb_rating   | decimal   |
| search_text   | text      |
| search_vector | tsvector  |
| status        | string    |
| created_at    | timestamp |

Indexes:

* tenant_id
* status
* release_year
* search_vector (GIN)

---

## casts

| Column | Type   |
| ------ | ------ |
| id     | bigint |
| name   | string |
| slug   | string |

---

## content_cast

| Column     | Type   |
| ---------- | ------ |
| content_id | bigint |
| cast_id    | bigint |

Indexes:

* content_id
* cast_id

---

# Current Search Implementation

## PostgreSQL Full Text Search

Current implementation uses:

```sql
plainto_tsquery
```

and:

```sql
ts_rank
```

for:

* keyword matching
* ranking
* relevance sorting

Search query example:

```sql
search_vector @@ plainto_tsquery('english', ?)
```

Ranking:

```sql
ts_rank(search_vector, plainto_tsquery('english', ?))
```

---

# Current Search Features

Implemented:

* Full text search
* Relevance ranking
* Multi-tenant filtering
* Pagination
* Redis caching
* Eager loading
* Search filters
* Optimized JSON response
* Search vector indexing

---

# Search Filters

Implemented filters:

* content_type
* release_year
* language
* minimum rating

Example:

```text
/search?q=action&tenant_id=1&content_type=movie
```

---

# Redis Cache Layer

Search responses are cached using Redis.

Current implementation:

```php
Cache::remember(...)
```

Benefits:

* Reduced DB load
* Faster repeated searches
* Lower query latency
* Better scalability

Observed performance:

| Scenario       | Response Time |
| -------------- | ------------- |
| Cold request   | ~500ms–1s     |
| Cached request | ~80ms         |

---

# Performance Optimization Work

## Implemented

### 1. PostgreSQL Full Text Search

Avoided:

```sql
LIKE '%keyword%'
```

Reason:

* Poor scalability
* Slow scans
* No ranking
* Inefficient indexing

---

### 2. GIN Indexing

Implemented:

```sql
CREATE INDEX contents_search_vector_idx
ON contents
USING GIN(search_vector)
```

---

### 3. Pagination

Implemented:

```text
?page=1
```

Avoids loading large datasets.

---

### 4. Eager Loading

Implemented:

```php
->with('actors:id,name')
```

Avoids N+1 query issues.

---

### 5. Response Optimization

Only required fields are returned.

Reduces payload size.

---

### 6. Redis Search Cache

Implemented cache key strategy:

```text
search:tenant:{id}:q:{query}
```

Supports filter-aware caching.

---

# Important WSL2 Performance Discovery

## Initial Problem

Docker + Laravel performance was extremely slow.

Observed:

* 5–7 second requests
* Very slow artisan commands
* Slow container filesystem operations

---

## Root Cause

Project was located on Windows filesystem:

```text
/mnt/d/
```

Docker bind mounts from Windows NTFS into Linux containers caused major filesystem overhead.

---

## Solution

Moved project into native WSL2 Linux filesystem:

```text
~/projects/muvi_ott_search
```

---

## Result

Performance improved dramatically.

Before:

| Operation     | Time |
| ------------- | ---- |
| artisan about | 5–7s |
| API response  | 2–7s |

After:

| Operation           | Time  |
| ------------------- | ----- |
| artisan about       | ~0.8s |
| Cached API response | ~85ms |

---

# Recommended Development Workflow

## Start Ubuntu WSL

```bash
wsl
```

---

## Open Project

```bash
cd ~/projects/muvi_ott_search
```

---

## Start Docker

```bash
docker compose up -d
```

---

## Open VS Code

```bash
code .
```

This ensures:

* Native Linux filesystem performance
* Fast Docker bind mounts
* Faster Composer
* Faster NPM
* Better Laravel performance

---

# Docker Services

Current services:

```yaml
services:
  app:
  nginx:
  postgres:
  redis:
  elasticsearch:
```

---

# Seeder Strategy

Implemented scalable seeders:

* TenantSeeder
* ContentSeeder
* CastSeeder
* ContentCastSeeder

Seed volume:

* 50K+ contents per tenant

Optimization:

```php
Model::insert($batch)
```

Batch inserts improve seeding performance significantly.

---

# API Endpoints

## Search API

```text
GET /search?q=action&tenant_id=1
```

---

## Search With Filters

```text
GET /search?q=action&tenant_id=1&content_type=movie
```

---

# Current Folder Structure

```text
app/
 ├── Console/
 ├── Http/
 │    ├── Controllers/
 │    ├── Requests/
 │    └── Resources/
 ├── Models/
 ├── Services/
```

---

# Current Laravel Components

Implemented:

* SearchController
* SearchRequest
* SearchService
* ContentResource
* GenerateSearchVectors command

---

# Current Status

## Completed

* Docker infrastructure
* PostgreSQL schema
* Seeder system
* Full text search
* Search ranking
* Multi-tenant filtering
* Redis caching
* Search filters
* Pagination
* Eager loading
* Search vector indexing
* WSL2 optimization
* Production-like local environment

---

# Next Planned Phase

## Elasticsearch Integration

Planned:

* Elasticsearch indexing
* Async indexing jobs
* Queue workers
* Event-driven indexing
* Fuzzy search
* Multi-field weighted search
* Search suggestions
* Reindex commands

---

# Future Improvements

Planned improvements:

* Autocomplete
* Search analytics
* Trending searches
* Popular search cache
* Search suggestions
* Elasticsearch synonyms
* Queue retry handling
* Circuit breaker strategy
* API rate limiting
* Observability & metrics

---

# Key Engineering Decisions

## Why PostgreSQL?

* Strong indexing support
* JSONB support
* Full text search support
* Production-grade reliability

---

## Why Redis?

* Fast caching
* Queue support
* Reduces DB load
* Better scalability

---

## Why WSL2 Linux Filesystem?

* Native Linux performance
* Faster Docker bind mounts
* Lower Laravel filesystem overhead
* Better development experience

---

## Why Full Text Search?

Compared to:

```sql
LIKE '%query%'
```

Benefits:

* Better performance
* Ranking support
* Scalable indexing
* Production-ready search

---

## Why Elasticsearch Later?

PostgreSQL FTS is excellent for:

* initial implementation
* moderate scale
* assignment MVP

Elasticsearch is better for:

* fuzzy search
* autocomplete
* typo tolerance
* large-scale distributed search
* advanced ranking
* analytics

---

# Final Notes

This implementation focuses heavily on:

* backend engineering quality
* scalability
* production architecture
* search optimization
* maintainability
* performance engineering
* infrastructure awareness

The current system is now:

* production-oriented
* scalable
* cache-optimized
* Dockerized
* multi-tenant ready
* extensible toward Elasticsearch
