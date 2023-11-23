<p align="center">
  <img src="https://raw.githubusercontent.com/k2so-dev/laravel-nuxt/main/art/logo.svg" width="500" />
</p>

## Laravel Nuxt Boilerplate

[![](https://img.shields.io/badge/Laravel-v10.33-ff2e21.svg)](https://laravel.com)
[![](https://img.shields.io/badge/nuxt.js-v3.8-04C690.svg)](https://nuxt.com)
[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Fk2so-dev%2Flaravel-nuxt.svg?type=shield&issueType=license)](https://app.fossa.com/projects/git%2Bgithub.com%2Fk2so-dev%2Flaravel-nuxt?ref=badge_shield&issueType=license)

The goal of the project is to create a simple template for quickly starting development on Laravel and Nuxt.

The project includes:
 - **Laravel 10** and **Nuxt 3**
  - **Laravel Octane** is a library for fast backend work.
 - **Sanctum** Token-based authorization is compatible with **SSR** and  **CSR**
  - Basic authorization methods have been implemented: registration, login, password recovery by email, confirmation by email. For methods of working with mail, Throttle is used.
 - **"ofetch"** preset for working with Laravel API, which makes it possible
use $**fetch** without having to resort to custom $**fetch** wrappers.
  - UI library **"Nuxt UI"** based on **TailwindCSS** and **HeadlessUI**.

### Installation
1. clone repository
2. `composer install`
3. `cp .env.example .env && php artisan key:generate && php artisan storage:link`
4. `php artisan octane:install`
5. `php artisan octane:start --watch --port=8000 --host=127.0.0.1`
6. `yarn install`
7. `yarn dev`

> Nuxt port is set in package.json scripts via **cross-env**

### Nuxt $fetch

To work with the api, the default path is **"/api/v1"**. All requests from **Nuxt** to the **Laravel API** can be executed without wrappers, as described in the **Nuxt.js** documentation. For example, the code for authorizing a user by email and password:
```ts
const { token } = useAuth();

const state = reactive({
  email: "",
  password: "",
});

const { data } = await useFetch("login", {
  method: "POST",
  body: { ...state },
});

if (data.value?.ok) {
  token.value = data.value.token;
  await router.push("/");
}
```
> In this example, a POST request will be made to the url **"/api/v1/login"**

### Authentication
**useAuth() composable** has everything you need to work with authorization.

Data returned by **useAuth**:
* `logged`: Boolean, whether the user is authorized
* `token`: Cookie, sanctum token
* `user`: User object, user stored in pinia store
* `logout`: Function, remove local data and call API to remove token
* `fetchUser`: Function, fetch user data

### Examples

#### Demo

https://github.com/k2so-dev/laravel-nuxt/assets/15279423/095e0da3-ce9c-460a-87fd-9282e8d8fb74

### Documentation links
* [Nuxt 3](https://nuxt.com/)
* [Nuxt UI](https://ui.nuxt.com/)
* [TailwindCSS](https://tailwindcss.com/)
* [Laravel 10x](https://laravel.com/docs/10.x)

[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Fk2so-dev%2Flaravel-nuxt.svg?type=large&issueType=license)](https://app.fossa.com/projects/git%2Bgithub.com%2Fk2so-dev%2Flaravel-nuxt?ref=badge_large&issueType=license)
