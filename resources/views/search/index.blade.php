<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTT Search</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen">

    <div class="max-w-7xl mx-auto px-6 py-10">

        <div class="mb-8">
            <h1 class="text-4xl font-bold mb-2">
                OTT Search Platform
            </h1>

            <p class="text-slate-400">
                Elasticsearch Powered Content Discovery
            </p>
        </div>

        <!-- Search + Filters -->
        <div class="bg-slate-800 rounded-2xl p-6 mb-8">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                <input
                    id="searchInput"
                    type="text"
                    placeholder="Search movies, series..."
                    class="md:col-span-2 bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 outline-none"
                >

                <select
                    id="contentType"
                    class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-3"
                >
                    <option value="">All Types</option>
                    <option value="movie">Movie</option>
                    <option value="series">Series</option>
                    <option value="documentary">Documentary</option>
                </select>

                <select
                    id="minRating"
                    class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-3"
                >
                    <option value="">Min Rating</option>
                    <option value="5">5+</option>
                    <option value="6">6+</option>
                    <option value="7">7+</option>
                    <option value="8">8+</option>
                </select>

            </div>
        </div>

        <!-- Loading -->
        <div
            id="loading"
            class="hidden justify-center py-10"
        >
            <div class="loader"></div>
        </div>

        <!-- Empty -->
        <div
            id="emptyState"
            class="hidden text-center py-16 text-slate-400"
        >
            No contents found.
        </div>

        <!-- Results -->
        <div
            id="results"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
        ></div>

        <!-- Pagination -->
        <div
            id="pagination"
            class="flex justify-center items-center gap-4 mt-10"
        ></div>

    </div>

    <script>

        let currentPage = 1;

        const searchInput = document.getElementById('searchInput');
        const contentType = document.getElementById('contentType');
        const minRating = document.getElementById('minRating');

        const resultsContainer = document.getElementById('results');
        const paginationContainer = document.getElementById('pagination');

        const loading = document.getElementById('loading');
        const emptyState = document.getElementById('emptyState');

        let debounceTimer;

        async function fetchContents(page = 1) {

            currentPage = page;

            loading.classList.remove('hidden');
            loading.classList.add('flex');

            emptyState.classList.add('hidden');

            resultsContainer.innerHTML = '';
            paginationContainer.innerHTML = '';

            const params = new URLSearchParams({
                q: searchInput.value,
                tenant_id: 1,
                page: page,
            });

            if (contentType.value) {
                params.append('content_type', contentType.value);
            }

            if (minRating.value) {
                params.append('min_rating', minRating.value);
            }

            const response = await fetch(
                `/api/search?${params.toString()}`
            );

            const data = await response.json();

            loading.classList.add('hidden');

            if (!data.data.length) {
                emptyState.classList.remove('hidden');
                return;
            }

            renderContents(data.data);

            renderPagination(
                data.current_page,
                data.last_page
            );
        }

        function renderContents(contents) {

            contents.forEach(content => {

                const card = document.createElement('div');

                card.className =
                    'bg-slate-800 rounded-2xl overflow-hidden border border-slate-700';

                card.innerHTML = `
                    <div class="h-52 bg-slate-900 flex items-center justify-center">
                        <span class="text-slate-500 text-sm">
                            No Poster
                        </span>
                    </div>

                    <div class="p-5">

                        <div class="flex items-center justify-between mb-3">

                            <h2 class="text-xl font-semibold">
                                ${content.title}
                            </h2>

                            <span class="text-yellow-400 font-bold">
                                ⭐ ${content.imdb_rating ?? 'N/A'}
                            </span>

                        </div>

                        <p class="text-slate-400 text-sm mb-4 line-clamp-3">
                            ${content.description ?? 'No description'}
                        </p>

                        <div class="flex flex-wrap gap-2">

                            <span class="bg-slate-700 px-3 py-1 rounded-full text-xs">
                                ${content.content_type}
                            </span>

                            <span class="bg-slate-700 px-3 py-1 rounded-full text-xs">
                                ${content.release_year ?? 'N/A'}
                            </span>

                        </div>

                    </div>
                `;

                resultsContainer.appendChild(card);
            });
        }

        function renderPagination(currentPage, lastPage) {

            if (lastPage <= 1) {
                return;
            }

            if (currentPage > 1) {

                const prevButton = document.createElement('button');

                prevButton.innerText = 'Previous';

                prevButton.className =
                    'bg-slate-700 hover:bg-slate-600 px-4 py-2 rounded-lg';

                prevButton.onclick = () => fetchContents(currentPage - 1);

                paginationContainer.appendChild(prevButton);
            }

            const pageText = document.createElement('span');

            pageText.className = 'text-slate-300';

            pageText.innerText =
                `Page ${currentPage} of ${lastPage}`;

            paginationContainer.appendChild(pageText);

            if (currentPage < lastPage) {

                const nextButton = document.createElement('button');

                nextButton.innerText = 'Next';

                nextButton.className =
                    'bg-slate-700 hover:bg-slate-600 px-4 py-2 rounded-lg';

                nextButton.onclick = () => fetchContents(currentPage + 1);

                paginationContainer.appendChild(nextButton);
            }
        }

        function debounceSearch() {

            clearTimeout(debounceTimer);

            debounceTimer = setTimeout(() => {
                fetchContents(1);
            }, 400);
        }

        searchInput.addEventListener('input', debounceSearch);

        contentType.addEventListener('change', () => {
            fetchContents(1);
        });

        minRating.addEventListener('change', () => {
            fetchContents(1);
        });

        fetchContents();

    </script>

</body>
</html>