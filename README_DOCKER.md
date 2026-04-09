## Docker (Ubuntu) Deployment

### Prerequisites

-   Docker + Docker Compose v2
-   DNS/Reverse proxy pointing `pmc-exam.cs.psu.ac.th` to your server

### 1) Create `.env.docker`

Copy and fill values:

```bash
cp .env.docker.example .env.docker
```

Set `APP_KEY` (generate once):

```bash
docker compose run --rm app php artisan key:generate --show
```

Paste the output into `.env.docker` as `APP_KEY=...`.

### 2) Build and start

```bash
docker compose up -d --build
```

### 3) Run migrations

```bash
docker compose exec app php artisan migrate --force
docker compose run --rm app php artisan migrate --force
```

### 4) Verify

-   Web is published on port `8080` (change in `docker-compose.yml` if you want 80/443)
-   Queue worker is running in service `queue`
-   Scheduler is running in service `scheduler`

### Notes

-   **Queue is required** for `admin/arrange_seat` background processing.
-   MySQL data persists in Docker volume `db_data`.
