Troy Pavlek's Jeopardy Player
==============================

> Note: The `v2` branch is currently under active development and is a full re-write that will replace the existing code.
> If you're looking for the old project, check the `master` branch.
> 

Installation
=============

The application runs as a typical Laravel backend with a Vue SPA frontend.

You must have the following dependencies installed:

* PHP > 8.0
* Postgres
* Redis

To install, clone the repository. Copy the `.env.example` file to `.env` and fill out the appropriate configuration values
for your system.

Run `composer install` to install dependencies, then `php artisan migrate` to set up the database.

If you want a demo board full of dummy categories to test with you may run `php artisan db:seed`.

The frontend development server can be run via vite with `npm run dev`.

Testing
=========

PHPUnit tests can be executed via `php artisan test`.
