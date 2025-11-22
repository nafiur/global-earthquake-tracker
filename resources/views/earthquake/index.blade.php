<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Global Earhquake Console</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            color-scheme: dark;
            --surface: rgba(3, 7, 18, 0.75);
            --stroke: rgba(148, 163, 184, 0.15);
            --primary: #7dd3fc;
            --accent: #a855f7;
        }
        body {
            min-height: 100vh;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #010409;
            color: #e2e8f0;
            margin: 0;
        }
        .starfield::before,
        .starfield::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(125,211,252,0.4) 1px, transparent 1px);
            background-size: 40px 40px;
            opacity: 0.15;
            animation: drift 50s linear infinite;
        }
        .starfield::after {
            opacity: 0.25;
            background-size: 80px 80px;
            animation-duration: 80s;
        }
        @keyframes drift {
            from { transform: translate3d(-10px, -10px, 0); }
            to { transform: translate3d(10px, 10px, 0); }
        }
        .glass-pane {
            background: var(--surface);
            backdrop-filter: blur(20px);
            border: 1px solid var(--stroke);
            border-radius: 28px;
            box-shadow: 0 40px 120px rgba(2, 6, 23, 0.65);
        }
        .control-dock select,
        .control-dock input {
            width: 100%;
            border-radius: 18px;
            border: 1px solid var(--stroke);
            background: rgba(15, 23, 42, 0.75);
            color: #f8fafc;
            padding: 0.8rem 1rem;
            font-size: 0.95rem;
            transition: border 0.25s ease, transform 0.25s ease;
        }
        .control-dock select:focus,
        .control-dock input:focus {
            outline: none;
            border-color: rgba(125, 211, 252, 0.7);
            transform: translateY(-2px);
        }
        .chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.9rem;
            border-radius: 999px;
            border: 1px solid rgba(125, 211, 252, 0.2);
            background: rgba(15, 23, 42, 0.6);
            font-size: 0.75rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: rgba(226, 232, 240, 0.85);
        }
        .toggle-pill {
            border-radius: 999px;
            border: 1px solid var(--stroke);
            padding: 0.35rem 1.2rem;
            font-size: 0.87rem;
            color: rgba(226,232,240,0.85);
            background: transparent;
            transition: all 0.25s ease;
            cursor: pointer;
        }
        .toggle-pill.active {
            background: linear-gradient(135deg, rgba(125, 211, 252, 0.2), rgba(168, 85, 247, 0.3));
            border-color: rgba(125, 211, 252, 0.4);
            color: #fff;
            box-shadow: 0 15px 40px rgba(79, 70, 229, 0.35);
        }
        .data-capsule {
            border-radius: 24px;
            border: 1px solid var(--stroke);
            background: rgba(2, 6, 23, 0.9);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }
        .timeline {
            position: relative;
            padding-left: 2.5rem;
        }
        .timeline::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 1rem;
            width: 2px;
            background: linear-gradient(to bottom, rgba(125, 211, 252, 0.4), rgba(168, 85, 247, 0.15));
        }
        .timeline-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #0ea5e9;
            border: 3px solid rgba(14, 165, 233, 0.3);
            box-shadow: 0 0 20px rgba(14, 165, 233, 0.6);
        }
        .event-card {
            border-radius: 24px;
            padding: 1.5rem;
            border: 1px solid rgba(148, 163, 184, 0.15);
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.65));
            transition: transform 0.25s ease, border 0.25s ease;
        }
        .event-card:hover {
            transform: translate3d(4px, -3px, 0);
            border-color: rgba(125, 211, 252, 0.4);
        }
        .status-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .status-bar span {
            border-radius: 999px;
            border: 1px solid rgba(125,211,252,0.25);
            padding: 0.4rem 0.9rem;
            color: rgba(226,232,240,0.85);
        }
    </style>
</head>
<body class="starfield relative">
    @php
        $sourceLabel = $activeSourceName ?? 'Global Live Feed';
        $sourceTypeLabel = $activeSourceType ?? 'USGS / EMSC Network';
    @endphp

    <main class="relative z-10 max-w-6xl mx-auto px-6 py-12 space-y-8">
        <header class="glass-pane p-8 grid gap-6 lg:grid-cols-[1.25fr,0.75fr]">
            <div class="space-y-4">
                <span class="chip">Global Earhquake Console</span>
                <h1 class="text-4xl md:text-5xl font-semibold leading-tight">Realtime Earthquake Intelligence Board</h1>
                <p class="text-slate-300 max-w-2xl">Cut through noise with a cinematic seismic feed. Compare magnitudes, explore regional stress, and anchor decisions with live telemetry.</p>
                <div class="status-bar">
                    <span>Active Source · {{ $sourceLabel }}</span>
                    <span>Network · {{ $sourceTypeLabel }}</span>
                    <span>Auto Sync · 5m cadence</span>
                </div>
            </div>
            <div class="relative">
                <div class="absolute inset-0 blur-3xl bg-gradient-to-br from-sky-500/30 to-purple-500/30"></div>
                <div class="relative glass-pane p-6 rounded-3xl h-full flex flex-col gap-4">
                    <p class="text-sm uppercase tracking-[0.3em] text-slate-400">Mission Controls</p>
                    <div class="flex flex-col gap-3">
                        <button id="refreshBtn" class="toggle-pill active text-center">Pulse Refresh</button>
                        <button id="nearMeBtn" class="toggle-pill text-center">Lock Near Me</button>
                        <p class="text-xs text-slate-400">Tip: Stay in grid view for macro or switch to stream for operator review.</p>
                    </div>
                </div>
            </div>
        </header>

        <section class="glass-pane p-6 space-y-6">
            <div class="control-dock grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="text-xs uppercase tracking-wider text-slate-400">Time Range</label>
                    <select id="timeRange">
                        <option value="hour">Past Hour</option>
                        <option value="day" selected>Past Day</option>
                        <option value="week">Past Week</option>
                        <option value="month">Past Month</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs uppercase tracking-wider text-slate-400">Minimum Magnitude</label>
                    <select id="minMagnitude">
                        <option value="all">All</option>
                        <option value="1">1.0+</option>
                        <option value="2.5">2.5+</option>
                        <option value="4.5" selected>4.5+</option>
                        <option value="6">6.0+</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs uppercase tracking-wider text-slate-400">Region Lens</label>
                    <select id="region">
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
                <div>
                    <label class="text-xs uppercase tracking-wider text-slate-400">Search Keyword</label>
                    <input id="searchTerm" type="text" placeholder="e.g. Alaska, Mexico" />
                </div>
            </div>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap gap-3">
                    <span class="chip">Sort feed</span>
                    <button id="sortMagnitude" class="toggle-pill active">Magnitude</button>
                    <button id="sortRecency" class="toggle-pill">Recency</button>
                </div>
                <div class="flex flex-wrap gap-3">
                    <span class="chip">View</span>
                    <button id="viewGrid" class="toggle-pill active">Grid Overview</button>
                    <button id="viewList" class="toggle-pill">Stream</button>
                </div>
            </div>
            <div id="filterSummary" class="flex flex-wrap gap-3"></div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4" id="stats"></section>

        <section>
            <div class="glass-pane p-6 rounded-3xl">
                <div class="flex flex-col gap-2 mb-4">
                    <h2 class="text-xl font-semibold">Signal Spotlights</h2>
                    <p class="text-slate-400 text-sm">Fast context snapshots. Use them for quick triage before drilling into specifics.</p>
                </div>
                <div class="grid gap-4 md:grid-cols-3" id="insights"></div>
            </div>
        </section>

        <div id="locationStatus" class="hidden glass-pane p-4 text-sm text-slate-200"></div>

        <section class="space-y-4">
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-2">
                    <h2 class="text-3xl font-semibold">Live Stream</h2>
                    <span class="chip">Tracking <span id="liveCount">0</span> events</span>
                </div>
                <p class="text-slate-400 text-sm">Scroll through the kinetic timeline or switch to grid view for macro comparisons.</p>
            </div>
            <div id="content" class="timeline space-y-4">
                <div class="glass-pane p-6 text-center text-slate-300">Loading telemetry...</div>
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
        const statsEl = document.getElementById('stats');
        const insightsEl = document.getElementById('insights');
        const contentEl = document.getElementById('content');
        const liveCountEl = document.getElementById('liveCount');
        const searchInput = document.getElementById('searchTerm');
        const filterSummaryEl = document.getElementById('filterSummary');
        const sortMagnitudeBtn = document.getElementById('sortMagnitude');
        const sortRecencyBtn = document.getElementById('sortRecency');
        const viewGridBtn = document.getElementById('viewGrid');
        const viewListBtn = document.getElementById('viewList');
        const locationStatus = document.getElementById('locationStatus');

        const loaderCard = `
            <div class="glass-pane p-5">
                <div class="skeleton h-6 w-1/3 rounded"></div>
                <div class="mt-4 space-y-3">
                    <div class="skeleton h-4 w-full rounded"></div>
                    <div class="skeleton h-4 w-3/4 rounded"></div>
                    <div class="skeleton h-4 w-2/3 rounded"></div>
                </div>
            </div>
        `;

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

        function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = value;
            return div.innerHTML;
        }

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
            refreshBtn.classList.add('active');

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
                applyFilters();
            } catch (error) {
                console.error(error);
                contentEl.innerHTML = `<div class="glass-pane p-6 text-center text-red-200">Feed temporarily unavailable. Please retry.</div>`;
            } finally {
                refreshBtn.disabled = false;
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

        function displayStats(list) {
            if (!list.length) {
                statsEl.innerHTML = `<div class="glass-pane p-6 text-center text-slate-300 md:col-span-2 xl:col-span-4">No telemetry for this combination.</div>`;
                return;
            }
            const magnitudes = list.map(e => e.properties.mag || 0);
            const depths = list.map(e => e.geometry.coordinates[2] || 0);
            const stats = [
                { label: 'Events', value: list.length, detail: 'matching filters' },
                { label: 'Peak magnitude', value: Math.max(...magnitudes).toFixed(1), detail: 'highest energy' },
                { label: 'Average magnitude', value: (magnitudes.reduce((a,b)=>a+b,0) / magnitudes.length).toFixed(1), detail: 'dataset mean' },
                { label: 'Average depth', value: `${(depths.reduce((a,b)=>a+b,0)/depths.length).toFixed(0)} km`, detail: 'focus depth' }
            ];
            statsEl.innerHTML = stats.map(stat => `
                <div class="data-capsule">
                    <span class="text-xs uppercase tracking-[0.3em] text-slate-400">${stat.label}</span>
                    <span class="text-3xl font-semibold">${stat.value}</span>
                    <span class="text-slate-400 text-sm">${stat.detail}</span>
                </div>
            `).join('');
        }

        function displayInsights(list) {
            if (!list.length) {
                insightsEl.innerHTML = `<div class="glass-pane p-4 text-center text-slate-300 md:col-span-3">Awaiting data...</div>`;
                return;
            }
            const sortedByMag = [...list].sort((a,b)=>(b.properties.mag||0)-(a.properties.mag||0));
            const sortedByTime = [...list].sort((a,b)=>(b.properties.time||0)-(a.properties.time||0));
            const sortedByDepth = [...list].sort((a,b)=>(a.geometry.coordinates[2]||0)-(b.geometry.coordinates[2]||0));
            const cards = [
                { title: 'Strongest Pulse', highlight: `${(sortedByMag[0].properties.mag||0).toFixed(1)} Mw`, place: sortedByMag[0].properties.place || 'Unknown', meta: formatTime(sortedByMag[0].properties.time) },
                { title: 'Newest Activity', highlight: formatRelative(sortedByTime[0].properties.time), place: sortedByTime[0].properties.place || 'Unknown', meta: `${(sortedByTime[0].properties.mag||0).toFixed(1)} Mw` },
                { title: 'Shallow Trigger', highlight: `${sortedByDepth[0].geometry.coordinates[2]?.toFixed(1) || 0} km`, place: sortedByDepth[0].properties.place || 'Unknown', meta: `${(sortedByDepth[0].properties.mag||0).toFixed(1)} Mw` }
            ];
            insightsEl.innerHTML = cards.map(card => `
                <article class="glass-pane p-5 rounded-2xl">
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">${card.title}</p>
                    <h3 class="text-3xl font-semibold mt-2">${card.highlight}</h3>
                    <p class="text-white/90">${card.place}</p>
                    <p class="text-slate-400 text-sm">${card.meta}</p>
                </article>
            `).join('');
        }

        function displayEarthquakes(list) {
            if (!list.length) {
                contentEl.innerHTML = `<div class="glass-pane p-6 text-center text-slate-300">No earthquakes in this slice.</div>`;
                return;
            }
            const layoutClass = viewMode === 'grid' ? 'grid gap-4 md:grid-cols-2' : 'space-y-4';
            const cards = list.map(eq => {
                const props = eq.properties;
                const coords = eq.geometry.coordinates;
                const mag = props.mag || 0;
                const distanceBlock = isNearMeMode && eq.distance !== undefined
                    ? `<div><p class="text-xs text-slate-400">Proximity</p><p class="text-white">${eq.distance.toFixed(0)} km</p></div>`
                    : `<div><p class="text-xs text-slate-400">Status</p><p class="text-white">${props.status || 'reviewed'}</p></div>`;
                const intensityClass = mag < 4.5 ? 'from-emerald-500/30 to-emerald-400/10' : mag < 6 ? 'from-amber-500/30 to-orange-500/10' : 'from-rose-500/30 to-red-500/10';
                return `
                    <article class="event-card" aria-label="Earthquake event">
                        <div class="flex gap-4 items-start">
                            <div class="timeline-dot mt-2"></div>
                            <div class="flex-1 space-y-2">
                                <div class="flex flex-wrap items-center gap-3">
                                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">${formatTime(props.time)}</p>
                                    <span class="text-xs rounded-full px-3 py-1 border border-white/10">${props.type || 'earthquake'}</span>
                                </div>
                                <h3 class="text-2xl font-semibold">${props.place || 'Unknown location'}</h3>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                    <div><p class="text-xs text-slate-400">Depth</p><p class="text-white">${coords[2]?.toFixed(1) || 0} km</p></div>
                                    <div><p class="text-xs text-slate-400">Coordinates</p><p class="text-white">${coords[1].toFixed(2)}°, ${coords[0].toFixed(2)}°</p></div>
                                    <div><p class="text-xs text-slate-400">Magnitude Type</p><p class="text-white">${props.magType || 'N/A'}</p></div>
                                    ${distanceBlock}
                                </div>
                            </div>
                            <div class="rounded-2xl px-4 py-2 bg-gradient-to-br ${intensityClass} text-right">
                                <p class="text-3xl font-bold">${mag.toFixed(1)}</p>
                                <p class="text-xs uppercase tracking-[0.3em]">${getIntensityLabel(mag)}</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-3 text-xs text-slate-400 mt-4">
                            <span>ID: ${props.code || props.ids || 'N/A'}</span>
                            ${props.url ? `<a href="${props.url}" target="_blank" class="text-sky-300">Detailed report →</a>` : ''}
                        </div>
                    </article>
                `;
            }).join('');

            contentEl.innerHTML = viewMode === 'grid' ? `<div class="${layoutClass}">${cards}</div>` : cards;
        }

        function updateFilterSummary() {
            const chips = [];
            const timeText = document.getElementById('timeRange').selectedOptions[0].text;
            const magText = document.getElementById('minMagnitude').selectedOptions[0].text;
            const regionText = document.getElementById('region').selectedOptions[0].text;
            chips.push(`Range: ${escapeHtml(timeText)}`);
            chips.push(`Magnitude: ${escapeHtml(magText)}`);
            chips.push(`Region: ${escapeHtml(regionText)}`);
            if (searchInput.value) chips.push(`Search: ${escapeHtml(searchInput.value)}`);
            chips.push(`Sort: ${sortMode === 'magnitude' ? 'Magnitude' : 'Recency'}`);
            filterSummaryEl.innerHTML = chips.map(label => `<span class="chip">${label}</span>`).join('');
        }

        function formatTime(timestamp) {
            if (!timestamp) return 'Unknown';
            return new Date(timestamp).toLocaleString();
        }

        function formatRelative(timestamp) {
            if (!timestamp) return 'Unknown';
            const diff = Date.now() - timestamp;
            const mins = Math.floor(diff / 60000);
            if (mins < 1) return 'Just now';
            if (mins < 60) return `${mins}m ago`;
            const hours = Math.floor(mins / 60);
            if (hours < 24) return `${hours}h ago`;
            return `${Math.floor(hours/24)}d ago`;
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
            const a = Math.sin(dLat/2) ** 2 + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon/2) ** 2;
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        }

        function showLocationStatus(message, type = 'info') {
            locationStatus.textContent = message;
            locationStatus.classList.remove('hidden');
            locationStatus.style.borderColor = type === 'success' ? 'rgba(74, 222, 128, 0.4)' : type === 'error' ? 'rgba(248, 113, 113, 0.4)' : 'var(--stroke)';
            if (type !== 'error') {
                setTimeout(() => locationStatus.classList.add('hidden'), 5000);
            }
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
            isNearMeMode = false;
            nearMeBtn.classList.remove('active');
            applyFilters();
        });
        document.getElementById('timeRange').addEventListener('change', () => fetchEarthquakes());
        document.getElementById('minMagnitude').addEventListener('change', () => fetchEarthquakes());

        nearMeBtn.addEventListener('click', () => {
            if (isNearMeMode) {
                isNearMeMode = false;
                nearMeBtn.classList.remove('active');
                showLocationStatus('Near-me mode disabled. Resuming global sort.', 'info');
                applyFilters();
                return;
            }
            if (!navigator.geolocation) {
                showLocationStatus('Geolocation unsupported in this browser.', 'error');
                return;
            }
            showLocationStatus('Acquiring location lock...', 'info');
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    userLocation = { latitude: position.coords.latitude, longitude: position.coords.longitude };
                    isNearMeMode = true;
                    nearMeBtn.classList.add('active');
                    showLocationStatus(`Locked at ${userLocation.latitude.toFixed(2)}°, ${userLocation.longitude.toFixed(2)}°`, 'success');
                    applyFilters();
                },
                (error) => {
                    showLocationStatus('Unable to retrieve location: ' + error.message, 'error');
                }
            );
        });

        fetchEarthquakes();
        setInterval(() => fetchEarthquakes(false), 300000);
    </script>
</body>
</html>
