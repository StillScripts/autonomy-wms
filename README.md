# Autonomy Server

## Overview

Autonomy Server is a modern Laravel application using React (via Inertia.js) for the frontend. It is designed for robust, scalable deployments and uses **Serverless Postgres (Neon)** on Laravel Cloud in production.

---

## Local Development Setup (Mac)

### 1. Install Postgres

- **Homebrew (recommended):**
    ```sh
    brew install postgresql
    brew services start postgresql
    ```
- **Or Docker:**
    ```sh
    docker run --name autonomy-postgres -e POSTGRES_PASSWORD=secret -p 5432:5432 -d postgres:15
    ```
- **Or [Postgres.app](https://postgresapp.com/)**

### 2. Create the PostgreSQL Role (Homebrew/Postgres.app users)

If you installed Postgres via Homebrew or Postgres.app, you may need to create the `postgres` role:

```sh
psql postgres -c "CREATE ROLE postgres WITH LOGIN PASSWORD 'secret' SUPERUSER CREATEDB CREATEROLE;"
```

**Alternative:** Use your system username instead by updating `.env`:
```sh
DB_USERNAME=your_username  # e.g., danielstill
DB_PASSWORD=               # usually blank for local Homebrew installs
```

### 3. Create the Database

```sh
createdb autonomy_server
```

Or, if using Docker:

```sh
docker exec -it autonomy-postgres psql -U postgres -c "CREATE DATABASE autonomy_server;"
```

### 4. Install Dependencies

```sh
composer install
npm install
```

### 5. Configure `.env`

```sh
cp .env.example .env
php artisan key:generate
```

Update the database settings in `.env`:

```sh
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=autonomy_server
DB_USERNAME=postgres
DB_PASSWORD=secret
```



### 6. Run Migrations and Seeders

```sh
php artisan migrate:fresh --seed
```

### 7. Run the Dev Environment

```sh 
composer run dev
```

### 8. Login as Test User

Email: `test@example.com`
Password: `password123`

### 9. Testing

- Create a `.env.testing` file with the same Postgres settings to ensure tests run against Postgres, not SQLite.

---

## Documentation

- See [`docs/README.md`](docs/README.md) for:
    - Payment system
    - Authentication
    - API and technical guides

---

## Troubleshooting

### "role 'postgres' does not exist"

If you see `FATAL: role "postgres" does not exist`, this means your PostgreSQL installation doesn't have a `postgres` user. This commonly happens with Homebrew and Postgres.app installations.

**Fix Option 1 - Create the postgres role:**
```sh
psql postgres -c "CREATE ROLE postgres WITH LOGIN PASSWORD 'secret' SUPERUSER CREATEDB CREATEROLE;"
```

**Fix Option 2 - Use your system username:**
Check what users exist:
```sh
psql postgres -c "\du"
```
Then update `.env` to use an existing user (usually your system username):
```sh
DB_USERNAME=your_username
DB_PASSWORD=
```

### "Application in Production" Warning

Run:
```sh
cp .env.example .env
php artisan key:generate
```

Then update database credentials in `.env`.

### Other Issues

- If you see connection errors, ensure Postgres is running and credentials match your `.env`.

---

## Quick Start

```sh
git clone <repo-url>
cd autonomy-server
createdb autonomy_server
cp .env.example .env
composer install
npm install
php artisan key:generate
php artisan migrate:fresh --seed
composer run dev
```

Update `.env` with Postgres credentials (see step 4 above).

---

## References

- [Laravel Cloud Databases](https://cloud.laravel.com/docs/resources/databases)
- [Neon: Testing Laravel Applications with Database Branching](https://neon.tech/guides/laravel-test-on-branch)
