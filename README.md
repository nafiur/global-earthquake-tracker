# Global Earhquake Tracker

Global Earhquake Tracker is a Laravel 12 application that elevates the old single-page quake visualizer into a modern seismic intelligence console. It blends a Leaflet-powered world map, cinematic UI, live telemetry, contextual insights, and admin tooling so operators can monitor and curate global earthquake feeds.

## ✨ Highlights

- **Immersive Console Experience** – Floating glass panels, theme switching, animated Leaflet markers, and dual views (grid atlas vs. stream) keep the UX polished and informative.
- **Real-time Filtering & Insights** – Filter by time range, magnitude, region, or keyword. Stats widgets and “Signal Spotlights” summarize totals, peaks, depths, and notable events.
- **Leaflet Map + News Modal** – Click any event to focus the map, view detailed metadata, and (with a NewsAPI key) fetch related headlines right inside the modal.
- **Admin Ops Suite** – Manage earthquake sources, define reusable source types (USGS, EMSC, …), and configure NewsAPI keys from the Breeze dashboard.
- **Near-Me Mode** – Leverage browser geolocation to prioritize the nearest quakes for situational awareness.

## 🛠 Stack Overview

| Layer    | Details                                  |
|----------|-------------------------------------------|
| Backend  | Laravel 12, PHP 8.2+, Eloquent, HTTP client |
| Frontend | Blade templates, vanilla JS, Tailwind CSS  |
| Mapping  | Leaflet 1.9 + CARTO basemaps               |
| Auth/UI  | Laravel Breeze (Blade stack)               |
| DB       | SQLite by default (MySQL/Postgres ready)   |

## 🚀 Getting Started

```bash
git clone <repository-url>
cd Earthquake
composer install
npm install
cp .env.example .env
php artisan key:generate
```

- Configure database connection in `.env` (SQLite works out of the box).
- Migrate + seed (creates default admin user, sources, and types):
  ```bash
  php artisan migrate --seed
  ```
- Run the dev stack:
  ```bash
  npm run dev   # or npm run build for production
  php artisan serve
  ```
  Visit `http://127.0.0.1:8000`.

## 🔧 Admin Tasks

1. Log in (default user: `admin@nafiur.com` / `password`).
2. Use `/dashboard` to add/edit data sources and source types.
3. Configure NewsAPI integration at `/admin/settings`.
4. Test the home console to ensure live feed + news are working.

## 🧭 Feature Breakdown

### Home Console (`/`)
- Leaflet map with theme-aware tiles and magnitude-scaled markers.
- Floating control panel with filtering, search, auto refresh, and Near Me.
- Live stats + insights cards, infinite-scroll event list, and a news-enabled detail modal.

### Admin Portal
- **Sources**: CRUD for providers, activate one at a time, toggle near-real-time feed.
- **Source Types**: Manage reusable adapters (USGS, EMSC, etc.).
- **Settings**: Store NewsAPI key (served to the frontend via `/api/settings/news-api-key`).

## 📁 Key Files

| File/Dir                                           | Purpose                                   |
|----------------------------------------------------|-------------------------------------------|
| `app/Http/Controllers/EarthquakeController.php`    | Renders console + proxies quake data       |
| `resources/views/earthquake/index.blade.php`       | Main console UI + JS                      |
| `app/Http/Controllers/Admin/SourceController.php`  | Source management                         |
| `app/Http/Controllers/Admin/SourceTypeController.php` | Source type CRUD                       |
| `app/Http/Controllers/Admin/SettingController.php` | Stores NewsAPI key, exposes API endpoint  |
| `database/seeders/*Seeder.php`                     | Seeds default user, source types, sources |

## ⚠️ Notes

- The NewsAPI key endpoint is public for convenience—lock it down if deploying publicly.
- Leaflet requires `map.invalidateSize()` after layout changes; the template already handles this on init.
- Customize map tiles/themes or extend source types to add new providers quickly.

Stay informed and stay safe. 🌍