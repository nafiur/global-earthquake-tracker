<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Global Earthquake Intelligence</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            color-scheme: dark;
        }
        body {
            background: radial-gradient(circle at top, rgba(79, 70, 229, 0.25), transparent 55%),
                       radial-gradient(circle at 20% 20%, rgba(14, 165, 233, 0.25), transparent 40%),
                       #020617;
        }
        .glass-panel {
            background: linear-gradient(135deg, rgba(15,23,42,0.75), rgba(15,23,42,0.55));
            border: 1px solid rgba(255,255,255,0.05);
            box-shadow: 0 25px 50px -12px rgba(15,23,42,0.35);
            backdrop-filter: blur(18px);
        }
        .chip-toggle {
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 9999px;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: rgb(203 213 225);
            background: transparent;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .chip-toggle.active {
            background: linear-gradient(120deg, #6366f1, #8b5cf6);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 10px 25px rgba(99,102,241,0.35);
        }
        .view-toggle {
            border-radius: 9999px;
            padding: 0.5rem 1rem;
            border: 1px solid rgba(255,255,255,0.15);
            color: rgba(226,232,240,0.8);
            transition: all 0.2s ease;
            background: transparent;
            cursor: pointer;
        }
        .view-toggle.active {
            background: rgba(99,102,241,0.15);
            border-color: rgba(99,102,241,0.5);
            color: #fff;
        }
        @keyframes shimmer {
            0% { background-position: -468px 0; }
            100% { background-position: 468px 0; }
        }
        .skeleton {
            animation: shimmer 1.25s infinite linear;
            background: linear-gradient(90deg, rgba(255,255,255,0.05) 25%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0.05) 75%);
            background-size: 400% 100%;
        }
    </style>
</head>
<body class="font-sans text-slate-100 min-h-screen relative overflow-x-hidden">
    @php
        $sourceLabel = $activeSourceName ?? 'Global Live Feed';
        $sourceTypeLabel = $activeSourceType ?? 'USGS / EMSC Network';
    @endphp
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute -top-32 -right-10 w-72 h-72 bg-indigo-500/40 blur-[120px]"></div>
        <div class="absolute top-1/3 -left-16 w-80 h-80 bg-cyan-500/30 blur-[120px]"></div>
    </div>

    <main class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
        <section class="glass-panel rounded-3xl p-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 text-sm text-slate-300">
                        <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Live Seismic Intelligence
                    </div>
                    <div>
                        <h1 class="text-4xl sm:text-5xl font-semibold tracking-tight text-white">Global Earthquake Intelligence Hub</h1>
                        <p class="text-slate-300 mt-3 max-w-2xl">Monitor worldwide seismic activity in real-time with advanced filtering, contextual insights, and instant proximity alerts.</p>
                    </div>
                    <div class="flex flex-wrap gap-3 text-sm">
                        <span class="px-3 py-1 rounded-full bg-white/5 border border-white/10">Active Source: <strong class="ml-1 text-white/90">{{ $sourceLabel }}</strong></span>
                        <span class="px-3 py-1 rounded-full bg-white/5 border border-white/10">Network: <strong class="ml-1 text-white/90">{{ $sourceTypeLabel }}</strong></span>
                        <span class="px-3 py-1 rounded-full bg-white/5 border border-white/10">Last sync <span id="lastUpdated" class="font-semibold text-white/90">--</span></span>
                    </div>
                    <div id="filterSummary" class="flex flex-wrap gap-2"></div>
                </div>
                <div class="glass-panel rounded-2xl p-6 w-full lg:w-80">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Quick Actions</p>
                    <div class="mt-4 flex flex-col gap-3">
                        <button id="refreshBtn" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-500 to-purple-500 px-4 py-3 font-semibold text-white shadow-lg shadow-indigo-900/40 transition hover:-translate-y-0.5">
                            <span>Refresh feed</span>
                        </button>
                        <button id="nearMeBtn" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/10 px-4 py-3 font-semibold text-white transition hover:border-white/30">
                            <span>Near me mode</span>
                        </button>
                        <div class="text-sm text-slate-400">
                            Auto refreshes every 5 minutes.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="glass-panel rounded-3xl p-6 space-y-6">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="space-y-2">
                    <label for="timeRange" class="text-sm text-slate-400">Time Range</label>
                    <select id="timeRange" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm focus:border-indigo-400 focus:ring-0">
                        <option value="hour">Past Hour</option>
                        <option value="day" selected>Past Day</option>
                        <option value="week">Past Week</option>
                        <option value="month">Past Month</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label for="minMagnitude" class="text-sm text-slate-400">Minimum Magnitude</label>
                    <select id="minMagnitude" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm focus:border-indigo-400 focus:ring-0">
                        <option value="all">All events</option>
                        <option value="1">1.0+</option>
                        <option value="2.5">2.5+</option>
                        <option value="4.5" selected>4.5+</option>
                        <option value="6">6.0+</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label for="region" class="text-sm text-slate-400">Region Focus</label>
                    <select id="region" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm focus:border-indigo-400 focus:ring-0">
                        <option value="global">Global</option>
                        <option value="asia">Asia</option>
                        <option value="europe">Europe</option>
                        <option value="northamerica">North America</option>
                        <option value="southamerica">South America</option>
                        <option value="africa">Africa</option>
                        <option value="oceania">Oceania</option>
                        <option value="middleeast">Middle East</option>
                        <option value="caribbean">Caribbean</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm text-slate-400">Search</label>
                    <input id="searchTerm" type="text" placeholder="Filter by location keyword" class="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm focus:border-indigo-400 focus:ring-0" />
                </div>
            </div>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap gap-2">
                    <span class="text-xs uppercase tracking-widest text-slate-500">Sort</span>
                    <button id="sortMagnitude" class="chip-toggle active">Highest magnitude</button>
                    <button id="sortRecency" class="chip-toggle">Most recent</button>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs uppercase tracking-widest text-slate-500">View</span>
                    <button id="viewGrid" class="view-toggle active border border-white/10 rounded-2xl px-4 py-2 text-sm">Grid</button>
                    <button id="viewList" class="view-toggle border border-white/10 rounded-2xl px-4 py-2 text-sm">List</button>
                </div>
            </div>
        </section>

        <div id="locationStatus" class="hidden rounded-2xl border border-white/10 bg-white/5 p-4 text-sm"></div>

        <section>
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4" id="stats"></div>
        </section>

        <section>
            <div class="grid gap-4 md:grid-cols-3" id="insights"></div>
        </section>

        <section class="space-y-4">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-white">Live Events</h2>
                    <p class="text-slate-400 text-sm">Showing <span id="liveCount" class="font-semibold">0</span> earthquakes that match your filters.</p>
                </div>
            </div>
            <div id="content" class="space-y-4">
                <div class="glass-panel rounded-2xl p-6 text-center text-slate-300">
                    Loading live data...
                </div>
            </div>
        </section>
    </main>

    <script>
        const API_BASE = '/earthquakes/data';
        let earthquakes = [];
        let userLocation = null;
        let isNearMeMode = false;
        let sortMode = 'magnitude';
        let viewMode = 'grid';
        let lastUpdated = null;

        const refreshBtn = document.getElementById('refreshBtn');
        const nearMeBtn = document.getElementById('nearMeBtn');
        const locationStatus = document.getElementById('locationStatus');
        const statsEl = document.getElementById('stats');
        const insightsEl = document.getElementById('insights');
        const contentEl = document.getElementById('content');
        const lastUpdatedEl = document.getElementById('lastUpdated');
        const liveCountEl = document.getElementById('liveCount');
        const searchInput = document.getElementById('searchTerm');
        const filterSummaryEl = document.getElementById('filterSummary');
        const sortMagnitudeBtn = document.getElementById('sortMagnitude');
        const sortRecencyBtn = document.getElementById('sortRecency');
        const viewGridBtn = document.getElementById('viewGrid');
        const viewListBtn = document.getElementById('viewList');

        const regions = {
            global: null,
            asia: { minLat: -10, maxLat: 55, minLon: 60, maxLon: 150 },
            europe: { minLat: 35, maxLat: 71, minLon: -10, maxLon: 40 },
            northamerica: { minLat: 15, maxLat: 72, minLon: -170, maxLon: -50 },
            southamerica: { minLat: -56, maxLat: 13, minLon: -82, maxLon: -34 },
            africa: { minLat: -35, maxLat: 37, minLon: -18, maxLon: 52 },
            oceania: { minLat: -50, maxLat: 0, minLon: 110, maxLon: 180 },
            middleeast: { minLat: 12, maxLat: 42, minLon: 34, maxLon: 63 },
            caribbean: { minLat: 10, maxLat: 27, minLon: -85, maxLon: -60 }
        };

        const loaderCard = `
            <div class="glass-panel rounded-2xl p-6">
                <div class="skeleton h-6 w-1/3 rounded"></div>
                <div class="mt-4 space-y-3">
                    <div class="skeleton h-4 w-full rounded"></div>
                    <div class="skeleton h-4 w-5/6 rounded"></div>
                    <div class="skeleton h-4 w-2/3 rounded"></div>
                </div>
            </div>
        `;

        function isInRegion(lat, lon, region) {
            if (region === 'global' || !regions[region]) return true;
            const bounds = regions[region];
            return lat >= bounds.minLat && lat <= bounds.maxLat && lon >= bounds.minLon && lon <= bounds.maxLon;
        }

        async function fetchEarthquakes(showLoader = true) {
            if (showLoader) {
                contentEl.innerHTML = `<div class="grid gap-4 md:grid-cols-2">${loaderCard.repeat(2)}</div>`;
            }

            refreshBtn.disabled = true;
            refreshBtn.classList.add('opacity-60');

            try {
                const timeRange = document.getElementById('timeRange').value;
                const minMag = document.getElementById('minMagnitude').value;
                const url = new URL(window.location.origin + API_BASE);
                url.searchParams.append('timeRange', timeRange);
                url.searchParams.append('minMagnitude', minMag);

                const response = await fetch(url);
                if (!response.ok) throw new Error('Failed to fetch data');

                const data = await response.json();
                earthquakes = data.features || [];
                lastUpdated = new Date();
                updateLastUpdated();
                applyFilters();
            } catch (error) {
                console.error(error);
                contentEl.innerHTML = `
                    <div class="glass-panel rounded-2xl p-6 text-center text-red-200 border border-red-500/30">
                        <p class="font-semibold">Unable to retrieve earthquake data.</p>
                        <p class="text-sm text-red-300 mt-2">Please check your connection or try again shortly.</p>
                    </div>
                `;
            } finally {
                refreshBtn.disabled = false;
                refreshBtn.classList.remove('opacity-60');
            }
        }

        function applyFilters() {
            const region = document.getElementById('region').value;
            const searchTerm = (searchInput.value || '').toLowerCase();
            let filtered = earthquakes.filter(eq => {
                const coords = eq.geometry.coordinates;
                const matchesRegion = isInRegion(coords[1], coords[0], region);
                const matchesSearch = !searchTerm || (eq.properties.place || '').toLowerCase().includes(searchTerm);
                return matchesRegion && matchesSearch;
            });

            if (isNearMeMode && userLocation) {
                filtered.forEach(eq => {
                    const coords = eq.geometry.coordinates;
                    eq.distance = calculateDistance(userLocation.latitude, userLocation.longitude, coords[1], coords[0]);
                });
                filtered.sort((a, b) => a.distance - b.distance);
            } else if (sortMode === 'magnitude') {
                filtered.sort((a, b) => (b.properties.mag || 0) - (a.properties.mag || 0));
            } else {
                filtered.sort((a, b) => (b.properties.time || 0) - (a.properties.time || 0));
            }

            liveCountEl.textContent = filtered.length;
            displayStats(filtered);
            displayInsights(filtered);
            displayEarthquakes(filtered);
            updateFilterSummary();
        }

        function displayStats(earthquakesList) {
            if (!earthquakesList.length) {
                statsEl.innerHTML = `<div class="glass-panel rounded-2xl p-6 text-center text-slate-300">No data for current filters.</div>`;
                return;
            }

            const magnitudes = earthquakesList.map(e => e.properties.mag || 0);
            const depths = earthquakesList.map(e => e.geometry.coordinates[2] || 0);
            const total = earthquakesList.length;
            const highest = Math.max(...magnitudes).toFixed(1);
            const average = (magnitudes.reduce((a, b) => a + b, 0) / (magnitudes.length || 1)).toFixed(1);
            const major = earthquakesList.filter(e => (e.properties.mag || 0) >= 6).length;
            const shallow = earthquakesList.filter(e => (e.geometry.coordinates[2] || 0) <= 70).length;
            const deep = earthquakesList.filter(e => (e.geometry.coordinates[2] || 0) >= 300).length;

            const stats = [
                { label: 'Total Events', value: total, detail: 'matching filters' },
                { label: 'Highest Magnitude', value: highest, detail: 'most energetic event' },
                { label: 'Average Magnitude', value: average, detail: 'across all events' },
                { label: 'Major (6.0+)', value: major, detail: 'strong or greater' },
                { label: 'Shallow (<70km)', value: shallow, detail: 'potentially damaging' },
                { label: 'Deep (>300km)', value: deep, detail: 'subduction zone activity' },
                { label: 'Average Depth', value: `${(depths.reduce((a,b)=>a+b,0)/ (depths.length || 1)).toFixed(0)} km`, detail: 'from focus point' },
                { label: 'Active Mode', value: isNearMeMode ? 'Near Me' : sortMode === 'magnitude' ? 'Top Magnitude' : 'Most Recent', detail: 'current prioritization' },
            ];

            statsEl.innerHTML = stats.map(stat => `
                <div class="glass-panel rounded-2xl p-5">
                    <p class="text-xs uppercase tracking-widest text-slate-400">${stat.label}</p>
                    <p class="text-3xl font-semibold text-white mt-2">${stat.value}</p>
                    <p class="text-sm text-slate-400">${stat.detail}</p>
                </div>
            `).join('');
        }

        function displayInsights(earthquakesList) {
            if (!earthquakesList.length) {
                insightsEl.innerHTML = `<div class="glass-panel rounded-2xl p-6 text-center text-slate-300 md:col-span-3">Insights will appear once data loads.</div>`;
                return;
            }

            const sortedByMag = [...earthquakesList].sort((a, b) => (b.properties.mag || 0) - (a.properties.mag || 0));
            const sortedByTime = [...earthquakesList].sort((a, b) => (b.properties.time || 0) - (a.properties.time || 0));
            const sortedByDepth = [...earthquakesList].sort((a, b) => (b.geometry.coordinates[2] || 0) - (a.geometry.coordinates[2] || 0));

            const insights = [
                {
                    label: 'Strongest Event',
                    highlight: `${(sortedByMag[0].properties.mag || 0).toFixed(1)} Mw`,
                    place: sortedByMag[0].properties.place || 'Unknown location',
                    meta: formatTime(sortedByMag[0].properties.time)
                },
                {
                    label: 'Newest Event',
                    highlight: formatRelativeTime(sortedByTime[0].properties.time),
                    place: sortedByTime[0].properties.place || 'Unknown location',
                    meta: `${(sortedByTime[0].properties.mag || 0).toFixed(1)} Mw`
                },
                {
                    label: 'Shallow Highlight',
                    highlight: `${sortedByDepth[0].geometry.coordinates[2]?.toFixed(1) || 0} km`,
                    place: sortedByDepth[0].properties.place || 'Unknown location',
                    meta: `${(sortedByDepth[0].properties.mag || 0).toFixed(1)} Mw`
                }
            ];

            insightsEl.innerHTML = insights.map(insight => `
                <div class="glass-panel rounded-2xl p-5">
                    <p class="text-xs uppercase tracking-widest text-slate-400">${insight.label}</p>
                    <p class="text-3xl font-semibold text-white mt-2">${insight.highlight}</p>
                    <p class="text-sm text-white/80 mt-1">${insight.place}</p>
                    <p class="text-xs text-slate-400 mt-1">${insight.meta}</p>
                </div>
            `).join('');
        }

        function displayEarthquakes(earthquakesList) {
            if (!earthquakesList.length) {
                contentEl.innerHTML = `
                    <div class="glass-panel rounded-2xl p-6 text-center text-slate-300">
                        No earthquakes match your criteria right now.
                    </div>
                `;
                return;
            }

            const layoutClass = viewMode === 'grid' ? 'grid gap-4 md:grid-cols-2' : 'space-y-4';
            const cards = earthquakesList.map(eq => {
                const props = eq.properties;
                const coords = eq.geometry.coordinates;
                const mag = props.mag || 0;
                const distanceHtml = isNearMeMode && eq.distance !== undefined
                    ? `<div><p class="text-xs text-slate-400">Distance</p><p class="text-sm text-white">${eq.distance.toFixed(0)} km away</p></div>`
                    : `<div><p class="text-xs text-slate-400">Status</p><p class="text-sm text-white">${props.status || 'reviewed'}</p></div>`;

                return `
                    <article class="glass-panel rounded-2xl p-6 h-full flex flex-col gap-4">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-widest text-slate-400">${formatTime(props.time)}</p>
                                <h3 class="text-2xl font-semibold text-white">${props.place || 'Unknown location'}</h3>
                                <div class="text-sm text-slate-400 mt-1">${props.type || 'earthquake'}</div>
                            </div>
                            <div class="text-right">
                                <div class="inline-flex flex-col items-end rounded-2xl px-4 py-2 bg-gradient-to-br ${getMagnitudeClass(mag)}">
                                    <span class="text-3xl font-bold text-white">${mag.toFixed(1)}</span>
                                    <span class="text-xs text-white/90">${getIntensityLabel(mag)}</span>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                            <div>
                                <p class="text-xs uppercase tracking-widest text-slate-400">Depth</p>
                                <p class="text-white">${coords[2]?.toFixed(1) || 0} km</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-widest text-slate-400">Coordinates</p>
                                <p class="text-white">${coords[1].toFixed(2)}°, ${coords[0].toFixed(2)}°</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-widest text-slate-400">Magnitude Type</p>
                                <p class="text-white">${props.magType || 'N/A'}</p>
                            </div>
                            ${distanceHtml}
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-3 text-xs text-slate-400">
                            <span>ID: ${props.code || props.ids || 'N/A'}</span>
                            ${props.url ? `<a href="${props.url}" target="_blank" class="text-indigo-300 hover:text-indigo-200">Open detailed report →</a>` : ''}
                        </div>
                    </article>
                `;
            }).join('');

            contentEl.innerHTML = `<div class="${layoutClass}">${cards}</div>`;
        }

        function updateFilterSummary() {
            const timeRangeText = document.getElementById('timeRange').selectedOptions[0].text;
            const minMagText = document.getElementById('minMagnitude').selectedOptions[0].text;
            const regionText = document.getElementById('region').selectedOptions[0].text;
            const chips = [
                { label: 'Range', value: timeRangeText },
                { label: 'Magnitude', value: minMagText },
                { label: 'Region', value: regionText },
                isNearMeMode ? { label: 'Mode', value: 'Near me' } : null,
                searchInput.value ? { label: 'Search', value: searchInput.value } : null,
                { label: 'Sort', value: sortMode === 'magnitude' ? 'Highest magnitude' : 'Most recent' }
            ].filter(Boolean);

            filterSummaryEl.innerHTML = chips.map(chip => `
                <span class="text-xs uppercase tracking-widest text-slate-400 bg-white/5 border border-white/10 rounded-full px-3 py-1">
                    ${chip.label}: <strong class="ml-1 text-white/90">${chip.value}</strong>
                </span>
            `).join('');
        }

        function updateLastUpdated() {
            if (!lastUpdated) return;
            const formatter = new Intl.DateTimeFormat(undefined, { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            lastUpdatedEl.textContent = formatter.format(lastUpdated);
        }

        function formatTime(timestamp) {
            if (!timestamp) return 'Unknown';
            const date = new Date(timestamp);
            return date.toLocaleString();
        }

        function formatRelativeTime(timestamp) {
            if (!timestamp) return 'Unknown';
            const diffMs = Date.now() - timestamp;
            const diffMins = Math.floor(diffMs / 60000);
            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return `${diffMins} min ago`;
            const diffHours = Math.floor(diffMins / 60);
            if (diffHours < 24) return `${diffHours} h ago`;
            const diffDays = Math.floor(diffHours / 24);
            return `${diffDays} d ago`;
        }

        function getMagnitudeClass(mag) {
            if (mag < 4.5) return 'from-emerald-500 to-emerald-600';
            if (mag < 6.0) return 'from-amber-500 to-orange-500';
            return 'from-rose-500 to-red-600';
        }

        function getIntensityLabel(mag) {
            if (mag < 4.5) return 'Light';
            if (mag < 6.0) return 'Moderate';
            if (mag < 7.5) return 'Strong';
            return 'Severe';
        }

        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }

        function showLocationStatus(message, type = 'info') {
            locationStatus.classList.remove('hidden');
            locationStatus.textContent = message;
            locationStatus.classList.remove('border-emerald-400/40', 'border-red-400/40', 'border-white/10');
            locationStatus.classList.remove('text-emerald-200', 'text-red-200', 'text-slate-200');

            if (type === 'success') {
                locationStatus.classList.add('border-emerald-400/40', 'text-emerald-200');
            } else if (type === 'error') {
                locationStatus.classList.add('border-red-400/40', 'text-red-200');
            } else {
                locationStatus.classList.add('border-white/10', 'text-slate-200');
            }

            if (type !== 'error') {
                setTimeout(() => {
                    locationStatus.classList.add('hidden');
                }, 5000);
            }
        }

        function resetNearMeMode() {
            isNearMeMode = false;
            nearMeBtn.classList.remove('bg-gradient-to-r', 'from-emerald-500', 'to-lime-400');
            nearMeBtn.classList.add('border', 'border-white/10');
            locationStatus.classList.add('hidden');
            applyFilters();
        }

        refreshBtn.addEventListener('click', () => fetchEarthquakes());
        sortMagnitudeBtn.addEventListener('click', () => {
            sortMode = 'magnitude';
            sortMagnitudeBtn.classList.add('active');
            sortRecencyBtn.classList.remove('active');
            applyFilters();
        });
        sortRecencyBtn.addEventListener('click', () => {
            sortMode = 'recency';
            sortRecencyBtn.classList.add('active');
            sortMagnitudeBtn.classList.remove('active');
            applyFilters();
        });
        viewGridBtn.addEventListener('click', () => {
            viewMode = 'grid';
            viewGridBtn.classList.add('active');
            viewListBtn.classList.remove('active');
            applyFilters();
        });
        viewListBtn.addEventListener('click', () => {
            viewMode = 'list';
            viewListBtn.classList.add('active');
            viewGridBtn.classList.remove('active');
            applyFilters();
        });

        searchInput.addEventListener('input', () => applyFilters());
        document.getElementById('region').addEventListener('change', () => {
            resetNearMeMode();
        });
        document.getElementById('timeRange').addEventListener('change', () => fetchEarthquakes());
        document.getElementById('minMagnitude').addEventListener('change', () => fetchEarthquakes());

        nearMeBtn.addEventListener('click', () => {
            if (!navigator.geolocation) {
                showLocationStatus('Geolocation not supported by your browser.', 'error');
                return;
            }

            showLocationStatus('Requesting your location...', 'info');
            nearMeBtn.classList.remove('border', 'border-white/10');
            nearMeBtn.classList.add('bg-gradient-to-r', 'from-emerald-500', 'to-lime-400');

            navigator.geolocation.getCurrentPosition(
                position => {
                    userLocation = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    };
                    isNearMeMode = true;
                    showLocationStatus(`Location locked at ${userLocation.latitude.toFixed(2)}°, ${userLocation.longitude.toFixed(2)}°. Showing closest quakes.`, 'success');
                    applyFilters();
                },
                error => {
                    showLocationStatus('Unable to access location: ' + error.message, 'error');
                    resetNearMeMode();
                }
            );
        });

        fetchEarthquakes();
        setInterval(() => fetchEarthquakes(false), 300000);
    </script>
</body>
</html>
