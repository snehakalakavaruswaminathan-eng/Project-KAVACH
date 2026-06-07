# Kavach Workstream B — Test Environment

## Prerequisites
- Docker Desktop installed and running
- macOS 12+ (or any OS with Docker)

## Quick Start (under 15 minutes)

### Additional tools

brew install curl
brew install httpie        # friendlier curl alternative

```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
git clone <your-repo-url>
mkdir -p ~/kavach-workstream-b
cd kavach-workstream-b
docker compose up -d
```

## Create the docker-compose.yml file:

### yaml

```
version: "3.8"

services:

  # ── DVWA (Damn Vulnerable Web Application) ──────────────────────────────
  dvwa:
    image: vulnerables/web-dvwa
    container_name: kavach-dvwa
    ports:
      - "8080:80"       # Access at http://localhost:8080
    environment:
      - MYSQL_PASS=p@ssw0rd
    restart: unless-stopped
    networks:
      - kavach-net

  # ── OWASP Juice Shop ────────────────────────────────────────────────────
  juiceshop:
    image: bkimminich/juice-shop
    container_name: kavach-juiceshop
    ports:
      - "3000:3000"     # Access at http://localhost:3000
    restart: unless-stopped
    networks:
      - kavach-net

networks:
  kavach-net:
    driver: bridge

```



| App          | URL                        | Credentials        |
|--------------|----------------------------|--------------------|
| DVWA         | http://localhost:8080      | admin / password   |
| Juice Shop   | http://localhost:3000      | (self-register)    |

### DVWA Initial Setup
1. Visit http://localhost:8080/setup.php
2. Click "Create / Reset Database"

   
## Stopping the Environment
```bash
docker compose down
```
