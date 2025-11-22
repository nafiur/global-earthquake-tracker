# Global Earhquake Tracker - Laravel Edition

This project is a modernization of a static HTML/JS earthquake tracker, migrated to a robust **Laravel 12** application. It leverages **Laravel Breeze** for scaffolding, **Tailwind CSS** for a premium UI, and features a dynamic **Admin Dashboard** to manage data sources.

## 🚀 Transformation Overview

The original project was a single `index.html` file. It has been transformed into a full-stack Laravel application with:

*   **Framework**: Migrated to **Laravel 12**.
*   **Dynamic Backend**: Data fetching is now proxied through Laravel, allowing for multiple API sources.
*   **Admin Control**: A dashboard to toggle between different earthquake data providers (e.g., USGS, EMSC).
*   **Styling**: Replaced custom CSS with **Tailwind CSS** utility classes.
*   **Scaffolding**: Integrated **Laravel Breeze** (Blade stack).

## 🛠️ Tech Stack

*   **Backend**: Laravel 12 (PHP)
*   **Frontend**: Blade Templates, JavaScript (Vanilla)
*   **Styling**: Tailwind CSS
*   **Database**: SQLite / MySQL (for managing data sources)
*   **Data Sources**:
    *   [USGS Earthquake Hazards Program](https://earthquake.usgs.gov/)
    *   [EMSC (European-Mediterranean Seismological Centre)](https://www.seismicportal.eu/)

## ✨ Features

*   **🔌 Multi-Source Support**: Switch between USGS and EMSC APIs dynamically from the admin panel.
*   **🛡️ Admin Dashboard**: Manage active data sources securely.
*   **🌍 Real-time Data**: Fetches live earthquake data based on the active source.
*   **🔍 Interactive Filtering**:
    *   **Time Range**: Past Hour, Day, Week, Month.
    *   **Magnitude**: Filter by minimum magnitude (1.0+ to 6.0+).
    *   **Region**: Filter by specific global regions.
*   **📍 Geolocation**: "Near Me" feature calculates distance to earthquakes from your current location.
*   **📊 Visual Statistics**: Dashboard showing total count, highest magnitude, and averages.
*   **📱 Responsive Design**: Fully optimized for all devices.

## 📦 Installation & Setup

1.  **Clone the repository**
    ```bash
    git clone <repository-url>
    cd earth-quak
    ```

2.  **Install Dependencies**
    ```bash
    composer install
    npm install
    ```

3.  **Environment Setup**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    *Ensure your database connection in `.env` is configured (default is SQLite).*

4.  **Database Setup**
    Run migrations and seed the default data sources (USGS & EMSC):
    ```bash
    php artisan migrate --seed
    ```

5.  **Build Assets**
    ```bash
    npm run build
    ```

6.  **Run the Application**
    ```bash
    php artisan serve
    ```
    Visit `http://localhost:8000` in your browser.

## 🎮 Usage

### Switching Data Sources
1.  **Log in** to the application (register a new account if needed).
2.  Navigate to the **Dashboard** (`/dashboard`).
3.  You will see a list of available data sources (USGS, EMSC).
4.  Click **Activate** on the source you wish to use.
5.  Return to the **Home** page to see data from the selected provider.

## 📂 Project Structure

*   `app/Http/Controllers/EarthquakeController.php`: Handles the main view and data proxying logic.
*   `app/Http/Controllers/Admin/SourceController.php`: Manages source toggling in the dashboard.
*   `app/Models/EarthquakeSource.php`: Model for the data sources table.
*   `resources/views/earthquake/index.blade.php`: The main frontend view.
*   `database/seeders/EarthquakeSourceSeeder.php`: Seeds the initial API sources.

---
*Built with ❤️ by Nafiur Rahman*
# global-earthquake-tracker
