# Budget App

A modern, responsive Budget Application built with Laravel 11, Vue 3, Inertia.js v3, and Tailwind CSS v4.

🌍 **Live Demo:** [https://www.budget-app.site/](https://www.budget-app.site/)

## Tech Stack

- **Backend:** Laravel (PHP 8.3+)
- **Frontend:** Vue 3 + Inertia.js
- **Styling:** Tailwind CSS v4
- **Routing:** Laravel Wayfinder
- **Testing:** Pest PHP

## Prerequisites

Before running the application, make sure you have the following installed:

- PHP >= 8.3
- Composer
- Node.js & npm (v20+ recommended)
- SQLite (or another supported database)

## Getting Started

### 1. Clone the repository

```bash
git clone <repository-url>
cd budget
```

### 2. Setup the application

We've provided a convenient Composer script that handles all the initial setup (installing dependencies, copying the `.env` file, generating the app key, running migrations, and building the frontend assets).

Run the following command:

```bash
composer setup
```

*Alternatively, if you prefer to run the setup manually:*

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Setup environment variables
cp .env.example .env

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Build frontend assets
npm run build
```

### 3. Run the development server

To start the local development server, run:

```bash
composer dev
```

This command uses `concurrently` to run the Laravel development server, the queue listener, and the Vite development server simultaneously.

The application should now be accessible at `http://localhost:8000`.

## Testing

The application uses [Pest](https://pestphp.com/) for testing.

```bash
# Run tests using the composer script
composer test

# Check code formatting (Pint)
composer lint:check
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
