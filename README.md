# Accounting Software

Laravel 12 admin application for master data, income and expense tracking, invoices, certificates, reports, users, roles, permissions, and application settings.

## Requirements

- PHP 8.2 or newer
- MySQL or MariaDB
- Composer
- PHP extensions required by Laravel, DomPDF, and PhpSpreadsheet

## Local Setup

1. Copy `.env.example` to `.env` and configure the database, mail server, and initial admin values.
2. Run `composer install`.
3. Run `php artisan key:generate` for a new environment.
4. Create the configured database and run `php artisan migrate --seed`.
5. Run `php artisan storage:link`.
6. Start the app with `php artisan serve`.

The seeded developer administrator uses `ADMIN_EMAIL` and `ADMIN_PASSWORD`. When `ADMIN_EMAIL` is omitted, the mail from-address is used. Change the default password immediately outside local development.

## Main Features

- OTP and password login with password reset
- Companies, customers, products, services, tax classes, and labels
- Income and expense CRUD, document attachments, invoice PDFs, and Excel export
- Customer certificates and certificate PDFs
- Persistent business, PDF sponsor, and encrypted SMTP settings
- Route-level role and permission enforcement
- User activation, trash, and optional protected permanent deletion

## Verification

Run the automated suite with:

```bash
php artisan test
```
