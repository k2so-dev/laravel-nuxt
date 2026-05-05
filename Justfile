set dotenv-load := true
set positional-arguments := true

compose := "docker compose --env-file ./apps/api/.env"

[private]
default:
	@just --list --unsorted --list-heading $'\nRECIPES:\n' --list-prefix '  '

# Copy .env files, build api image, install deps, generate APP_KEY, link storage
[group('setup')]
init:
	#!/usr/bin/env bash
	if [ -f .env ] && [ -f apps/api/.env ] && [ -f apps/web/.env ]; then
		echo "Project already initialized (.env files exist). Skipping."
		exit 0
	fi
	cp -n .env.example .env || true
	cp -n apps/api/.env.example apps/api/.env || true
	cp -n apps/web/.env.example apps/web/.env || true
	just composer install --ignore-platform-reqs
	just build
	{{compose}} run --rm --no-deps laravelnuxt.api bun install
	just bun install
	just a key:generate
	just a storage:link --relative

# Show container status (running / stopped / missing)
[group('setup')]
status:
	#!/usr/bin/env bash
	echo "STATUS:"
	running=$({{compose}} ps --status running --services 2>/dev/null || true)
	existing=$({{compose}} ps -a --services 2>/dev/null || true)
	for s in laravelnuxt.api laravelnuxt.web; do
		if echo "$running" | grep -qx "$s" 2>/dev/null; then
			printf "  \033[32m●\033[0m %-6s running\n" "$s"
		elif echo "$existing" | grep -qx "$s" 2>/dev/null; then
			printf "  \033[33m○\033[0m %-6s stopped\n" "$s"
		else
			printf "  \033[90m✗\033[0m %-6s missing\n" "$s"
		fi
	done

# Start full app: api + web — no implicit build
[group('stack')]
up *args:
	{{compose}} up "$@"

# Start full app in production mode (Octane no-watch, APP_ENV=production, web .output)
[group('stack')]
prod *args:
	APP_ENV=production \
	APP_DEBUG=false \
	SUPERVISOR_PHP_COMMAND="php artisan octane:start" \
	WEB_COMMAND="bun run .output/server/index.mjs" \
	{{compose}} up "$@"

# Stop containers without removing
[group('stack')]
stop *args:
	{{compose}} stop "$@"

# Stop and remove containers (-v also removes volumes)
[group('stack')]
down *args:
	{{compose}} down "$@"

# Restart running services
[group('stack')]
restart *args:
	{{compose}} restart "$@"

# Tail and follow service logs
[group('stack')]
logs *args:
	{{compose}} logs -f "$@"

# Show raw `docker compose ps`
[group('stack')]
ps *args:
	{{compose}} ps "$@"

# Start api only -d
[group('profile')]
api *args:
	{{compose}} up -d laravelnuxt.api "$@"

# Run web foreground, ephemeral, with port forwarding
[group('profile')]
web *args:
	{{compose}} run --rm --service-ports laravelnuxt.web "$@"

# Start redis only -d
[group('profile')]
redis *args:
	{{compose}} up -d laravelnuxt.redis "$@"

# Stop redis
[group('profile')]
redis-stop *args:
	{{compose}} stop laravelnuxt.redis "$@"

# Build api Docker image
[group('build')]
build:
	{{compose}} build laravelnuxt.api

# Alias for `just artisan`
[group('laravel')]
a *args: (artisan args)

# Run any `php artisan` command
[group('laravel')]
artisan *args:
	{{compose}} run --rm --no-deps laravelnuxt.api php artisan "$@"

# Run raw `php` in api container
[group('laravel')]
php *args:
	{{compose}} run --rm --no-deps laravelnuxt.api php "$@"

# Run composer (uses composer:latest sidecar — works without api image build)
[group('laravel')]
composer *args:
	{{compose}} --profile cli run --rm composer "$@"

# Run vendored Sail (apps/api/vendor/bin/sail) with monorepo-aware service name
[group('laravel')]
[no-exit-message]
sail *args:
	APP_SERVICE=laravelnuxt.api ./apps/api/vendor/bin/sail "$@"

# Run Laravel Pint (code style)
[group('laravel')]
pint *args:
	{{compose}} run --rm --no-deps laravelnuxt.api ./vendor/bin/pint "$@"

# Run PHPUnit
[group('laravel')]
test *args:
	{{compose}} run --rm --no-deps laravelnuxt.api ./vendor/bin/phpunit "$@"

# Run any bun command
[group('nuxt')]
bun *args:
	{{compose}} run --rm --no-deps laravelnuxt.web bun "$@"

# Run any bunx command
[group('nuxt')]
bunx *args:
	{{compose}} run --rm --no-deps laravelnuxt.web bunx "$@"

# Run nuxt CLI (alias for `bunx nuxt`)
[group('nuxt')]
nuxt *args:
	{{compose}} run --rm --no-deps laravelnuxt.web bunx nuxt "$@"

# Build Nuxt for production (writes apps/web/.output/)
[group('nuxt')]
nuxt-build:
	just bun install --frozen-lockfile
	just bun run build
