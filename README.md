# pseudo

A proof-of-work based imageboard built with Laravel. Every post, thread, and chat message requires solving cryptographic puzzles to submit.

## Features

- **Four Boards**: /gen/, /tech/, /doodle/, /meta/
- **Proof-of-Work Mining**: Browser-based SHA-256 mining with 8 difficulty levels
- **Points System**: Earn points based on computational effort (5 to 25,000 points)
- **Chatrooms**: Real-time chat with PoW requirements
- **Chorums**: Personal blogs for each user
- **Full Audit Trail**: All proof-of-work attempts logged with hash, nonce, and IP

## Installation

### Requirements

- PHP 8.1 or higher
- Composer
- SQLite (or MySQL/PostgreSQL)

### Setup

1. Clone the repository:
```bash
git clone https://github.com/nozmo-king/pseudo.git
cd pseudo
```

2. Install dependencies:
```bash
composer install
```

3. Set up environment:
```bash
cp .env.example .env
php artisan key:generate
```

4. Create database:
```bash
touch database/database.sqlite
```

5. Run migrations:
```bash
php artisan migrate:fresh --seed
```

6. Start the development server:
```bash
php artisan serve
```

7. Visit http://localhost:8000

## Point System

The more computational effort you invest, the more points your content earns:

- **21e8** = 5 points (easiest, fastest)
- **21e80** = 15 points
- **21e800** = 45 points
- **21e8000** = 100 points
- **21e80000** = 675 points
- **21e800000** = 1,000 points
- **21e8000000** = 5,000 points
- **21e800000000** = 25,000 points (hardest, slowest)

## How It Works

1. Write your post/thread/message
2. Click submit
3. Your browser automatically mines a proof-of-work puzzle
4. Once solved, the content is submitted
5. Points are awarded based on difficulty

All proof-of-work is verified server-side to prevent cheating.

## Database Schema

- **boards** - The four discussion boards
- **threads** - Thread topics with points
- **posts** - Replies to threads with points
- **users** - User accounts with total points
- **proof_of_works** - Audit log of all PoW attempts
- **chatrooms** - Chat channels
- **chat_messages** - Chat messages with points
- **chorum_posts** - User blog posts with points

## License

MIT