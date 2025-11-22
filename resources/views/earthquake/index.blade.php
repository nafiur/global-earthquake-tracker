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
    
    <style>
        :root {
            color-scheme: dark;
            --surface: rgba(15, 23, 42, 0.85);
            --surface-light: rgba(30, 41, 59, 0.75);
            --stroke: rgba(148, 163, 184, 0.15);
            --primary: #0ea5e9;
            --accent: #8b5cf6;
            --danger: #ef4444;
            --warning: #f59e0b;
            --success: #10b981;
            --bg-primary: #0f172a;
            --bg-map: #0f172a;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --marker-light: #10b981;
            --marker-moderate: #f59e0b;
            --marker-strong: #ef4444;
        }
        
        /* Light Theme Variables */
        [data-theme="light"] {
            color-scheme: light;
            --surface: rgba(255, 255, 255, 0.9);
            --surface-light: rgba(241, 245, 249, 0.85);
            --stroke: rgba(71, 85, 105, 0.2);
            --bg-primary: #f1f5f9;
            --bg-map: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #475569;
            --marker-light: #059669;
            --marker-moderate: #d97706;
            --marker-strong: #dc2626;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            overflow-x: hidden;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        /* Map Container */
        #map {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            z-index: 1;
        }
        
        /* Floating Header */
        .app-header {
            position: fixed;
            top: 1rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            background: var(--surface);
            backdrop-filter: blur(20px);
            border: 1px solid var(--stroke);
            border-radius: 24px;
            padding: 1rem 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            min-width: 320px;
            max-width: 90%;
        }
        
        .app-header h1 {
            font-size: 1.25rem;
            font-weight: 600;
            text-align: center;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .status-chip {
            display: inline-block;
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            background: rgba(14, 165, 233, 0.15);
            border: 1px solid rgba(14, 165, 233, 0.3);
            color: var(--primary);
            margin-top: 0.5rem;
        }
        
        /* Floating Sidebar */
        .sidebar {
            position: fixed;
            top: 6.5rem;
            right: 1rem;
            width: 400px;
            max-width: calc(100vw - 2rem);
            max-height: calc(100vh - 8rem);
            background: var(--surface);
            backdrop-filter: blur(20px);
            border: 1px solid var(--stroke);
            border-radius: 24px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.6);
            z-index: 999;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--stroke);
            flex-shrink: 0;
        }
        
        .sidebar-header h2 {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .sidebar-content {
            padding: 1rem;
            overflow-y: auto;
            flex-grow: 1;
        }
        
        /* Custom Scrollbar */
        .sidebar-content::-webkit-scrollbar {
            width: 8px;
        }
        
        .sidebar-content::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.5);
            border-radius: 10px;
        }
        
        .sidebar-content::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.3);
            border-radius: 10px;
        }
        
        .sidebar-content::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.5);
        }
        
        /* Controls */
        .controls {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .control-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }
        
        .control-group label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        .control-group select,
        .control-group input {
            width: 100%;
            padding: 0.65rem 0.9rem;
            background: var(--surface-light);
            border: 1px solid var(--stroke);
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 0.875rem;
            font-family: inherit;
            transition: all 0.2s ease;
        }
        
        .control-group select:focus,
        .control-group input:focus {
            outline: none;
            border-color: var(--primary);
            background: var(--surface);
        }
        
        .btn {
            padding: 0.75rem 1.25rem;
            border: 1px solid var(--stroke);
            border-radius: 12px;
            background: var(--surface-light);
            color: #f1f5f9;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            font-family: inherit;
        }
        
        .btn:hover {
            background: rgba(14, 165, 233, 0.15);
            border-color: var(--primary);
            transform: translateY(-1px);
        }
        
        .btn.active {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.25), rgba(139, 92, 246, 0.25));
            border-color: var(--primary);
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .stat-card {
            background: rgba(15, 23, 42, 0.75);
            border: 1px solid var(--stroke);
            border-radius: 16px;
            padding: 1rem;
            text-align: center;
        }
        
        .stat-card .label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            margin-bottom: 0.4rem;
            font-weight: 500;
        }
        
        .stat-card .value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        
        /* Earthquake List */
        .eq-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .eq-card {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--stroke);
            border-radius: 16px;
            padding: 1rem;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .eq-card:hover {
            background: rgba(30, 41, 59, 0.75);
            border-color: rgba(14, 165, 233, 0.4);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .eq-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.75rem;
        }
        
        .eq-magnitude {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
        }
        
        .mag-light { color: var(--marker-light); }
        .mag-moderate { color: var(--marker-moderate); }
        .mag-strong { color: var(--marker-strong); }
        
        .eq-time {
            font-size: 0.7rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .eq-location {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .eq-details {
            display: flex;
            gap: 1rem;
            font-size: 0.75rem;
            color: var(--text-secondary);
        }
        
        .eq-details span {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        /* Loading State */
        .loading {
            text-align: center;
            padding: 2rem;
            color: var(--text-secondary);
        }
        
        .spinner {
            border: 3px solid rgba(148, 163, 184, 0.2);
            border-top: 3px solid var(--primary);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 1rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .app-header {
                top: 0.5rem;
                padding: 0.75rem 1rem;
                border-radius: 16px;
            }
            
            .app-header h1 {
                font-size: 1rem;
            }
            
            .sidebar {
                top: auto;
                bottom: 0;
                right: 0;
                left: 0;
                width: 100%;
                max-width: 100%;
                max-height: 50vh;
                border-radius: 24px 24px 0 0;
            }
            
            .controls {
                grid-template-columns: 1fr;
            }
        }
        
        /* Leaflet Popup Customization */
        .leaflet-popup-content-wrapper {
            background: var(--surface);
            color: var(--text-primary);
            border-radius: 16px;
            border: 1px solid var(--stroke);
        }
        
        .leaflet-popup-content {
            margin: 1rem;
            font-family: 'Inter', sans-serif;
        }
        
        .popup-tip {
            background: var(--surface);
        }
        
        .popup-magnitude {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .popup-location {
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .popup-details {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }
        
        /* Load More Button */
        .load-more-container {
            padding: 1rem 0;
            text-align: center;
        }
        
        .load-more-btn {
            width: 100%;
            padding: 0.875rem 1.5rem;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.15), rgba(139, 92, 246, 0.15));
            border: 1px solid rgba(14, 165, 233, 0.3);
            border-radius: 12px;
            color: #f1f5f9;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s ease;
            font-family: inherit;
        }
        
        .load-more-btn:hover {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.25), rgba(139, 92, 246, 0.25));
            border-color: rgba(14, 165, 233, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(14, 165, 233, 0.3);
        }
        
        .load-more-btn:active {
            transform: translateY(0);
        }
        
        .hidden {
            display: none;
        }
        
        /* Settings Button */
        .settings-btn {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1001;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--surface);
            backdrop-filter: blur(20px);
            border: 1px solid var(--stroke);
            color: var(--text-primary);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            transition: all 0.25s ease;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }
        
        .settings-btn:hover {
            transform: rotate(90deg);
            border-color: var(--primary);
            box-shadow: 0 12px 32px rgba(14, 165, 233, 0.4);
        }
        
        /* Settings Menu */
        .settings-menu {
            position: fixed;
            top: 5rem;
            right: 1rem;
            z-index: 1002;
            min-width: 220px;
            background: var(--surface);
            backdrop-filter: blur(20px);
            border: 1px solid var(--stroke);
            border-radius: 16px;
            padding: 1rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            opacity: 0;
            transform: translateY(-10px);
            pointer-events: none;
            transition: all 0.25s ease;
        }
        
        .settings-menu.active {
            opacity: 1;
            transform: translateY(0);
            pointer-events: all;
        }
        
        .settings-menu h3 {
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            margin-bottom: 0.75rem;
        }
        
        .theme-option {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 0.5rem;
            background: transparent;
            border: 1px solid transparent;
        }
        
        .theme-option:hover {
            background: var(--surface-light);
            border-color: var(--stroke);
        }
        
        .theme-option.active {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.15), rgba(139, 92, 246, 0.15));
            border-color: var(--primary);
        }
        
        .theme-option .icon {
            font-size: 1.25rem;
            width: 24px;
            text-align: center;
        }
        
        .theme-option .label {
            flex: 1;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary);
        }
        
        .theme-option .checkmark {
            font-size: 1rem;
            color: var(--primary);
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        
        .theme-option.active .checkmark {
            opacity: 1;
        }
        
        @media (max-width: 768px) {
            .settings-btn {
                top: 0.5rem;
                right: 0.5rem;
                width: 44px;
                height: 44px;
            }
            
            .settings-menu {
                right: 0.5rem;
                top: 4.5rem;
            }
        }
        
        /* Earthquake Detail Modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
            padding: 1rem;
        }
        
        .modal-overlay.active {
            opacity: 1;
            pointer-events: all;
        }
        
        .modal-content {
            background: var(--surface);
            border: 1px solid var(--stroke);
            border-radius: 24px;
            max-width: 700px;
            width: 100%;
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transform: scale(0.9);
            transition: transform 0.3s ease;
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.6);
        }
        
        .modal-overlay.active .modal-content {
            transform: scale(1);
        }
        
        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--stroke);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .modal-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }
        
        .modal-close {
            background: transparent;
            border: none;
            font-size: 1.5rem;
            color: var(--text-secondary);
            cursor: pointer;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .modal-close:hover {
            background: var(--surface-light);
            color: var(--text-primary);
        }
        
        .modal-body {
            padding: 1.5rem;
            overflow-y: auto;
            flex: 1;
        }
        
        .eq-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .eq-detail-item {
            background: var(--surface-light);
            padding: 1rem;
            border-radius: 12px;
            border: 1px solid var(--stroke);
        }
        
        .eq-detail-item .detail-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .eq-detail-item .detail-value {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .news-section {
            margin-top: 1.5rem;
        }
        
        .news-section h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .news-loading {
            text-align: center;
            padding: 2rem;
            color: var(--text-secondary);
        }
        
        .news-article {
            background: var(--surface-light);
            border: 1px solid var(--stroke);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.2s ease;
        }
        
        .news-article:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
        }
        
        .news-article h4 {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }
        
        .news-article p {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-bottom: 0.75rem;
            line-height: 1.5;
        }
        
        .news-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.75rem;
            color: var(--text-secondary);
        }
        
        .news-source {
            font-weight: 500;
        }
        
        .news-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .news-link:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .modal-content {
                max-height: 80vh;
            }
            
            .eq-detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
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
    
    <script>
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
                document.documentElement.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
            } else {
                document.documentElement.setAttribute('data-theme', theme);
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
                return;
            }
            
            const mags = list.map(e => e.properties.mag || 0);
            const depths = list.map(e => e.geometry.coordinates[2] || 0);
            
            document.getElementById('statTotal').textContent = list.length;
            document.getElementById('statPeak').textContent = Math.max(...mags).toFixed(1);
            document.getElementById('statAvg').textContent = (mags.reduce((a,b) => a+b, 0) / mags.length).toFixed(1);
            document.getElementById('statDepth').textContent = Math.round(depths.reduce((a,b) => a+b, 0) / depths.length) + ' km';
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
        }
        
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
        });
        
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
        
        // Initialize
        initTheme();
        initMap();
        fetchEarthquakes();
        
        // Auto-refresh every 5 minutes
        setInterval(() => fetchEarthquakes(), 300000);
    </script>
</body>
</html>
