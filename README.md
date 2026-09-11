# Starhaven Oculus

Oculus is Starhaven's account and control-plane API. It owns player registration and authentication. The authoritative Node.js game servers own gameplay simulation.

No Laravel starter kit is installed. The API uses Laravel Sanctum bearer tokens.

## Requirements

- PHP 8.3 or newer
- Composer
- PostgreSQL

## Setup

```bash
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

Configure the PostgreSQL connection and `GAME_SERVER_TOKEN` in `.env` first. Generate a server credential with:

```bash
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```

Set the same credential as `GAME_SERVER_TOKEN` in Oculus and `OCULUS_GAME_SERVER_TOKEN` in each trusted game-server environment. Do not put it in the Phaser client.

## Account API

Register with `POST /api/auth/register`:

```json
{
	"name": "Player One",
	"email": "player@example.com",
	"password": "password123",
	"password_confirmation": "password123"
}
```

Successful registration returns `201 Created` with `{"success":true}`. It does not log the player in or create an access token.

Log in with `POST /api/auth/login`:

```json
{
	"email": "player@example.com",
	"password": "password123",
	"device_name": "starhaven-client"
}
```

The login endpoint returns a Sanctum access token. Send it to Oculus account endpoints as:

```http
Authorization: Bearer <access-token>
Accept: application/json
```

Authenticated session endpoints:

| Method | Endpoint | Purpose |
| --- | --- | --- |
| `GET` | `/api/auth/session` | Validate a token and return its user |
| `PUT` | `/api/auth/session` | Rotate the current token |
| `DELETE` | `/api/auth/session` | Revoke the current token |
| `DELETE` | `/api/auth/sessions` | Revoke all tokens for the user |

Access tokens expire after `ACCESS_TOKEN_EXPIRATION_MINUTES`. An expired token cannot be refreshed and the client must log in again.

## Game-server Handoff

Do not send the reusable Sanctum access token to a game server. Before opening its gameplay socket, the client requests a short-lived ticket from Oculus:

```http
POST /api/game-tickets
Authorization: Bearer <access-token>
Accept: application/json
```

Oculus returns a single-use ticket. The client sends that ticket in its initial socket authentication payload. The game server consumes it server-to-server:

```http
POST /api/internal/game-tickets/consume
Authorization: Bearer <game-server-token>
Content-Type: application/json
Accept: application/json

{"ticket":"<game-ticket>"}
```

A successful response contains the authenticated user's `id` and `name`. An invalid, expired, or previously consumed ticket returns `401`. The game server should reject the socket in that case.

After a successful handoff, the authenticated socket is the gameplay session. Oculus does not need to validate every gameplay message. Future bans or forced logouts should be pushed to game servers as control events.

---

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
