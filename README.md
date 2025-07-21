![preview](https://github.com/user-attachments/assets/08c7f1f5-9828-4a33-986a-7c70444b0ac6)

# Laravel Nuxt Boilerplate

[![](https://img.shields.io/badge/Laravel-v12-ff2e21.svg)](https://laravel.com)
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
    - [Docker Deploy (Laravel Sail)](#docker-deploy-laravel-sail)
    - [Auth Guard Switch](#auth-guard-switch)
- [Upgrade](#upgrade)
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

 - [**Laravel 12**](https://laravel.com/docs/12.x) and [**Nuxt 4**](https://nuxt.com/)
 - [**Laravel Octane**](https://laravel.com/docs/12.x/octane) supercharges your application's performance by serving your application using high-powered application servers.
 - [**Laravel Telescope**](https://laravel.com/docs/12.x/telescope) provides insight into the requests coming into your application, exceptions, log entries, database queries, queued jobs, mail, notifications, cache operations, scheduled tasks, variable dumps, and more.
 - [**Laravel Sanctum**](https://laravel.com/docs/12.x/sanctum) Token/Session-based authorization is compatible with **SSR** and **CSR**
 - [**Laravel Socialite**](https://laravel.com/docs/12.x/socialite) OAuth providers
 - [**Laravel Sail**](https://laravel.com/docs/12.x/sail) Light-weight command-line interface for interacting with Laravel's default Docker development environment.
 - [**Spatie Laravel Permissions**](https://spatie.be/docs/laravel-permission/v6/introduction) This package allows you to manage user permissions and roles in a database.
 - UI library [**Nuxt UI 3**](https://ui.nuxt.com/) based on [**TailwindCSS 4**](https://tailwindcss.com/) and [**Reka UI**](https://reka-ui.com/).
 - [**Pinia**](https://pinia.vuejs.org/ssr/nuxt.html) The intuitive store for Vue.js
 - Integrated pages: login, registration, password recovery, email confirmation, account information update, password change.
 - Temporary uploads with cropping and optimization of images.
 - Device management
 - Enhanced Fetch Wrappers : Utilizes `$http` and `useHttp`, which extend the capabilities of **Nuxt's** standard `$fetch` and `useFetch`.

## Requirements

 - PHP 8.3+ / Node 20+
 - **Redis** is required for the [**Throttling with Redis**](https://laravel.com/docs/12.x/routing#throttling-with-redis) feature
 - [**Laravel Octane**](https://laravel.com/docs/12.x/octane) supports 3 operating modes: Swoole (php extension), Roadrunner and FrankenPHP

## Installation
### Standalone
1. `composer install && bun install`
2. `cp .env.example .env && php artisan key:generate && php artisan storage:link`
3. `php artisan migrate && php artisan db:seed`
4. `php artisan octane:install`
5. `php artisan octane:start --watch --port=8000 --host=127.0.0.1`
6. `bun dev`

### Docker Deploy (Laravel Sail)
[Laravel Sail](https://laravel.com/docs/12.x/sail) is a light-weight command-line interface for interacting with Laravel's default Docker development environment. Sail provides a great starting point for building a Laravel application using PHP, MySQL, and Redis without requiring prior Docker experience.

At its heart, Sail is the `docker-compose.yml` file and the `sail` script that is stored at the root of your project. The sail script provides a CLI with convenient methods for interacting with the Docker containers defined by the docker-compose.yml file.

Laravel Sail is supported on macOS, Linux, and Windows (via [WSL2](https://docs.microsoft.com/en-us/windows/wsl/about)).
1. Installing Composer Dependencies
```shell
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```
2. Configuring A Shell Alias (Optional)
```shell
alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'
```
To make sure this is always available, you may add this to your shell configuration file in your home directory, such as ~/.zshrc or ~/.bashrc, and then restart your shell.

3. `sail up`
4. `sail bun install`
5. `sail bun dev`

> Read the full [Laravel Sail](https://laravel.com/docs/12.x/sail) documentation to get the best user experience

### Auth Guard Switch

You can switch the authentication guard between **Token** and **Session** using the following command:

```shell
php artisan auth:switch
```

## Upgrade

Standalone:
```shell
npx nuxi upgrade
composer update
```

Sail:
```shell
sail npx nuxi upgrade
sail composer update
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
      await auth.login(response._data.token ?? null);
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
* `login`: Function, login user by token/session
* `logout`: Function, remove local data and call API to remove token/session
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
* [Nuxt UI 3](https://ui.nuxt.com/)
* [Tailwind CSS 4](https://tailwindcss.com/)
* [Laravel 12x](https://laravel.com/docs/12.x)

## License
[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Fk2so-dev%2Flaravel-nuxt.svg?type=large)](https://app.fossa.com/projects/git%2Bgithub.com%2Fk2so-dev%2Flaravel-nuxt?ref=badge_large)
