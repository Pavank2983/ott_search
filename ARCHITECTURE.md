# MUVI OTT Search Platform — Architecture Diagram

## Search Request Flow

```mermaid
flowchart TD

    A[Client Browser]

    A --> B[Laravel Application]

    B --> C[Search Controller]

    C --> D[Search Service]

    D --> E[Redis Cache Check]

    E -->|Cache Hit| F[Cached Response]

    E -->|Cache Miss| G[Elasticsearch]

    G --> H[Matched Content IDs]

    H --> I[PostgreSQL]

    I --> J[Hydrated Content Data]

    J --> K[Formatted JSON Response]

    K --> A
```

---

## Elasticsearch Indexing Flow

```mermaid
flowchart TD

    A[PostgreSQL Contents]

    A --> B[Laravel Import Command]

    B --> C[Metadata Aggregation]

    C --> D[Actor Aggregation]

    D --> E[Elasticsearch Bulk Index]

    E --> F[Searchable OTT Index]
```

---

## Multi-Tenant Search Isolation

```mermaid
flowchart LR

    A[Netflix Tenant]

    B[Amazon Prime Tenant]

    A --> C[tenant_id = 1]

    B --> D[tenant_id = 2]

    C --> E[Elasticsearch Query Filter]

    D --> E

    E --> F[Isolated Search Results]
```

---

## Docker Infrastructure

```mermaid
flowchart LR

    A[Client Browser]

    A --> B[Nginx]

    B --> C[Laravel App]

    C --> D[PostgreSQL]

    C --> E[Redis]

    C --> F[Elasticsearch]
```

---

## Search Relevance Strategy

```mermaid
flowchart TD

    A[User Query]

    A --> B[Exact Match Boost]

    A --> C[Phrase Match]

    A --> D[Prefix Match]

    A --> E[Fuzzy Match]

    B --> F[Elasticsearch Scoring]

    C --> F

    D --> F

    E --> F

    F --> G[Ranked OTT Results]
```