<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        MUVI OTT Search Platform
    </title>

    <meta name="description"
        content="Scalable OTT SaaS search platform powered by Laravel, Elasticsearch, Redis queues, and PostgreSQL.">

    <meta name="keywords" content="OTT Search, Elasticsearch, Laravel, Redis, PostgreSQL, Streaming Platform">

    <meta name="robots" content="index, follow">

    <meta property="og:title" content="MUVI OTT Search Platform">

    <meta property="og:description" content="Production-grade OTT SaaS search architecture using Elasticsearch.">

    <meta property="og:type" content="website">

    <meta name="theme-color" content="#020617">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="bg-slate-950 text-white antialiased">

    <main class="min-h-screen">

        <!-- Hero -->
        <section class="border-b border-slate-800 bg-slate-900/80 backdrop-blur">

            <div class="max-w-7xl mx-auto px-6 py-10">

                <div class="max-w-4xl">

                    <h1 class="text-4xl lg:text-5xl font-black leading-tight tracking-tight">
                        MUVI OTT Search Platform
                    </h1>

                    <p class="mt-6 text-slate-400 text-lg leading-relaxed max-w-3xl">
                        Production-grade multi-tenant OTT search platform powered by
                        Laravel, Elasticsearch, Redis queues, PostgreSQL, and Dockerized infrastructure.
                    </p>

                </div>

            </div>

        </section>

        <!-- Sticky Search -->
        <section id="searchSection"
            class="sticky top-0 z-40 border-b border-slate-800 bg-slate-950/90 backdrop-blur-xl">

            <div class="max-w-7xl mx-auto px-6 py-5">

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-2xl">

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

                        <!-- Search -->
                        <div class="lg:col-span-5">

                            <label class="block text-sm text-slate-400 mb-2">
                                Search Content
                            </label>

                            <div class="relative">

                                <!-- Search Icon -->
                                <svg class="absolute left-4 top-5 w-5 h-5 text-slate-500 z-10" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>

                                <!-- Search Input -->
                                <input id="searchInput" type="text" autocomplete="off"
                                    placeholder="Search movies, series..."
                                    class="
                                    w-full
                                    bg-slate-950
                                    border
                                    border-slate-700
                                    rounded-2xl
                                    pl-12
                                    pr-5
                                    py-4
                                    outline-none
                                    focus:border-indigo-500
                                    transition
                                ">

                                <!-- Suggestions -->
                                <div id="searchSuggestions"
                                    class="
                                    hidden
                                    absolute
                                    top-full
                                    left-0
                                    right-0
                                    mt-2
                                    bg-slate-900
                                    border
                                    border-slate-800
                                    rounded-2xl
                                    overflow-hidden
                                    shadow-2xl
                                    z-50
                                    max-h-[420px]
                                    overflow-y-auto
                                ">
                                </div>

                            </div>

                        </div>

                        <!-- Tenant -->
                        <div class="lg:col-span-2">

                            <label class="block text-sm text-slate-400 mb-2">
                                Platform
                            </label>

                            <select id="tenantId"
                                class="w-full bg-slate-950 border border-slate-700 rounded-2xl px-5 py-4 outline-none focus:border-indigo-500 transition">

                                <option value="">
                                    Loading Platforms...
                                </option>

                            </select>

                        </div>

                        <!-- Type -->
                        <div class="lg:col-span-2">

                            <label class="block text-sm text-slate-400 mb-2">
                                Content Type
                            </label>

                            <select id="contentType"
                                class="w-full bg-slate-950 border border-slate-700 rounded-2xl px-5 py-4 outline-none focus:border-indigo-500 transition">

                                <option value="">
                                    All Types
                                </option>

                                <option value="movie">
                                    Movie
                                </option>

                                <option value="series">
                                    Series
                                </option>

                                <option value="documentary">
                                    Documentary
                                </option>

                            </select>

                        </div>

                        <!-- Rating -->
                        <div class="lg:col-span-2">

                            <label class="block text-sm text-slate-400 mb-2">
                                Minimum Rating
                            </label>

                            <select id="minRating"
                                class="w-full bg-slate-950 border border-slate-700 rounded-2xl px-5 py-4 outline-none focus:border-indigo-500 transition">

                                <option value="">
                                    Any Rating
                                </option>

                                <option value="5">
                                    5+
                                </option>

                                <option value="6">
                                    6+
                                </option>

                                <option value="7">
                                    7+
                                </option>

                                <option value="8">
                                    8+
                                </option>

                            </select>

                        </div>

                    </div>

                    <!-- Active Filters -->
                    <div id="activeFilters" class="hidden flex flex-wrap gap-2 mt-5"></div>

                </div>

            </div>

        </section>

        <!-- Results -->
        <section class="max-w-[1800px] mx-auto px-6 pt-8 pb-40">

            <!-- Loading -->
            <div id="loading" class="hidden">

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">

                    @for ($i = 0; $i < 10; $i++)
                        <div class="animate-pulse rounded-3xl overflow-hidden border border-slate-800 bg-slate-900">

                            <div class="aspect-[2/3] bg-slate-800"></div>

                            <div class="p-4">

                                <div class="h-6 bg-slate-800 rounded mb-4"></div>

                                <div class="h-4 bg-slate-800 rounded mb-2"></div>

                                <div class="h-4 bg-slate-800 rounded w-2/3"></div>

                            </div>

                        </div>
                    @endfor

                </div>

            </div>

            <!-- Empty -->
            <div id="emptyState" class="hidden text-center py-28">

                <div class="max-w-md mx-auto">

                    <div class="text-7xl mb-6">
                        🎬
                    </div>

                    <h2 class="text-3xl font-bold mb-3">
                        No Contents Found
                    </h2>

                    <p class="text-slate-400">
                        Try different search keywords or filters.
                    </p>

                </div>

            </div>

            <!-- Meta -->
            <div id="resultsMeta" class="hidden items-center justify-between mb-6 text-sm text-slate-400">

                <div class="flex items-center gap-3">

                    <div class="w-2 h-2 rounded-full bg-emerald-400"></div>

                    <p id="resultsCount"></p>

                </div>

                <p>
                    Elasticsearch Cursor Search
                </p>

            </div>

            <!-- Grid -->
            <div id="results"
                class="
                    grid
                    grid-cols-2
                    sm:grid-cols-3
                    md:grid-cols-4
                    lg:grid-cols-5
                    gap-4
                ">
            </div>

        </section>

        <!-- Fixed Pagination -->
        <div id="paginationWrapper"
            class="hidden fixed bottom-0 left-0 right-0 z-50 border-t border-slate-800 bg-slate-950/95 backdrop-blur-xl">

            <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between gap-4">

                <div id="paginationInfo" class="text-sm text-slate-400"></div>

                <div id="pagination" class="flex items-center gap-2"></div>

            </div>

        </div>

    </main>

</body>

</html>
