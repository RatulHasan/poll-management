# Poll Management System

A real-time poll management system built with Laravel, Inertia, React, and TailwindCSS. Features include live voting updates, dark mode support, and comprehensive poll management capabilities.

## Features

-  Real-time vote updates using WebSockets
-  Dark mode support
-  User authentication with Laravel Breeze
-  Live poll results with animated charts
-  Support for both authenticated and anonymous voting
-  Poll expiration management
-  One vote per user/IP (via user_id or IP tracking)
-  Responsive design

## Requirements

- PHP ^8.2
- Node.js 23+
- Composer
- MySQL or PostgreSQL or SQLite
- Pusher account for WebSocket functionality

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd poll-management
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install Node.js dependencies:
```bash
pnpm install
```
If you don't have pnpm installed, remove pnpm-lock.yaml and run:
```bash
npm install
```

4. Configure environment:
```bash
cp .env.example .env
php artisan key:generate
```

5. Configure your database in .env and run migrations:
```bash
php artisan migrate --seed
or
php artisan db:seed --class=PollsSeeder
```

6. Setup Pusher for WebSocket functionality:
   - Create a free account at https://pusher.com
   - Create a new app in Pusher
   - Copy the credentials to your .env file:
     ```
     PUSHER_APP_ID=your_app_id
     PUSHER_APP_KEY=your_app_key
     PUSHER_APP_SECRET=your_app_secret
     PUSHER_APP_CLUSTER=your_app_cluster
     ```

7. Start the development servers:
```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Vite development server
pnpm dev

# (Optional) Terminal 3: Queue worker for notifications (broadcast queue)
php artisan queue:work
```

## Usage

### Admin Poll Creation

1. Using the Web Interface:
   - Login as an admin
   - Navigate to /admin/polls
   - Click "Create New Poll"
   - Fill in the poll details and options

2. Using Command Line:
```bash
php artisan poll:create [user_id]
```

### Poll Sharing

Each poll has a unique, shareable URL in the format:
```
http://your-domain/polls/{poll_id}
```

## WebSocket Setup

This project uses Pusher as the broadcast driver (pusher/pusher-php-server). You don't need to run a local WebSocket server.

### Configure Pusher
1. Create an account at https://pusher.com and create an app.
2. Update your .env file with credentials (already scaffolded in .env.example):
```env
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=your_cluster
```
3. Vite will pick up the VITE_* values for Echo. Start the app with `pnpm dev`.

### Production Notes
- Ensure broadcasting, cache, session, and queue are configured appropriately.
- Run a queue worker for broadcasting notifications if using queue: `php artisan queue:work`.
- No websockets:serve process is required for Pusher.

## Testing

Run the full test suite:
```bash
php artisan test
```

Key tests included:
- tests/Feature/PollVotingTest.php: end-to-end voting, validation, and view rendering
- tests/Feature/Services/VoteServiceTest.php: service-level invariants and statistics

Manual real-time test:
- Open two browser windows to a poll page; submit a vote in one, observe live updates in the other.
