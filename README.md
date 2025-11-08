# pseudochan

imageboard built on proof-of-work mining and secp256k1 authentication. no passwords.

## requirements

- php 8.3+
- sqlite3
- composer

## setup

```bash
cd web-app
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

## proof-of-work

all content requires mining SHA-256 hashes with `21e8` prefix.

- `21e8` = 15 points
- `21e80` = 60 points
- `21e800` = 240 points
- `21e8000` = 960 points

points = `15 * pow(4, zeros_after_21e8)`

## auth

secp256k1 signature verification. pubkey = identity.