<p align="center">
  <img src="https://github.com/k2so-dev/laravel-nuxt/assets/15279423/1f030310-3bc4-4ae1-8cfc-a424bbec818a" width="100%" />
</p>

# Laravel Nuxt Boilerplate

[![](https://img.shields.io/badge/Laravel-v10.33-ff2e21.svg)](https://laravel.com)
[![](https://img.shields.io/badge/nuxt.js-v3.8-04C690.svg)](https://nuxt.com)
[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Fk2so-dev%2Flaravel-nuxt.svg?type=shield)](https://app.fossa.com/projects/git%2Bgithub.com%2Fk2so-dev%2Flaravel-nuxt?ref=badge_shield)

The goal of the project is to create a template for development on Laravel and Nuxt with maximum API performance, ready-made authorization methods, image uploading with optimization and ready-made user roles.

<!-- TOC -->

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
    - [Nuxt $fetch](#nuxt-fetch)
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

 - [**Laravel 10**](https://laravel.com/docs/10.x) and [**Nuxt 3**](https://nuxt.com/)
 - [**Laravel Octane**](https://laravel.com/docs/10.x/octane) is a library for fast backend work.
 - [**Laravel Telescope**](https://laravel.com/docs/10.x/telescope) provides insight into the requests coming into your application, exceptions, log entries, database queries, queued jobs, mail, notifications, cache operations, scheduled tasks, variable dumps, and more.
 - [**Laravel Sanctum**](https://laravel.com/docs/10.x/sanctum) Token-based authorization is compatible with **SSR** and **CSR**
 - [**Spatie Laravel Permissions**](https://spatie.be/docs/laravel-permission/v6/introduction) This package allows you to manage user permissions and roles in a database.
 - UI library [**Nuxt UI**](https://ui.nuxt.com/) based on [**TailwindCSS**](https://tailwindui.com/) and [**HeadlessUI**](https://headlessui.com/).
 - [**Pinia**](https://pinia.vuejs.org/ssr/nuxt.html) The intuitive store for Vue.js
 - Integrated pages: login, registration, password recovery, email confirmation, account information update, password change.
 - Temporary uploads with cropping and optimization of images.
 - [**ofetch**](https://github.com/unjs/ofetch) preset for working with Laravel API, which makes it possible
use $**fetch** without having to resort to custom $**fetch** wrappers.

## Requirements

 - PHP 8.2 / Node 20+
 - **Redis** is required for the [**Throttling with Redis**](https://laravel.com/docs/10.x/routing#throttling-with-redis) feature
 - [**Laravel Octane**](https://laravel.com/docs/10.x/octane) supports 2 operating modes: Swoole (php extension) or Roadrunner

## Installation
1. clone repository
2. `composer install`
3. `cp .env.example .env && php artisan key:generate && php artisan storage:link`
4. `php artisan migrate`
5. `php artisan db:seed`
6. `php artisan octane:install`
7. `php artisan octane:start --watch --port=8000 --host=127.0.0.1`
8. `yarn install`
9. `yarn dev`

> Nuxt port is set in package.json scripts via **cross-env**

## Usage

### Nuxt $fetch

To work with the api, the default path is **"/api/v1"**. All requests from **Nuxt** to the **Laravel API** can be executed without wrappers, as described in the **Nuxt.js** documentation. For example, the code for authorizing a user by email and password:
```ts
const auth = useAuthStore();

const state = reactive({
  email: "",
  password: "",
});

const { data } = await useFetch("login", {
  method: "POST",
  body: state,
  watch: false,
});

if (data.value?.ok) {
  auth.token = data.value.token;
  await auth.fetchUser();
  await router.push("/");
}
```
> In this example, a POST request will be made to the url **"/api/v1/login"**

### Authentication
**useAuthStore()** has everything you need to work with authorization.

Data returned by **useAuthStore**:
* `logged`: Boolean, whether the user is authorized
* `token`: Cookie, sanctum token
* `user`: User object, user stored in pinia store
* `logout`: Function, remove local data and call API to remove token
* `fetchUser`: Function, fetch user data

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

<p align="center">
  <img src="https://raw.githubusercontent.com/k2so-dev/laravel-nuxt/main/art/routes.png" width="100%" />
</p>

### Demo

https://github.com/k2so-dev/laravel-nuxt/assets/15279423/095e0da3-ce9c-460a-87fd-9282e8d8fb74

## Links
* [Nuxt 3](https://nuxt.com/)
* [Nuxt UI](https://ui.nuxt.com/)
* [Tailwind CSS](https://tailwindcss.com/)
* [Laravel 10x](https://laravel.com/docs/10.x)

## License
[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Fk2so-dev%2Flaravel-nuxt.svg?type=large)](https://app.fossa.com/projects/git%2Bgithub.com%2Fk2so-dev%2Flaravel-nuxt?ref=badge_large)
