# Minimal Docker Upgrade Guide

Your current machine is running:
- macOS 10.15.7
- Docker 17.12.0-ce
- docker-compose 1.18.0

That stack is too old for the current project setup and is also hitting image pull signature issues.

## Goal
Get to a Docker setup that supports:
- Docker Compose v2 or a newer `docker-compose`
- Pulling modern images without `missing signature key`
- Running the local PHP 8.2 + PostgreSQL environment

## Minimal path
1. Remove the old Docker installation if it is an old Docker Toolbox / legacy CE setup.
2. Install a newer Docker Desktop release that is still compatible with macOS 10.15.7.
3. Start Docker Desktop and wait until the daemon is fully running.
4. Verify the new commands:
   - `docker --version`
   - `docker compose version`
   - `docker-compose --version`
5. Re-run the local stack:
   - `docker compose up --build`
   - or `docker-compose up --build` if your installation only provides the legacy command

## If Docker Desktop refuses to install
macOS 10.15.7 may be the limiting factor. In that case, the truly minimal fix is one of these:
- upgrade macOS to a version supported by current Docker Desktop
- use another machine with a newer macOS/Linux
- use a remote development environment

## Clean up old Docker bits
Before retrying, make sure only one Docker installation is active:
- Quit Docker if it is running
- Remove old Docker Machine / Toolbox binaries if you used them before
- Reopen Docker Desktop after installation

## What success looks like
You should be able to run:
```bash
docker compose up --build
```
and the app container should start without:
- `missing signature key`
- `Version in "./docker-compose.yml" is unsupported`
- `unknown flag: --build`

## If you still see pull/signature errors
Then the Docker client is still too old for the registry/images being used. At that point, upgrading the OS or moving to a newer machine is the quickest path.
