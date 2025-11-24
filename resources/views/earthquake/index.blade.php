<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Global Earthquake Tracker</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
          crossorigin=""/>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    
</head>
<body>
    @php
        $sourceLabel = $activeSourceName ?? 'Global Live Feed';
        $sourceTypeLabel = $activeSourceType ?? 'USGS / EMSC';
    @endphp
    
    <!-- Settings Button -->
    <button id="settingsBtn" class="settings-btn" aria-label="Settings">
        ⚙️
    </button>
    
    <!-- Settings Menu -->
    <div id="settingsMenu" class="settings-menu">
        <h3>Theme</h3>
        <div class="theme-option" data-theme="system">
            <span class="icon">💻</span>
            <span class="label">System Default</span>
            <span class="checkmark">✓</span>
        </div>
        <div class="theme-option" data-theme="light">
            <span class="icon">☀️</span>
            <span class="label">Light</span>
            <span class="checkmark">✓</span>
        </div>
        <div class="theme-option" data-theme="dark">
            <span class="icon">🌙</span>
            <span class="label">Dark</span>
            <span class="checkmark">✓</span>
        </div>
    </div>
    
    <!-- Map Container -->
    <div id="map"></div>
    
    <!-- Floating Header -->
    <header class="app-header">
        <h1>Global Earthquake Tracker</h1>
        <div style="text-align: center;">
            <span class="status-chip">● Live: {{ $sourceLabel }}</span>
        </div>
    </header>
    
    <!-- Floating Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Recent Earthquakes</h2>
            <div style="display: flex; gap: 0.5rem; margin-top: 0.75rem;">
                <button id="refreshBtn" class="btn active" style="flex: 1;">
                    ↻ Refresh
                </button>
                <button id="nearMeBtn" class="btn" style="flex: 1;">
                    📍 Near Me
                </button>
            </div>
        </div>
        
        <div class="sidebar-content">
            <!-- Controls -->
            <div class="controls">
                <div class="control-group">
                    <label>Time Range</label>
                    <select id="timeRange">
                        <option value="hour">Past Hour</option>
                        <option value="day" selected>Past Day</option>
                        <option value="week">Past Week</option>
                        <option value="month">Past Month</option>
                    </select>
                </div>
                
                <div class="control-group">
                    <label>Min Magnitude</label>
                    <select id="minMagnitude">
                        <option value="all">All</option>
                        <option value="1">1.0+</option>
                        <option value="2.5">2.5+</option>
                        <option value="4.5" selected>4.5+</option>
                        <option value="6">6.0+</option>
                    </select>
                </div>
                
                <div class="control-group">
                    <label>Region</label>
                    <select id="region">
                        <option value="global">Global</option>
                        <option value="asia">Asia</option>
                        <option value="europe">Europe</option>
                        <option value="northamerica">North America</option>
                        <option value="southamerica">South America</option>
                        <option value="africa">Africa</option>
                        <option value="oceania">Oceania</option>
                    </select>
                </div>
                
                <div class="control-group">
                    <label>Search</label>
                    <input id="searchTerm" type="text" placeholder="Location..." />
                </div>
            </div>
            
            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="label">Events</div>
                    <div class="value" id="statTotal">0</div>
                </div>
                <div class="stat-card">
                    <div class="label">Peak Mag</div>
                    <div class="value" id="statPeak">0.0</div>
                </div>
                <div class="stat-card">
                    <div class="label">Avg Mag</div>
                    <div class="value" id="statAvg">0.0</div>
                </div>
                <div class="stat-card">
                    <div class="label">Avg Depth</div>
                    <div class="value" id="statDepth">0 km</div>
                </div>
            </div>
            
            <!-- Earthquake List -->
            <div id="eqList" class="eq-list">
                <div class="loading">
                    <div class="spinner"></div>
                    <p>Loading earthquake data...</p>
                </div>
            </div>
            
            <!-- Load More Button -->
            <div id="loadMoreContainer" class="load-more-container hidden">
                <button id="loadMoreBtn" class="load-more-btn">
                    ↓ Load More Earthquakes
                </button>
            </div>
        </div>
    </aside>
    
    <!-- Earthquake Detail Modal -->
    <div id="eqModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Earthquake Details</h2>
                <button class="modal-close" onclick="closeEarthquakeModal()">×</button>
            </div>
            <div class="modal-body">
                <div class="eq-detail-grid" id="modalDetails">
                    <!-- Details will be inserted here -->
                </div>
                <div class="news-section">
                    <h3>📰 Related News</h3>
                    <div id="newsContainer">
                        <div class="news-loading">
                            <div class="spinner"></div>
                            <p>Loading news...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" 
            crossorigin=""></script>
    
    
</body>
</html>
