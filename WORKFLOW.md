# MUVI OTT SaaS Search Assignment — Architecture & Implementation Roadmap

## 1. Goal of the Assignment

Build a scalable OTT SaaS search system where:

* Multiple OTT accounts (tenants) exist
* Each account has 50,000+ contents
* Users can search across all metadata fields
* Search should be fast, scalable, and production-oriented
* The project should demonstrate backend engineering quality, architecture thinking, and maintainability

---

# 2. Recommended Tech Stack

## Backend

* Laravel 11
* PHP 8.3+

## Database

* PostgreSQL

Why PostgreSQL:

* Better indexing capabilities
* JSONB support for metadata
* Better scaling and search flexibility
* Production-grade relational database

## Search Engine

* Elasticsearch 8.x

Why Elasticsearch:

* Designed for high-performance search
* Multi-field search
* Fuzzy search
* Ranking/relevance support
* Handles 100K+ records efficiently
* Industry-standard for OTT/content platforms

## Queue System

* Redis + Laravel Queue

Why:

* Async indexing
* Better scalability
* Avoid blocking requests

## Frontend

* Blade OR minimal React/Next.js frontend

Recommendation:
Keep frontend simple.
This assignment is backend/search focused.

## Containerization

* Docker + Docker Compose

Services:

* app
* nginx
* postgres
* redis
* elasticsearch

---

# 3. High-Level Architecture

```text
User Search Request
        ↓
Laravel API
        ↓
Elasticsearch Query
        ↓
Search Results
        ↓
Metadata fetched from DB (optional)
        ↓
Response
```

Content creation/update flow:

```text
Content Created/Updated
        ↓
Laravel Event
        ↓
Queue Job
        ↓
Index into Elasticsearch
```

This demonstrates:

* Scalability
* Event-driven architecture
* Async processing
* Reliability

---

# 4. Multi-Tenant SaaS Design

The assignment explicitly mentions:

> 2 accounts with 50K+ content each

This means:

* Tenant isolation is important
* Queries must be scoped by account

---

## Recommended Tenant Strategy

### Single DB + tenant_id

Every content row contains:

```text
tenant_id
```

Benefits:

* Simpler implementation
* Easier scaling
* Easier querying
* Production-friendly

---

# 5. Database Schema

## tenants

| Column     | Type      |
| ---------- | --------- |
| id         | bigint    |
| name       | string    |
| created_at | timestamp |

---

## contents

| Column       | Type      |
| ------------ | --------- |
| id           | bigint    |
| tenant_id    | bigint    |
| title        | string    |
| slug         | string    |
| description  | text      |
| poster_url   | string    |
| release_year | integer   |
| language     | string    |
| genre        | string    |
| rating       | decimal   |
| metadata     | jsonb     |
| created_at   | timestamp |

Indexes:

* tenant_id
* slug
* release_year

---

## casts

| Column | Type   |
| ------ | ------ |
| id     | bigint |
| name   | string |

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

# 6. Why NOT Store Everything in SQL Search?

Avoid:

```sql
LIKE '%keyword%'
```

Reasons:

* Slow at scale
* Poor ranking
* Bad fuzzy search
* Doesn't scale well for 100K+ records

Using Elasticsearch shows:

* Senior-level thinking
* Real-world OTT architecture

---

# 7. Elasticsearch Design

## Index Structure

Recommended index:

```text
ott_contents
```

Each document:

```json
{
  "id": 1,
  "tenant_id": 1,
  "title": "Avengers",
  "description": "Marvel movie",
  "casts": ["Robert Downey Jr"],
  "genre": "Action",
  "language": "English"
}
```

---

# 8. Search Query Strategy

Use:

## multi_match query

Search across:

* title
* description
* casts
* genre
* metadata

Example:

```json
{
  "query": {
    "bool": {
      "must": [
        {
          "multi_match": {
            "query": "avengers",
            "fields": [
              "title^3",
              "casts^2",
              "description",
              "genre"
            ],
            "fuzziness": "AUTO"
          }
        }
      ],
      "filter": [
        {
          "term": {
            "tenant_id": 1
          }
        }
      ]
    }
  }
}
```

---

# 9. Performance Optimizations

## MUST IMPLEMENT

### 1. Elasticsearch Indexing

Search should never hit SQL directly.

---

### 2. Pagination

API:

```text
/api/search?page=1&limit=20
```

Never return all results.

---

### 3. Async Indexing

When content updates:

* Dispatch queue job
* Update ES asynchronously

Avoid blocking writes.

---

### 4. Redis Queue

Use Redis for:

* Queue workers
* Better reliability
* Faster processing

---

### 5. DB Indexes

Indexes on:

* tenant_id
* slug
* release_year

---

### 6. Avoid N+1 Queries

Always eager load:

```php
Content::with('casts')
```

---

### 7. Response Optimization

Return only required fields.

Avoid:

* huge payloads
* unnecessary metadata

---

# 10. Reliability Considerations

## Queue Retry Strategy

Configure:

* retries
* timeout
* failed_jobs table

This demonstrates:

* production thinking
* resilience

---

## Logging

Use Laravel logging for:

* indexing failures
* search failures

---

## Graceful Error Handling

Return:

```json
{
  "message": "Search failed"
}
```

Avoid exposing internal errors.

---

# 11. Seeder Strategy (IMPORTANT)

The assignment explicitly asks:

> 50K+ contents per account

---

## Recommended Approach

Create:

### TenantSeeder

Creates:

* Account A
* Account B

### ContentSeeder

Generates:

* 50,000 contents per tenant

Use:

* Faker
* Chunk inserts

Avoid inserting one-by-one.

---

## Seeder Optimization

Use:

```php
Content::insert($batch)
```

Batch size:

```text
1000 rows
```

This massively improves performance.

---

# 12. APIs to Build

## Search API

```text
GET /api/tenants/{tenant}/search?q=avengers
```

Response:

```json
{
  "data": [
    {
      "title": "Avengers",
      "poster": "...",
      "casts": ["Robert Downey Jr"],
      "genre": "Action"
    }
  ]
}
```

---

## Optional APIs

### Content Details

```text
GET /api/contents/{id}
```

### Reindex API

```text
POST /api/reindex
```

Good bonus feature.

---

# 13. Suggested Folder Structure

```text
app/
 ├── Actions/
 ├── DTOs/
 ├── Services/
 ├── Repositories/
 ├── Jobs/
 ├── Events/
 ├── Listeners/
 ├── Http/
```

This demonstrates clean architecture.

---

# 14. Recommended Packages

## Elasticsearch

Recommended:

```text
elastic/elasticsearch
```

OR Laravel Scout + Elasticsearch driver.

Recommendation:
Use direct Elasticsearch client for better control.

---

# 15. Docker Compose Setup

Include:

```yaml
services:
  app:
  nginx:
  postgres:
  redis:
  elasticsearch:
```

This significantly improves professionalism.

---

# 16. README Structure (VERY IMPORTANT)

## Sections

### 1. Project Overview

Explain:

* multi-tenant OTT search system
* scalable architecture

---

### 2. Tech Stack

Explain why:

* Laravel
* PostgreSQL
* Elasticsearch
* Redis

---

### 3. Setup Instructions

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan queue:work
```

Docker setup:

```bash
docker-compose up -d
```

---

### 4. Seeder Details

Mention:

* 50K records per tenant
* generated via optimized batch seeding

---

### 5. Architecture Decisions

Explain:

* why Elasticsearch
* why async indexing
* why queues
* tenant isolation strategy

---

### 6. Performance Considerations

Mention:

* indexing
* pagination
* eager loading
* async processing

---

### 7. Future Improvements

Examples:

* autocomplete
* trending searches
* analytics
* search suggestions
* cache layer

---