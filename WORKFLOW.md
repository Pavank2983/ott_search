# WORKFLOW.md

# MUVI OTT SaaS Search Platform — System Workflow & Architecture

This document explains the internal architecture, request lifecycle, indexing workflow, search implementation strategy, and engineering decisions behind the MUVI OTT SaaS Search Platform.

The purpose of this document is to describe how the platform processes search requests, manages tenant isolation, indexes OTT content, and scales search operations using Elasticsearch and Dockerized infrastructure.

---

# System Objectives

The platform is designed to support:

- Multi-tenant OTT SaaS architecture
- 50,000+ contents per tenant
- 100,000+ searchable contents
- Elasticsearch-powered search
- Metadata-based filtering
- Relevance-based ranking
- Actor and cast search
- Autocomplete suggestions
- Scalable indexing workflows
- Production-oriented backend architecture

---

# High-Level Architecture

```text
Client Browser
      ↓
Laravel Application
      ↓
Search Controller
      ↓
Search Service
      ↓
Elasticsearch Query Engine
      ↓
Matched Content IDs
      ↓
PostgreSQL Data Retrieval
      ↓
JSON Response
      ↓
Frontend Rendering
```

---

# Search Request Lifecycle

The search request lifecycle follows a service-oriented architecture.

---

## Step 1 — Client Search Request

The frontend sends a request containing:

- search keyword
- tenant ID
- filters
- pagination

Example:

```http
GET /api/search?q=thriller&tenant_id=1&content_type=movie&page=1
```

---

## Step 2 — Request Validation

Laravel validates:

- tenant ID
- pagination values
- filter inputs
- search query parameters

Validation is handled using dedicated request classes.

---

## Step 3 — Search Service Layer

The request is delegated to:

```text
SearchService
```

Responsibilities:

- request normalization
- cache management
- Elasticsearch integration
- result transformation
- pagination handling

---

## Step 4 — Elasticsearch Query Execution

The search service executes Elasticsearch queries using:

```text
ElasticSearchService
```

Search capabilities include:

- exact title matching
- phrase matching
- prefix matching
- fuzzy matching
- actor search
- metadata matching
- weighted relevance scoring

---

## Step 5 — Multi-Tenant Filtering

Every Elasticsearch query includes tenant-aware filtering:

```text
tenant_id
```

This guarantees tenant-level content isolation across the shared infrastructure.

---

## Step 6 — Search Result Retrieval

Elasticsearch returns:

- matched document IDs
- relevance scores
- indexed metadata

The platform retrieves relational content data from PostgreSQL using the matched IDs.

---

## Step 7 — Database Hydration

PostgreSQL retrieves:

- titles
- posters
- descriptions
- ratings
- actors
- metadata relationships

Laravel eager loading is used to avoid N+1 query issues.

Example:

```php
->with('actors:id,name')
```

---

## Step 8 — JSON Response Formatting

The final response is returned as structured JSON containing:

- paginated content data
- metadata
- search totals
- pagination details

---

# Elasticsearch Architecture

The platform uses Elasticsearch as the primary search engine.

---

## Indexed Fields

The Elasticsearch index contains:

- title
- description
- actors
- genres
- language
- content_type
- release_year
- imdb_rating
- search_text
- tenant_id

---

## Search Relevance Strategy

Search ranking uses weighted scoring strategies.

| Query Strategy | Purpose |
|---|---|
| Exact Match | Highest priority |
| Phrase Match | Strong contextual relevance |
| Prefix Match | Autocomplete behavior |
| Multi-field Match | Metadata searching |
| Fuzzy Matching | Typo tolerance |

---

## Search Features

Implemented search capabilities:

- Full-text search
- Actor search
- Multi-field metadata search
- Exact title prioritization
- Autocomplete suggestions
- Tenant-aware filtering
- Filter-based search
- Relevance sorting

---

# Elasticsearch Suggestion Workflow

Autocomplete suggestions follow a lightweight query path.

Workflow:

```text
Search Input
      ↓
Suggestion API
      ↓
Elasticsearch Suggestion Query
      ↓
Top Matching Titles
      ↓
Frontend Suggestion Dropdown
```

Suggestions prioritize:

- exact title matches
- prefix matches
- phrase relevance
- search score

---

# Multi-Tenant SaaS Design

The platform follows a shared-database multi-tenant architecture.

Each content item belongs to a tenant using:

```text
tenant_id
```

Benefits:

- tenant isolation
- scalable querying
- lower infrastructure cost
- simplified architecture
- centralized indexing strategy

---

# Database Architecture

PostgreSQL is used for relational data storage.

---

## Primary Tables

### tenants

Stores OTT platform accounts.

---

### contents

Stores OTT media content:

- movies
- series
- documentaries

---

### actors

Stores cast metadata.

---

### content_actor

Pivot table connecting:

- contents
- actors

---

# Indexing Workflow

The indexing workflow imports relational PostgreSQL data into Elasticsearch.

---

## Index Creation

The Elasticsearch index is created using:

```php
createIndex()
```

Mappings define:

- searchable fields
- keyword fields
- relevance behavior
- filtering fields

---

## Bulk Import Workflow

The import command:

```bash
php artisan search:import-content
```

performs:

```text
PostgreSQL Content Retrieval
      ↓
Metadata Aggregation
      ↓
Actor Aggregation
      ↓
Elasticsearch Bulk Indexing
```

---

## Indexed Search Data

Each indexed document includes:

```json
{
  "title": "",
  "description": "",
  "actors": "",
  "genres": [],
  "language": "",
  "tenant_id": 1
}
```

---

# Caching Strategy

Redis is used as the caching layer.

Search responses are cached using filter-aware cache keys.

Example:

```text
search:tenant:1:q:action:page:1
```

Benefits:

- lower Elasticsearch load
- reduced response latency
- faster repeated searches
- improved scalability

---

# Pagination Strategy

The platform uses paginated search results.

Pagination benefits:

- lower payload size
- reduced frontend rendering overhead
- lower memory consumption
- scalable result retrieval

---

# Performance Optimizations

The platform includes several production-oriented optimizations.

---

## Elasticsearch Indexing

Optimized indexed metadata improves:

- relevance scoring
- filtering speed
- search accuracy

---

## Eager Loading

Laravel eager loading prevents N+1 queries.

Example:

```php
->with('actors:id,name')
```

---

## Reduced Payload Responses

Only required fields are returned from the API.

Benefits:

- lower bandwidth usage
- faster response serialization
- improved frontend rendering

---

## Redis Response Caching

Frequently repeated searches are cached to reduce Elasticsearch query overhead.

---

## Dockerized Infrastructure

All services run in isolated containers:

- Laravel
- PostgreSQL
- Redis
- Elasticsearch
- Nginx

Benefits:

- environment consistency
- reproducible local setup
- isolated dependencies
- scalable deployment readiness

---

# Frontend Search Workflow

Frontend search behavior includes:

- debounced search input
- URL state synchronization
- autocomplete suggestions
- tenant switching
- dynamic filters
- paginated rendering

---

# Development Workflow

Recommended development workflow:

```bash
wsl
cd ~/projects/muvi_ott_search
docker compose up -d
code .
```

Recommended environment:

- WSL2 Ubuntu 24.04
- Linux filesystem-based project storage

Recommended path:

```bash
~/projects/muvi_ott_search
```

This improves Docker bind mount performance significantly compared to Windows NTFS mounts.

---

# Current System Status

Implemented components:

- Multi-tenant OTT architecture
- Elasticsearch integration
- Redis caching
- Actor search
- Search suggestions
- Metadata filtering
- Dockerized infrastructure
- Elasticsearch indexing
- Large-scale seeders
- Weighted relevance ranking
- Paginated search responses

---

# Scalability Considerations

The architecture is designed for scalable OTT search workloads.

Key scalability considerations:

- Elasticsearch distributed indexing
- queue-ready indexing workflows
- stateless API architecture
- isolated infrastructure services
- scalable caching layer
- large dataset handling
- service-oriented search architecture

---

# Future Improvements

Potential future enhancements include:

- Async indexing queues
- Search analytics
- Trending searches
- Search click tracking
- Semantic/vector search
- Personalized recommendations
- Elasticsearch synonyms
- Infinite scroll search
- Search observability dashboards

---

# Engineering Focus

The platform focuses heavily on:

- backend engineering quality
- scalable architecture
- production-oriented design
- search optimization
- infrastructure awareness
- maintainability
- search relevance engineering
- multi-tenant SaaS patterns