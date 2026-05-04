![preview](https://github.com/user-attachments/assets/2704bbc3-5d4f-4a65-b861-ab24f648d517)

# ⚡ Laravel Nuxt Boilerplate

[![](https://img.shields.io/badge/Laravel-v13-ff2e21.svg)](https://laravel.com)
[![](https://img.shields.io/badge/nuxt.js-v4-04C690.svg)](https://nuxt.com)
[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Fk2so-dev%2Flaravel-nuxt.svg?type=shield)](https://app.fossa.com/projects/git%2Bgithub.com%2Fk2so-dev%2Flaravel-nuxt?ref=badge_shield)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/k2so-dev/laravel-nuxt/laravel.yml)](https://github.com/k2so-dev/laravel-nuxt/actions/workflows/laravel.yml)
[![CodeQL](https://github.com/k2so-dev/laravel-nuxt/actions/workflows/github-code-scanning/codeql/badge.svg)](https://github.com/k2so-dev/laravel-nuxt/actions/workflows/github-code-scanning/codeql)

The goal of the project is to create a template for development on Laravel and Nuxt with maximum API performance, ready-made authorization methods, image uploading with optimization and ready-made user roles.

<!-- TOC -->

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
    - [Standalone](#standalone)
    - [Docker](#docker)
- [Usage](#usage)
    - [Fetch wrapper](#fetch-wrapper)
    - [Authentication](#authentication)
    - [Nuxt Middleware](#nuxt-middleware)
    - [Laravel Middleware](#laravel-middleware)
- [Examples](#examples)
    - [Route list](#route-list)
    - [Demo](#demo)
- [Links](#links)
- [License](#license)

<!-- /TOC -->

## Features

 - [**Laravel 13**](https://laravel.com/docs/13.x) and [**Nuxt 4**](https://nuxt.com/)
 - [**Laravel Octane**](https://laravel.com/docs/13.x/octane) supercharges your application's performance by serving your application using high-powered application servers.
 - [**Laravel Socialite**](https://laravel.com/docs/13.x/socialite) OAuth providers
 - [**Laravel Sail**](https://laravel.com/docs/13.x/sail) Light-weight command-line interface for interacting with Laravel's default Docker development environment.
 - [**Spatie Laravel Permissions**](https://spatie.be/docs/laravel-permission/v6/introduction) This package allows you to manage user permissions and roles in a database.
 - UI library [**Nuxt UI 4**](https://ui.nuxt.com/) based on [**TailwindCSS 4**](https://tailwindcss.com/) and [**Reka UI**](https://reka-ui.com/).
 - [**Pinia**](https://pinia.vuejs.org/ssr/nuxt.html) The intuitive store for Vue.js
 - Integrated pages: login, registration, password recovery, email confirmation, account information update, password change.
 - Temporary uploads with cropping and optimization of images.
 - Device management
 - Enhanced Fetch Wrappers : Utilizes `$http` and `useHttp`, which extend the capabilities of **Nuxt's** standard `$fetch` and `useFetch`.
 - Monorepo layout (`apps/api` + `apps/web`) with `just` task runner.

## Requirements

 - PHP 8.4+ / Node 20+ (or [**Bun**](https://bun.com))
 - **Redis** is required for the [**Throttling with Redis**](https://laravel.com/docs/13.x/routing#throttling-with-redis) feature
 - For Docker setup: [**Docker**](https://github.com/docker/docker-install) and [**just**](https://github.com/casey/just) task runner

## Installation
### Standalone

<details>
<summary>Show standalone instructions</summary>

1. `cd apps/api && composer install && cd ../web && bun install && cd ../..`
2. `cp apps/api/.env.example apps/api/.env`
3. `cd apps/api && php artisan key:generate && php artisan storage:link`
4. `php artisan migrate && php artisan db:seed`
5. `php artisan octane:install --server=swoole && php artisan octane:start --host=127.0.0.1 --port=8000`
6. In another terminal: `cd apps/web && bun run dev`

</details>

### Docker

Single `docker-compose.yml`: API runs on [**Laravel Sail**](https://laravel.com/docs/13.x/sail) with [**Octane**](https://laravel.com/docs/13.x/octane) in watch mode, web runs on `oven/bun:1`, plus a `redis:8-alpine` service for cache / queue / session / throttling. Orchestrated via `just`. `just sail ...` wraps the upstream `vendor/bin/sail` for those who want it.

#### Installing `just`

```bash
brew install just                          # macOS
apt install just                           # Debian / Ubuntu
winget install --id Casey.Just --exact     # Windows
```

Other systems: see the [full list of packages](https://github.com/casey/just/tree/master#packages).

#### Lifecycle

```bash
just up -d                 # start full app (api + web) — :8000 / :3000
just prod -d               # production mode (Octane no-watch, web .output/)
just api -d                # api only — :8000
just web                   # web foreground, ephemeral — :3000
just stop                  # pause containers
just down                  # remove containers
```

Quick start:

```bash
just init                  # copies .env files, composer install, builds api image, bun install, key:generate, storage:link
just a migrate --seed
just up -d
```

Common commands:

```bash
just                       # show all recipes
just build                 # rebuild the api image
just a migrate             # `php artisan migrate` in ephemeral container
just sail ...              # invoke Laravel Sail directly (e.g. `just sail tinker`)
just composer require ...
just bun add @vueuse/core
just pint                  # PHP linter
just test                  # PHPUnit
just down -v               # stop and remove volumes
```

## Usage

### Fetch wrapper

To integrate with the API, enhanced `$http` and `useHttp` wrappers are used, expanding the functionality of Nuxt's standard `$fetch` and `useFetch`. The `$http` wrapper includes custom interceptors to replace the originals:
- `onFetch` instead of `onRequest`
- `onFetchError` instead of `onRequestError`
- `onFetchResponse` instead of `onResponse`
- `onFetchResponseError` instead of `onResponseError`

Additionally, `$http` predefines a base url, authorization headers, and proxy IP for convenient API work in SSR mode.
For example, the code for authorizing a user by email and password:
```vue
<script lang="ts" setup>
const router = useRouter();
const auth = useAuthStore();
const form = templateRef("form");
const state = reactive({
  email: "",
  password: "",
  remember: false,
});

const { refresh: onSubmit, status } = useHttp("login", {
  method: "POST",
  body: state,
  immediate: false,
  watch: false,
  async onFetchResponse({ response }) {
    if (response?.status === 422) {
      form.value.setErrors(response._data?.errors);
    } else if (response._data?.ok) {
      await auth.login();
      await router.push("/");
    }
  }
});

const loading = computed(() => status.value === "pending");
</script>
<template>
  <UForm ref="form" :state="state" @submit="onSubmit" class="space-y-4">
    <UFormField label="Email" name="email" required>
      <UInput
        v-model="state.email"
        placeholder="you@example.com"
        icon="i-heroicons-envelope"
        trailing
        type="email"
        autofocus
      />
    </UFormField>

    <UFormField label="Password" name="password" required>
      <UInput v-model="state.password" class="w-full" type="password" />
    </UFormField>

    <UTooltip :delay-duration="0" text="for 1 month" :content="{ side: 'right' }">
      <UCheckbox v-model="state.remember" class="w-full" label="Remember me" />
    </UTooltip>

    <div class="flex items-center justify-end space-x-4">
      <NuxtLink class="text-sm" to="/auth/forgot">Forgot your password?</NuxtLink>
      <UButton type="submit" label="Login" :loading="loading" />
    </div>
  </UForm>
</template>
```
> In this example, a POST request will be made to the url **"/api/v1/login"**

### Authentication
**useAuthStore()** has everything you need to work with authorization.

Data returned by **useAuthStore**:
* `logged`: Boolean, whether the user is authorized
* `user`: User object, user stored in pinia store
* `fetchCsrf`: Function, fetch csrf token
* `fetchUser`: Function, fetch user data
* `login`: Function, login user
* `logout`: Function, remove local data and call API to remove session
* `hasRole`: Function, checks the role

### Nuxt Middleware

The following middleware is supported:
* `guest`: unauthorized users
* `auth`: authorized users
* `verified`: users who have confirmed their email
* `role-user`: users with the 'user' role
* `role-admin`: users with the 'admin' role

### Laravel Middleware

All built-in middleware from Laravel + middleware based on roles [**Spatie Laravel Permissions Middleware**](https://spatie.be/docs/laravel-permission/v6/basic-usage/middleware)

## Examples

### Route list

![routes](https://github.com/k2so-dev/laravel-nuxt/assets/15279423/39bb3021-a4d1-4472-8320-5a397809904d)

### Demo

https://github.com/k2so-dev/laravel-nuxt/assets/15279423/9b134491-1444-4323-a7a3-d87833dcdc67

## Links
* [Nuxt 4](https://nuxt.com/)
* [Nuxt UI 4](https://ui.nuxt.com/)
* [Tailwind CSS 4](https://tailwindcss.com/)
* [Laravel 13x](https://laravel.com/docs/13.x)

## License
[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Fk2so-dev%2Flaravel-nuxt.svg?type=large)](https://app.fossa.com/projects/git%2Bgithub.com%2Fk2so-dev%2Flaravel-nuxt?ref=badge_large)
