# Comments-with-Laravel-websockets
A simple application which displays newly created comments and upvote/downvote counts on each comment in real-time using websockets

## How to run
Must have:
- php 8.0 or greater
- MySQL
- composer
- npm

To run the application create a MySQL database named "websockets" (it can have a different name but then the .env file needs to be modified accordingly). After doing so run the following commands in the project directory:

Install npm packages:

```bash
npm install
```

Install composer packages:

```bash
composer install
```

Automatically create database tables:

```bash
php artisan migrate
```

Then in three separate terminals run:

(After running the following command a link will pop up. Cilck it when the other commands have been executed)

```bash
php artisan serve
```

```bash
php artisan websockets:serve
```

```bash
npm run hot
```