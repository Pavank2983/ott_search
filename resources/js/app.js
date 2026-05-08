let currentPage = 1;

const searchInput = document.getElementById('searchInput');

const tenantId = document.getElementById('tenantId');

const contentType = document.getElementById('contentType');

const minRating = document.getElementById('minRating');

const resultsContainer = document.getElementById('results');

const paginationContainer = document.getElementById('pagination');

const paginationWrapper = document.getElementById('paginationWrapper');

const paginationInfo = document.getElementById('paginationInfo');

const loading = document.getElementById('loading');

const emptyState = document.getElementById('emptyState');

const resultsMeta = document.getElementById('resultsMeta');

const resultsCount = document.getElementById('resultsCount');

const activeFilters = document.getElementById('activeFilters');

const searchSuggestions = document.getElementById('searchSuggestions');

let debounceTimer, suggestionDebounceTimer;

/*
|--------------------------------------------------------------------------
| URL Helpers
|--------------------------------------------------------------------------
*/

function updateUrl(page = 1) {

    const params = new URLSearchParams();

    const q = searchInput?.value?.trim();

    const tenant = tenantId?.value;

    const type = contentType?.value;

    const rating = minRating?.value;

    if (q) {
        params.set('q', q);
    }

    if (tenant) {
        params.set('tenant_id', tenant);
    }

    if (type) {
        params.set('content_type', type);
    }

    if (rating) {
        params.set('min_rating', rating);
    }

    if (page > 1) {
        params.set('page', page);
    }

    const queryString = params.toString();

    const newUrl = queryString
        ? `${window.location.pathname}?${queryString}`
        : window.location.pathname;

    window.history.replaceState(
        {},
        '',
        newUrl
    );
}

function restoreFiltersFromUrl() {

    const params = new URLSearchParams(
        window.location.search
    );

    const q = params.get('q');

    const tenant = params.get('tenant_id');

    const type = params.get('content_type');

    const rating = params.get('min_rating');

    const page = params.get('page');

    if (q && searchInput) {
        searchInput.value = q;
    }

    if (tenant && tenantId) {
        tenantId.dataset.selected = tenant;
    }

    if (type && contentType) {
        contentType.value = type;
    }

    if (rating && minRating) {
        minRating.value = rating;
    }

    currentPage = page
        ? parseInt(page)
        : 1;
}

/*
|--------------------------------------------------------------------------
| Load Tenants
|--------------------------------------------------------------------------
*/

async function loadTenants() {

    try {

        const response = await fetch(
            '/api/tenants',
            {
                headers: {
                    'Accept': 'application/json',
                },
            }
        );

        if (!response.ok) {

            throw new Error(
                'Failed to load tenants'
            );
        }

        const tenants = await response.json();

        tenantId.innerHTML = tenants.map(tenant => `
            <option value="${tenant.id}">
                ${tenant.name}
            </option>
        `).join('');

        const selectedTenant =
            tenantId.dataset.selected;

        if (selectedTenant) {

            tenantId.value = selectedTenant;
        }

        /*
        |--------------------------------------------------------------------------
        | Default First Tenant
        |--------------------------------------------------------------------------
        */

        if (!tenantId.value && tenants.length) {

            tenantId.value = tenants[0].id;
        }

    } catch (error) {

        console.error(error);

        tenantId.innerHTML = `
            <option value="">
                Failed to load platforms
            </option>
        `;
    }
}

/*
|--------------------------------------------------------------------------
| Search Suggestions
|--------------------------------------------------------------------------
*/

async function fetchSuggestions() {

    const query =
        searchInput?.value?.trim();

    /*
    |--------------------------------------------------------------------------
    | Minimum Query Length
    |--------------------------------------------------------------------------
    */

    if (!query || query.length < 2) {

        hideSuggestions();

        return;
    }

    try {

        const params = new URLSearchParams({
            q: query,
            tenant_id: tenantId?.value || 1,
        });

        const response = await fetch(
            `/api/search/suggestions?${params.toString()}`,
            {
                headers: {
                    'Accept': 'application/json',
                },
            }
        );

        if (!response.ok) {

            throw new Error(
                'Failed to fetch suggestions'
            );
        }

        const suggestions =
            await response.json();

        renderSuggestions(
            suggestions
        );

    } catch (error) {

        console.error(error);

        hideSuggestions();
    }
}

function renderSuggestions(
    suggestions
) {

    if (!suggestions.length) {

        searchSuggestions.innerHTML = `
            <div class="
                px-5
                py-4
                text-sm
                text-slate-400
            ">
                No suggestions found
            </div>
        `;

        searchSuggestions.classList.remove(
            'hidden'
        );

        return;
    }

    searchSuggestions.innerHTML =
        suggestions.map(title => `
            <button
                class="
                    suggestion-item
                    w-full
                    text-left
                    px-5
                    py-4
                    border-b
                    border-slate-800
                    hover:bg-slate-800/70
                    transition
                    flex
                    items-center
                    gap-3
                "
                data-title="${title}"
            >

                <svg
                    class="w-4 h-4 text-slate-500"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"
                    />
                </svg>

                <span class="text-sm">
                    ${title}
                </span>

            </button>
        `).join('');

    searchSuggestions.classList.remove(
        'hidden'
    );

    /*
    |--------------------------------------------------------------------------
    | Suggestion Click Events
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.suggestion-item')
        .forEach(item => {

            item.addEventListener(
                'click',
                () => {

                    const title =
                        item.dataset.title;

                    searchInput.value = title;

                    hideSuggestions();

                    fetchContents(1);
                }
            );
        });
}

function hideSuggestions() {

    searchSuggestions.classList.add(
        'hidden'
    );
}

function debounceSuggestions() {

    clearTimeout(
        suggestionDebounceTimer
    );

    suggestionDebounceTimer =
        setTimeout(() => {

            fetchSuggestions();

        }, 250);
}

/*
|--------------------------------------------------------------------------
| Fetch Contents
|--------------------------------------------------------------------------
*/

async function fetchContents(page = 1) {

    currentPage = page;

    updateUrl(page);

    showLoading();

    try {

        const params = new URLSearchParams({
            tenant_id: tenantId?.value || 1,
            page,
        });

        if (searchInput?.value?.trim()) {

            params.append(
                'q',
                searchInput.value.trim()
            );
        }

        if (contentType?.value) {

            params.append(
                'content_type',
                contentType.value
            );
        }

        if (minRating?.value) {

            params.append(
                'min_rating',
                minRating.value
            );
        }

        const response = await fetch(
            `/api/search?${params.toString()}`,
            {
                headers: {
                    'Accept': 'application/json',
                },
            }
        );

        if (!response.ok) {

            throw new Error(
                'Failed to fetch contents'
            );
        }

        const data = await response.json();

        hideLoading();

        if (!data.data || !data.data.length) {

            showEmptyState();

            return;
        }

        renderContents(data.data);

        renderMeta(data);

        renderActiveFilters();

        renderPagination(
            data.current_page,
            data.last_page,
            data.total
        );

    } catch (error) {

        console.error(error);

        hideLoading();

        showEmptyState();
    }
}

/*
|--------------------------------------------------------------------------
| Render Meta
|--------------------------------------------------------------------------
*/

function renderMeta(data) {

    resultsMeta.classList.remove('hidden');

    resultsMeta.classList.add('flex');

    const start =
        ((data.current_page - 1) * data.per_page) + 1;

    const end =
        Math.min(
            data.current_page * data.per_page,
            data.total
        );

    resultsCount.innerText =
        `Showing ${start}-${end} of ${data.total.toLocaleString()} results`;
}

/*
|--------------------------------------------------------------------------
| Active Filters
|--------------------------------------------------------------------------
*/

function renderActiveFilters() {

    activeFilters.innerHTML = '';

    const filters = [];

    /*
    |--------------------------------------------------------------------------
    | Tenant
    |--------------------------------------------------------------------------
    */

    if (tenantId?.value) {

        const selectedTenant =
            tenantId.options[
                tenantId.selectedIndex
            ]?.text;

        filters.push({
            label: selectedTenant,
            type: 'tenant',
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    if (searchInput?.value?.trim()) {

        filters.push({
            label: `Search: "${searchInput.value.trim()}"`,
            type: 'search',
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Content Type
    |--------------------------------------------------------------------------
    */

    if (contentType?.value) {

        filters.push({
            label: contentType.value,
            type: 'content_type',
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Rating
    |--------------------------------------------------------------------------
    */

    if (minRating?.value) {

        filters.push({
            label: `${minRating.value}+ Rating`,
            type: 'min_rating',
        });
    }

    if (!filters.length) {

        activeFilters.classList.add('hidden');

        return;
    }

    activeFilters.classList.remove('hidden');

    filters.forEach(filter => {

        const chip = document.createElement('button');

        chip.className = `
            inline-flex
            items-center
            gap-2
            px-4
            py-2
            rounded-full
            bg-indigo-500/10
            border
            border-indigo-500/20
            text-indigo-300
            text-sm
            hover:bg-indigo-500/20
            transition
        `;

        chip.innerHTML = `
            <span>${filter.label}</span>
            ${filter.type !== 'tenant'
                ? '<span class="text-white">×</span>'
                : ''
            }
        `;

        /*
        |--------------------------------------------------------------------------
        | Tenant Should Not Be Removable
        |--------------------------------------------------------------------------
        */

        if (filter.type !== 'tenant') {

            chip.onclick = () => {

                if (filter.type === 'search') {
                    searchInput.value = '';
                }

                if (filter.type === 'content_type') {
                    contentType.value = '';
                }

                if (filter.type === 'min_rating') {
                    minRating.value = '';
                }

                fetchContents(1);
            };
        }

        activeFilters.appendChild(chip);
    });
}

/*
|--------------------------------------------------------------------------
| Render Cards
|--------------------------------------------------------------------------
*/

function renderContents(contents) {

    resultsContainer.innerHTML = '';

    contents.forEach(content => {

        const poster =
            content.poster_url ||
            '/images/posters/1.jpg';

        const description =
            content.description ||
            'No description available';

        const shortDescription =
            description.length > 140
                ? `${description.slice(0, 140)}...`
                : description;

        const card = document.createElement('article');

        card.className = `
            group
            bg-slate-900
            border
            border-slate-800
            rounded-2xl
            overflow-hidden
            hover:border-indigo-500/50
            hover:-translate-y-1
            hover:shadow-2xl
            hover:shadow-indigo-500/10
            transition-all
            duration-300
        `;

        card.innerHTML = `
            <div class="relative overflow-hidden">

                <img
                    src="${poster}"
                    alt="${content.title}"
                    loading="lazy"
                    class="
                        w-full
                        h-[270px]
                        object-cover
                        bg-slate-800
                        group-hover:scale-105
                        transition-transform
                        duration-500
                    "
                >

                <div class="
                    absolute
                    top-3
                    right-3
                    bg-black/80
                    px-3
                    py-1
                    rounded-full
                    text-xs
                    font-bold
                    text-yellow-400
                ">
                    ⭐ ${content.imdb_rating ?? 'N/A'}
                </div>

            </div>

            <div class="p-4 flex flex-col h-[260px]">

                <h2 class="
                    text-xl
                    font-bold
                    mb-3
                    line-clamp-2
                ">
                    ${content.title}
                </h2>

                <div class="flex flex-wrap gap-2 mb-3">

                    <span class="
                        bg-indigo-500/10
                        border
                        border-indigo-500/20
                        text-indigo-300
                        px-3
                        py-1
                        rounded-full
                        text-xs
                    ">
                        ${content.content_type}
                    </span>

                    <span class="
                        bg-slate-800
                        text-slate-300
                        px-3
                        py-1
                        rounded-full
                        text-xs
                    ">
                        ${content.release_year}
                    </span>

                </div>

                <div class="flex-1">

                    <p
                        class="
                            text-slate-400
                            text-sm
                            leading-relaxed
                        "
                        data-description
                        data-full="${description}"
                        data-short="${shortDescription}"
                    >
                        ${shortDescription}
                    </p>

                    ${description.length > 140
                ? `
                                <button
                                    class="
                                        mt-2
                                        text-indigo-400
                                        hover:text-indigo-300
                                        text-sm
                                        font-medium
                                    "
                                    data-toggle-description
                                >
                                    See more
                                </button>
                            `
                : ''
            }

                </div>

            </div>
        `;

        const toggleButton =
            card.querySelector(
                '[data-toggle-description]'
            );

        const descriptionElement =
            card.querySelector(
                '[data-description]'
            );

        if (
            toggleButton &&
            descriptionElement
        ) {

            let expanded = false;

            toggleButton.addEventListener(
                'click',
                () => {

                    expanded = !expanded;

                    descriptionElement.innerText =
                        expanded
                            ? descriptionElement.dataset.full
                            : descriptionElement.dataset.short;

                    toggleButton.innerText =
                        expanded
                            ? 'See less'
                            : 'See more';
                }
            );
        }

        resultsContainer.appendChild(card);
    });
}

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

function renderPagination(
    currentPage,
    lastPage,
    total
) {

    paginationContainer.innerHTML = '';

    if (lastPage <= 1) {

        paginationWrapper.classList.add('hidden');

        return;
    }

    paginationWrapper.classList.remove('hidden');

    paginationInfo.innerText =
        `Page ${currentPage} of ${lastPage} • ${total.toLocaleString()} contents`;

    if (currentPage > 1) {

        paginationContainer.appendChild(
            createPaginationButton(
                'Previous',
                () => fetchContents(currentPage - 1)
            )
        );
    }

    const maxPages = 5;

    let start =
        Math.max(
            1,
            currentPage - 2
        );

    let end =
        Math.min(
            lastPage,
            start + maxPages - 1
        );

    for (let i = start; i <= end; i++) {

        const button =
            createPaginationButton(
                i,
                () => fetchContents(i)
            );

        if (i === currentPage) {

            button.classList.add(
                'bg-white',
                'text-black'
            );
        }

        paginationContainer.appendChild(button);
    }

    if (currentPage < lastPage) {

        paginationContainer.appendChild(
            createPaginationButton(
                'Next',
                () => fetchContents(currentPage + 1)
            )
        );
    }
}

/*
|--------------------------------------------------------------------------
| Pagination Button
|--------------------------------------------------------------------------
*/

function createPaginationButton(
    text,
    onClick
) {

    const button = document.createElement('button');

    button.innerText = text;

    button.className = `
        bg-indigo-600
        hover:bg-indigo-500
        px-4
        py-2
        rounded-xl
        text-sm
        font-medium
        transition
    `;

    button.onclick = onClick;

    return button;
}

/*
|--------------------------------------------------------------------------
| Debounce
|--------------------------------------------------------------------------
*/

function debounceSearch() {

    clearTimeout(debounceTimer);

    debounceTimer = setTimeout(() => {
        fetchContents(1);
    }, 400);
}

/*
|--------------------------------------------------------------------------
| Loading
|--------------------------------------------------------------------------
*/

function showLoading() {

    loading.classList.remove('hidden');

    emptyState.classList.add('hidden');

    resultsMeta.classList.add('hidden');

    paginationWrapper.classList.add('hidden');

    resultsContainer.innerHTML = '';
}

function hideLoading() {

    loading.classList.add('hidden');
}

/*
|--------------------------------------------------------------------------
| Empty
|--------------------------------------------------------------------------
*/

function showEmptyState() {

    emptyState.classList.remove('hidden');

    resultsMeta.classList.add('hidden');

    paginationWrapper.classList.add('hidden');

    activeFilters.classList.add('hidden');

    resultsContainer.innerHTML = '';

    paginationContainer.innerHTML = '';
}

/*
|--------------------------------------------------------------------------
| Events
|--------------------------------------------------------------------------
*/

searchInput?.addEventListener(
    'input',
    () => {

        debounceSearch();

        debounceSuggestions();
    }
);

tenantId?.addEventListener(
    'change',
    () => {

        hideSuggestions();

        fetchContents(1);
    }
);

contentType?.addEventListener(
    'change',
    () => fetchContents(1)
);

minRating?.addEventListener(
    'change',
    () => fetchContents(1)
);

/*
|--------------------------------------------------------------------------
| Keyboard Shortcut
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'keydown',
    (event) => {

        if (
            event.key === '/'
            && document.activeElement !== searchInput
        ) {

            event.preventDefault();

            searchInput.focus();
        }

        if (event.key === 'Escape') {

            searchInput.blur();
        }
    }
);

/*
|--------------------------------------------------------------------------
| Outside Click
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'click',
    (event) => {

        if (
            !searchSuggestions.contains(event.target)
            && event.target !== searchInput
        ) {

            hideSuggestions();
        }
    }
);

/*
|--------------------------------------------------------------------------
| Initial Load
|--------------------------------------------------------------------------
*/

async function init() {

    restoreFiltersFromUrl();

    await loadTenants();

    fetchContents(currentPage);
}

init();