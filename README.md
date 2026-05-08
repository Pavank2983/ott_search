# MUVI OTT SaaS Search Platform

A scalable multi-tenant OTT search platform built using Laravel, Elasticsearch, PostgreSQL, Redis, Docker, and modern search engineering practices.

The platform is designed to simulate a production-grade OTT SaaS search system where multiple streaming platforms maintain isolated content catalogs while sharing the same infrastructure layer.

The application supports high-volume search indexing, relevance-based querying, metadata filtering, autocomplete suggestions, and scalable multi-tenant architecture patterns.

---

# Project Objectives

This project demonstrates:

- Multi-tenant SaaS architecture
- Elasticsearch-powered search engineering
- Scalable indexing strategies
- Large-scale dataset handling
- Search relevance optimization
- Metadata-based filtering
- Dockerized infrastructure
- Redis caching integration
- Production-oriented backend architecture

---

# Features

## Search Features

- Full-text Elasticsearch search
- Multi-field metadata matching
- Cast / actor search support
- Autocomplete suggestions
- Exact title relevance boosting
- Phrase and fuzzy matching
- Filter-based querying
- Relevance ranking
- Tenant-aware search isolation
- Pagination support

---

## OTT Content Features

- Movie, Series, and Documentary support
- Poster rendering
- Cast metadata support
- Language filtering
- Rating filtering
- Release year filtering
- Tenant-specific content catalogs

---

## Infrastructure Features

- Dockerized development environment
- Redis caching layer
- PostgreSQL relational storage
- Elasticsearch indexing
- Queue-ready architecture
- Optimized large-scale seeders
- Linux/WSL2 optimized workflow

---

# Dataset Information

The platform contains:

| Dataset | Count |
|---|---|
| Tenants | 2 |
| Contents Per Tenant | 50,000+ |
| Total Indexed Contents | 100,000+ |
| Actors / Cast Relationships | Large-scale relational dataset |

The dataset is intentionally designed to simulate realistic OTT platform scale and search load.

---

# Tech Stack

| Layer | Technology |
|---|---|
| Backend Framework | Laravel |
| Language | PHP 8.3 |
| Database | PostgreSQL 16 |
| Search Engine | Elasticsearch 8 |
| Cache Layer | Redis 7 |
| Infrastructure | Docker + Docker Compose |
| Web Server | Nginx |
| Frontend | Blade + Vite + TailwindCSS |

---

# Search Architecture

```text
Client UI
    ↓
Laravel API Layer
    ↓
Search Service
    ↓
Elasticsearch Query Engine
    ↓
Result IDs
    ↓
PostgreSQL Content Retrieval
    ↓
Formatted JSON Response
```

---

# Multi-Tenant Architecture

The platform follows a shared-infrastructure multi-tenant architecture.

Each tenant maintains isolated search results and content catalogs using:

- tenant_id-based filtering
- tenant-aware Elasticsearch queries
- isolated metadata retrieval
- tenant-specific search relevance

Example tenants:

- Netflix
- Amazon Prime

The architecture is designed to scale horizontally while maintaining tenant isolation at the query layer.

---

# Elasticsearch Search Design

The search engine uses Elasticsearch as the primary search layer for:

- Full-text search
- Autocomplete suggestions
- Multi-field matching
- Weighted relevance ranking
- Prefix search
- Phrase matching
- Fuzzy search support

---

## Indexed Metadata

The search index includes:

- title
- description
- actors
- genres
- language
- release year
- content type
- ratings
- search_text aggregation field

---

## Search Relevance Strategy

The search system prioritizes results using weighted ranking strategies:

| Search Strategy | Purpose |
|---|---|
| Exact Match | Highest relevance |
| Phrase Match | Strong contextual relevance |
| Prefix Match | Autocomplete and title matching |
| Multi-field Match | Metadata search |
| Fuzzy Matching | Typo tolerance |

This ensures accurate and user-friendly OTT search behavior.

---

# Search API Examples

## Basic Search

```http
GET /api/search?q=action&tenant_id=1
```

---

## Actor Search

```http
GET /api/search?q=Tom%20Cruise&tenant_id=1
```

---

## Filtered Search

```http
GET /api/search?q=thriller&tenant_id=1&content_type=movie&min_rating=8
```

---

## Autocomplete Suggestions

```http
GET /api/search/suggestions?q=bat&tenant_id=1
```

---

# Performance Optimizations

The platform includes several production-oriented optimizations:

- Elasticsearch indexing
- Redis response caching
- Optimized database queries
- Eager loading relationships
- Reduced payload responses
- Weighted relevance scoring
- Tenant-aware filtering
- Dockerized isolated services
- Lazy-loaded poster rendering

---

# Scalability Considerations

The architecture is designed to support scalable OTT search workloads.

Key scalability considerations include:

- Elasticsearch distributed indexing
- Queue-ready indexing architecture
- Redis caching layer
- Stateless API design
- Search-service abstraction
- Optimized relational queries
- Horizontal container scaling readiness

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
 ├── Jobs/
 └── Providers/

config/
database/
docker/
public/
resources/
routes/
```

---

# Documentation

| File | Purpose |
|---|---|
| README.md | Project overview and architecture |
| SETUP.md | Complete local environment setup instructions |
| WORKFLOW.md | Internal application and search workflow documentation |
| ARCHITECTURE.md | Mermaid Diagram of the architecture |

---

# Development Environment

Recommended environment:

- Windows + WSL2 Ubuntu 24.04
- Docker Desktop
- Linux filesystem-based project storage

Recommended project path:

```bash
~/projects/muvi_ott_search
```

This avoids Docker bind mount performance issues commonly observed on Windows NTFS mounts.

---

# Future Improvements

Potential future enhancements include:

- Semantic / AI-powered search
- Search analytics
- Trending searches
- Search click tracking
- Infinite scroll pagination
- Async Elasticsearch indexing queues
- Search observability dashboards
- Personalized recommendations
- Hybrid vector search

---

# Assignment Coverage

This implementation satisfies the assignment requirements by providing:

- Multi-tenant OTT SaaS architecture
- 50,000+ contents per account
- 100,000+ searchable contents
- Poster rendering
- Cast metadata support
- Metadata-based search
- Scalable search infrastructure
- Production-grade search relevance
- Elasticsearch-powered querying
- Dockerized local environment