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

## CI/CD Deployment

The repository now includes `.github/workflows/deploy.yml` for GitHub Actions based CI/CD and `scripts/deploy.sh` for the server-side release step.

### 1. One-time server setup

1. Create the project directory on the server, for example `/www/wwwroot/accounts.santrains.com`.
2. Upload this project once or leave the directory empty.
3. Create the production `.env` file inside the deploy path.
4. Make sure the target PHP binary can run Artisan commands. In aaPanel this is often something like `/www/server/php/82/bin/php`.
5. Make sure the SSH user has write access to the deploy path.

### 2. GitHub repository secrets

Add these repository secrets before running the workflow:

- `SSH_HOST`: server IP or hostname
- `SSH_PORT`: usually `22`
- `SSH_USER`: SSH username
- `SSH_PRIVATE_KEY`: private key for the deploy user
- `DEPLOY_PATH`: absolute path on the server, for example `/www/wwwroot/accounts.santrains.com`
- `PHP_BIN`: server PHP binary path, for example `/www/server/php/82/bin/php`

### 3. Deployment flow

On every push to `main`, GitHub Actions will:

1. Install PHP and Node dependencies
2. Run migrations and tests in CI
3. Build frontend assets
4. Package the production-ready application
5. Upload it to the server over SSH
6. Run the remote deploy script, migrations, caches, and storage link

### 4. Important note

The workflow does not create the production `.env` file for you. Keep that file only on the server and do not commit it to git.
