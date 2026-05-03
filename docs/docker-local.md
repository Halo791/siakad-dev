# Local Docker setup

This repo now includes a local PHP 8.2 + PostgreSQL setup.

## Files
- `docker-compose.yml`
- `docker/php/Dockerfile`
- `docker/php/start-app.sh`
- `.env.local`
- `docs/upgrade-docker.md`

## Run
1. Start the stack:
   - `docker-compose up --build`
2. Open the app:
   - `http://localhost:8000`

## Notes
- `.env.local` overrides only the local container environment.
- Your main `.env` file stays untouched.
- The app container uses PostgreSQL at the `postgres` service name.
- The stack uses `postgres:13-alpine` for better compatibility with older Docker clients.
- If you want to test against Neon instead, edit `.env.local` to point `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, and `DB_SSLMODE` to the Neon values, then stop the local `postgres` service.
- If your Docker client is too old, follow `docs/upgrade-docker.md` first.
