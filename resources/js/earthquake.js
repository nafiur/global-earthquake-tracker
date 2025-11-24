document.addEventListener('DOMContentLoaded', () => {
// Global State
        let map;
        let earthquakes = [];
        let filteredEarthquakes = [];
        let markers = [];
        let userLocation = null;
        let isNearMeMode = false;
        let currentDisplayCount = 20;
        const ITEMS_PER_PAGE = 20;
        let currentTheme = 'dark';
        
        // Theme Management
        function initTheme() {
            const savedTheme = localStorage.getItem('earthquakeTrackerTheme') || 'dark';
            applyTheme(savedTheme);
        }
        
        function setThemeMode(mode) {
            if (mode === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.removeAttribute('data-theme');
            }
        }
        
        function applyTheme(theme) {
            currentTheme = theme;
            localStorage.setItem('earthquakeTrackerTheme', theme);
            
            // Update active state in menu
            document.querySelectorAll('.theme-option').forEach(option => {
                option.classList.remove('active');
                if (option.dataset.theme === theme) {
                    option.classList.add('active');
                }
            });
            
            // Apply theme
            if (theme === 'system') {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                setThemeMode(prefersDark ? 'dark' : 'light');
            } else if (theme === 'dark') {
                setThemeMode('dark');
            } else {
                setThemeMode('light');
            }
            
            // Update map tiles if map exists
            if (map) {
                updateMapTiles();
                // Refresh markers to update colors
                if (filteredEarthquakes.length > 0) {
                    updateMap(filteredEarthquakes);
                }
            }
        }
        
        function updateMapTiles() {
            const theme = document.documentElement.getAttribute('data-theme');
            const isDark = theme === 'dark';
            
            // Remove existing tile layer
            map.eachLayer(layer => {
                if (layer instanceof L.TileLayer) {
                    map.removeLayer(layer);
                }
            });
            
            // Add appropriate tile layer
            const tileUrl = isDark 
                ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'
                : 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
            
            L.tileLayer(tileUrl, {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                subdomains: 'abcd',
                maxZoom: 20
            }).addTo(map);
        }
        
        // Region Bounds
        const regions = {
            global: null,
            asia: { minLat: -10, maxLat: 55, minLon: 60, maxLon: 150 },
            europe: { minLat: 35, maxLat: 71, minLon: -10, maxLon: 40 },
            northamerica: { minLat: 15, maxLat: 72, minLon: -170, maxLon: -50 },
            southamerica: { minLat: -56, maxLat: 13, minLon: -82, maxLon: -34 },
            africa: { minLat: -35, maxLat: 37, minLon: -18, maxLon: 52 },
            oceania: { minLat: -50, maxLat: 0, minLon: 110, maxLon: 180 }
        };
        
        // Initialize Map
        function initMap() {
            map = L.map('map', {
                center: [20, 0],
                zoom: 2,
                zoomControl: true,
                minZoom: 2,
                maxZoom: 18
            });
            
            // Dark theme map tiles
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                subdomains: 'abcd',
                maxZoom: 20
            }).addTo(map);
        }
        
        // Fetch Earthquakes
        async function fetchEarthquakes() {
            const timeRange = document.getElementById('timeRange').value;
            const minMag = document.getElementById('minMagnitude').value;
            
            document.getElementById('eqList').innerHTML = `
                <div class="loading">
                    <div class="spinner"></div>
                    <p>Loading earthquake data...</p>
                </div>
            `;
            
            try {
                const url = new URL(window.location.origin + '/earthquakes/data');
                url.searchParams.append('timeRange', timeRange);
                url.searchParams.append('minMagnitude', minMag);
                
                const response = await fetch(url);
                if (!response.ok) throw new Error('Failed to fetch data');
                
                const data = await response.json();
                earthquakes = data.features || [];
                applyFilters();
            } catch (error) {
                console.error(error);
                document.getElementById('eqList').innerHTML = `
                    <div class="loading">
                        <p style="color: #ef4444;">Failed to load data. Please try again.</p>
                    </div>
                `;
            }
        }
        
        // Apply Filters
        function applyFilters() {
            const region = document.getElementById('region').value;
            const searchTerm = (document.getElementById('searchTerm').value || '').toLowerCase();
            
            let filtered = earthquakes.filter(eq => {
                const coords = eq.geometry.coordinates;
                const matchesRegion = isInRegion(coords[1], coords[0], region);
                const matchesSearch = !searchTerm || (eq.properties.place || '').toLowerCase().includes(searchTerm);
                return matchesRegion && matchesSearch;
            });
            
            if (isNearMeMode && userLocation) {
                filtered.forEach(eq => {
                    const coords = eq.geometry.coordinates;
                    eq.distance = calculateDistance(
                        userLocation.latitude, 
                        userLocation.longitude, 
                        coords[1], 
                        coords[0]
                    );
                });
                filtered.sort((a, b) => a.distance - b.distance);
            } else {
                filtered.sort((a, b) => (b.properties.mag || 0) - (a.properties.mag || 0));
            }
            
            updateStats(filtered);
            updateMap(filtered);
            updateList(filtered);
        }
        
        // Check if in Region
        function isInRegion(lat, lon, region) {
            if (region === 'global' || !regions[region]) return true;
            const bounds = regions[region];
            return lat >= bounds.minLat && lat <= bounds.maxLat && 
                   lon >= bounds.minLon && lon <= bounds.maxLon;
        }
        
        // Calculate Distance
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) ** 2 + 
                      Math.cos(lat1 * Math.PI / 180) * 
                      Math.cos(lat2 * Math.PI / 180) * 
                      Math.sin(dLon/2) ** 2;
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        }
        
        // Update Stats
        function updateStats(list) {
            if (!list.length) {
                document.getElementById('statTotal').textContent = '0';
                document.getElementById('statPeak').textContent = '0.0';
                document.getElementById('statAvg').textContent = '0.0';
                document.getElementById('statDepth').textContent = '0 km';
                document.getElementById('statToday').textContent = '0';
                document.getElementById('statMonth').textContent = '0';
                return;
            }
            
            const mags = list.map(e => e.properties.mag || 0);
            const depths = list.map(e => e.geometry.coordinates[2] || 0);
            const now = new Date();
            
            document.getElementById('statTotal').textContent = list.length;
            document.getElementById('statPeak').textContent = Math.max(...mags).toFixed(1);
            document.getElementById('statAvg').textContent = (mags.reduce((a,b) => a+b, 0) / mags.length).toFixed(1);
            document.getElementById('statDepth').textContent = Math.round(depths.reduce((a,b) => a+b, 0) / depths.length) + ' km';
            
            const todayCount = list.filter(eq => {
                const date = new Date(eq.properties.time);
                return date.getFullYear() === now.getFullYear() &&
                       date.getMonth() === now.getMonth() &&
                       date.getDate() === now.getDate();
            }).length;
            
            const monthCount = list.filter(eq => {
                const date = new Date(eq.properties.time);
                return date.getFullYear() === now.getFullYear() &&
                       date.getMonth() === now.getMonth();
            }).length;
            
            document.getElementById('statToday').textContent = todayCount.toString();
            document.getElementById('statMonth').textContent = monthCount.toString();
        }
        
        // Update Map
        function updateMap(list) {
            // Clear existing markers
            markers.forEach(marker => map.removeLayer(marker));
            markers = [];
            
            if (!list.length) return;
            
            // Add new markers
            list.forEach(eq => {
                const coords = eq.geometry.coordinates;
                const mag = eq.properties.mag || 0;
                const place = eq.properties.place || 'Unknown';
                
                // Marker size and color based on magnitude
                const radius = Math.max(mag * 2, 4);
                const color = mag < 4.5 
                    ? getComputedStyle(document.documentElement).getPropertyValue('--marker-light').trim()
                    : mag < 6 
                    ? getComputedStyle(document.documentElement).getPropertyValue('--marker-moderate').trim()
                    : getComputedStyle(document.documentElement).getPropertyValue('--marker-strong').trim();
                
                const marker = L.circleMarker([coords[1], coords[0]], {
                    radius: radius,
                    fillColor: color,
                    color: '#fff',
                    weight: 1,
                    opacity: 0.8,
                    fillOpacity: 0.6
                }).addTo(map);
                
                // Popup
                const popupContent = `
                    <div class="popup-magnitude" style="color: ${color};">${mag.toFixed(1)}</div>
                    <div class="popup-location">${place}</div>
                    <div class="popup-details">
                        Depth: ${coords[2]?.toFixed(1) || 0} km<br>
                        ${new Date(eq.properties.time).toLocaleString()}
                    </div>
                `;
                marker.bindPopup(popupContent);
                
                markers.push(marker);
            });
            
            // Fit map to markers
            if (markers.length > 0) {
                const group = new L.featureGroup(markers);
                map.fitBounds(group.getBounds().pad(0.1));
            }
        }
        
        // Update List
        function updateList(list) {
            const listEl = document.getElementById('eqList');
            filteredEarthquakes = list;
            
            if (!list.length) {
                listEl.innerHTML = '<div class="loading"><p>No earthquakes found.</p></div>';
                document.getElementById('loadMoreContainer').classList.add('hidden');
                return;
            }
            
            // Reset display count when filters change
            currentDisplayCount = ITEMS_PER_PAGE;
            renderList();
        }
        
        // Render List with Pagination
        function renderList() {
            const listEl = document.getElementById('eqList');
            const loadMoreContainer = document.getElementById('loadMoreContainer');
            const loadMoreBtn = document.getElementById('loadMoreBtn');
            
            const itemsToShow = filteredEarthquakes.slice(0, currentDisplayCount);
            
            listEl.innerHTML = itemsToShow.map((eq, index) => {
                const mag = eq.properties.mag || 0;
                const magClass = mag < 4.5 ? 'mag-light' : mag < 6 ? 'mag-moderate' : 'mag-strong';
                const coords = eq.geometry.coordinates;
                
                return `
                    <div class="eq-card" onclick="showEarthquakeDetails(${index})">
                        <div class="eq-card-header">
                            <div>
                                <div class="eq-magnitude ${magClass}">${mag.toFixed(1)}</div>
                            </div>
                            <div class="eq-time">${formatRelative(eq.properties.time)}</div>
                        </div>
                        <div class="eq-location">${eq.properties.place || 'Unknown location'}</div>
                        <div class="eq-details">
                            <span>📍 ${coords[2]?.toFixed(1) || 0} km</span>
                            <span>📊 ${eq.properties.magType || 'N/A'}</span>
                            ${eq.distance !== undefined ? `<span>↔ ${eq.distance.toFixed(0)} km</span>` : ''}
                        </div>
                    </div>
                `;
            }).join('');
            
            // Show/hide Load More button
            if (currentDisplayCount < filteredEarthquakes.length) {
                loadMoreContainer.classList.remove('hidden');
                const remaining = filteredEarthquakes.length - currentDisplayCount;
                loadMoreBtn.textContent = `↓ Load More (${remaining} remaining)`;
            } else {
                loadMoreContainer.classList.add('hidden');
            }
        }
        
        // Fly to Earthquake on Map
        function flyToEarthquake(lat, lon) {
            map.flyTo([lat, lon], 8, {
                duration: 1
            });
        }
        
        // Format Relative Time
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
        
        // Event Listeners
        document.getElementById('refreshBtn').addEventListener('click', fetchEarthquakes);
        document.getElementById('timeRange').addEventListener('change', fetchEarthquakes);
        document.getElementById('minMagnitude').addEventListener('change', fetchEarthquakes);
        document.getElementById('region').addEventListener('change', () => {
            isNearMeMode = false;
            document.getElementById('nearMeBtn').classList.remove('active');
            applyFilters();
        });
        document.getElementById('searchTerm').addEventListener('input', applyFilters);
        
        document.getElementById('loadMoreBtn').addEventListener('click', () => {
            currentDisplayCount += ITEMS_PER_PAGE;
            renderList();
        });
        
        // Show Earthquake Details Modal
        function showEarthquakeDetails(index) {
            const eq = filteredEarthquakes[index];
            if (!eq) return;
            
            const props = eq.properties;
            const coords = eq.geometry.coordinates;
            const mag = props.mag || 0;
            
            // Update modal title
            document.getElementById('modalTitle').textContent = props.place || 'Unknown Location';
            
            // Update details
            const detailsHtml = `
                <div class="eq-detail-item">
                    <div class="detail-label">Magnitude</div>
                    <div class="detail-value">${mag.toFixed(1)} ${props.magType || 'Mw'}</div>
                </div>
                <div class="eq-detail-item">
                    <div class="detail-label">Depth</div>
                    <div class="detail-value">${coords[2]?.toFixed(1) || 0} km</div>
                </div>
                <div class="eq-detail-item">
                    <div class="detail-label">Time</div>
                    <div class="detail-value">${new Date(props.time).toLocaleString()}</div>
                </div>
                <div class="eq-detail-item">
                    <div class="detail-label">Status</div>
                    <div class="detail-value">${props.status || 'reviewed'}</div>
                </div>
                <div class="eq-detail-item">
                    <div class="detail-label">Coordinates</div>
                    <div class="detail-value">${coords[1].toFixed(4)}°, ${coords[0].toFixed(4)}°</div>
                </div>
                <div class="eq-detail-item">
                    <div class="detail-label">Event ID</div>
                    <div class="detail-value">${props.code || props.ids || 'N/A'}</div>
                </div>
            `;
            
            document.getElementById('modalDetails').innerHTML = detailsHtml;
            
            // Show modal
            document.getElementById('eqModal').classList.add('active');
            
            // Fetch news
            fetchEarthquakeNews(props.place, mag, props.time);
            
            // Fly to location on map
            flyToEarthquake(coords[1], coords[0]);
        }
        
        // Close Modal
        function closeEarthquakeModal() {
            document.getElementById('eqModal').classList.remove('active');
        }
        
        // Fetch Earthquake News
        async function fetchEarthquakeNews(location, magnitude, timestamp) {
            const newsContainer = document.getElementById('newsContainer');
            newsContainer.innerHTML = `
                <div class="news-loading">
                    <div class="spinner"></div>
                    <p>Loading news...</p>
                </div>
            `;
            
            try {
                // First, get the API key from backend
                const apiKeyResponse = await fetch('/api/settings/news-api-key');
                const apiKeyData = await apiKeyResponse.json();
                const apiKey = apiKeyData.api_key;
                
                if (!apiKey) {
                    newsContainer.innerHTML = `
                        <div class="news-loading">
                            <p>News API key not configured.</p>
                            <p style="margin-top: 0.5rem; font-size: 0.875rem;">
                                Please configure the NewsAPI key in the admin settings to enable news features.
                            </p>
                        </div>
                    `;
                    return;
                }
                
                // Extract location keywords
                const locationMatch = location?.match(/([A-Za-z\s]+)$/);
                const searchLocation = locationMatch ? locationMatch[1].trim() : 'earthquake';
                
                // Search for earthquake news
                const searchQuery = `earthquake ${searchLocation} magnitude ${magnitude.toFixed(1)}`;
                
                const response = await fetch(
                    `https://newsapi.org/v2/everything?q=${encodeURIComponent(searchQuery)}&sortBy=publishedAt&pageSize=5&apiKey=${apiKey}`
                );
                
                if (!response.ok) {
                    throw new Error('Failed to fetch news');
                }
                
                const data = await response.json();
                
                if (!data.articles || data.articles.length === 0) {
                    newsContainer.innerHTML = '<div class="news-loading"><p>No recent news articles found for this earthquake.</p></div>';
                    return;
                }
                
                newsContainer.innerHTML = data.articles.map(article => `
                    <div class="news-article">
                        <h4>${article.title}</h4>
                        <p>${article.description || 'No description available.'}</p>
                        <div class="news-meta">
                            <span class="news-source">${article.source.name}</span>
                            <a href="${article.url}" target="_blank" class="news-link">Read more →</a>
                        </div>
                    </div>
                `).join('');
                
            } catch (error) {
                console.error('Error fetching news:', error);
                // Show fallback with general earthquake news search
                const searchLocation = location?.match(/([A-Za-z\s]+)$/)?.[1]?.trim() || 'earthquake';
                const searchQuery = `earthquake ${searchLocation} magnitude ${magnitude.toFixed(1)}`;
                newsContainer.innerHTML = `
                    <div class="news-loading">
                        <p>Unable to load news at this time.</p>
                        <p style="margin-top: 0.5rem;">
                            <a href="https://news.google.com/search?q=${encodeURIComponent(searchQuery)}" 
                               target="_blank" 
                               class="news-link">
                               Search on Google News →
                            </a>
                        </p>
                    </div>
                `;
            }
        }
        
        document.getElementById('nearMeBtn').addEventListener('click', () => {
            if (isNearMeMode) {
                isNearMeMode = false;
                document.getElementById('nearMeBtn').classList.remove('active');
                applyFilters();
                return;
            }
            
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser');
                return;
            }
            
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    userLocation = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    };
                    isNearMeMode = true;
                    document.getElementById('nearMeBtn').classList.add('active');
                    map.flyTo([userLocation.latitude, userLocation.longitude], 6);
                    applyFilters();
                },
                (error) => {
                    alert('Unable to retrieve your location: ' + error.message);
                }
            );
        });
        
        // Settings Button Event Listeners
        const settingsBtn = document.getElementById('settingsBtn');
        const settingsMenu = document.getElementById('settingsMenu');
        
        settingsBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            settingsMenu.classList.toggle('active');
        });
        
        // Close settings menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!settingsMenu.contains(e.target) && !settingsBtn.contains(e.target)) {
                settingsMenu.classList.remove('active');
            }
        });
        
        // Theme option click handlers
        document.querySelectorAll('.theme-option').forEach(option => {
            option.addEventListener('click', () => {
                const theme = option.dataset.theme;
                applyTheme(theme);
                settingsMenu.classList.remove('active');
            });
        });
        
        // Listen to system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (currentTheme === 'system') {
                applyTheme('system');
            }
        });
        
        // Expose modal handlers globally for inline calls
        window.showEarthquakeDetails = showEarthquakeDetails;
        window.closeEarthquakeModal = closeEarthquakeModal;
        
        // Initialize
        initTheme();
        initMap();
        fetchEarthquakes();
        
        // Auto-refresh every 5 minutes
        setInterval(() => fetchEarthquakes(), 300000);
});
