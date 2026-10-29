# Product API

This API is for products and their prices in different currencies. It uses Laravel 13, Eloquent and Sanctum.

The task is in `Prueba Backend.pdf`. The paths are `/products` and `/products/{id}/prices`. They do not start with `/api`.

## What you need

- Docker with Compose
- You do not need PHP on your computer. PHP runs inside Docker.

## Start

```bash
docker compose up --build
```

The app is at [http://localhost:8000](http://localhost:8000). MySQL is on port `33060`, only on this computer.

The first time, the app waits for MySQL, runs the migrations and adds demo data: one user, five currencies and two products. If you seed again, those two products go back to the demo values.

If you want the new files to use your user id, start it like this:

```bash
HOST_UID=$(id -u) HOST_GID=$(id -g) docker compose up --build
```

Stop with `docker compose down`. Add `-v` if you also want to delete the database.

## Demo user

| Email | Password |
| --- | --- |
| demo@example.com | password |

People cannot register from the API. Change this password if other people can open the server.

```bash
curl -s -X POST http://localhost:8000/auth/login \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{"email":"demo@example.com","password":"password"}'
```

Use the token on the next requests:

```bash
curl -s http://localhost:8000/products \
  -H 'Accept: application/json' \
  -H 'Authorization: Bearer TOKEN'
```

The token lasts 24 hours. `POST /auth/logout` deletes the current token.

## Docs

- Swagger: [http://localhost:8000/docs](http://localhost:8000/docs). Swagger UI 5.34.0 is in `public/vendor/swagger-ui`, so the page does not need internet.
- OpenAPI file: [docs/openapi.yaml](docs/openapi.yaml). The app also serves it at `/docs/openapi.yaml`.
- Postman: import [docs/postman_collection.json](docs/postman_collection.json). Run **Login** first.

## Endpoints

Product, price and currency routes need a token. The answer is JSON.

| Method | Path | What it does |
| --- | --- | --- |
| POST | `/auth/login` | Get a token |
| POST | `/auth/logout` | Delete the current token |
| GET | `/auth/me` | See the current user |
| GET | `/currencies` | List currencies |
| GET | `/currencies/{currency}` | Show one currency |
| GET | `/products` | List products |
| POST | `/products` | Create a product |
| GET | `/products/{product}` | Show one product |
| PUT | `/products/{product}` | Replace a product |
| DELETE | `/products/{product}` | Delete a product and its extra prices |
| GET | `/products/{product}/prices` | List extra prices |
| POST | `/products/{product}/prices` | Add a price in another currency |

`GET /products` can use `search`, `currency_id`, `per_page` (from 1 to 100, default 15) and `page`.

`PUT /products/{product}` replaces the whole product. You must send every field.

## Data

Money is a string with two decimals, for example `"12.50"`. Do not send it as a JSON number.

A product keeps `price`, `tax_cost` and `manufacturing_cost` in `currency_id`. `total_cost` is the sum of those three numbers. It is not a column.

`product_prices` keeps the same product in other currencies. Do not save the base currency there again. One product can have only one extra price for each currency.

`exchange_rate` is how many US dollars you get for 1 unit of that currency. `price_in_product_currency` changes an extra price into the product currency. It uses USD in the middle.

Demo currencies: USD (`1`), EUR (`1.08`), GBP (`1.27`), BRL (`0.18`), PYG (`0.00013`). Check the ids with `GET /currencies`. On a new database they start at 1.

## Security

- Sanctum tokens. Passwords are hashed. There is no public register.
- Form requests check the input. Models only fill some fields.
- Login: 5 tries per minute for the same email and IP.
- Other routes: 60 requests per minute.
- Security headers on the API (`nosniff`, no iframe, `no-store`).
- A browser can call the API with a token. The browser cannot send login cookies.

Set `APP_DEBUG=false` when this is not only on your computer.

## Tests

```bash
docker compose run --rm --no-deps app php artisan test
```

Tests use SQLite in memory. They do not need MySQL.

## Folders

- `app/Models` — models and money math
- `app/Http/Controllers/Api` — controllers. They stay small.
- `app/Http/Requests` — validation
- `app/Http/Resources` — the JSON shape
- `app/Services` — create, update, list and price rules
- `database/migrations`, `database/seeders`, `database/factories`
- `docs` — OpenAPI and Postman
