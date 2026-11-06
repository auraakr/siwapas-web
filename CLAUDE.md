# SIWAPAS: Sistem Informasi Wisata Pasirnanjung

Website for Desa Wisata Pasirnanjung Geulis (Indonesia): public tourism content, facility rental booking, event registration, and manual bank-transfer payments verified by an admin. Visitors do not need an account.

## Stack
- Laravel (latest stable), PHP 8.3+
- Inertia.js + React + TypeScript with SSR, Tailwind CSS
- Filament admin panel at /admin
- PostgreSQL 16+, Redis (cache, queue, locks)
- Pest, Larastan, Laravel Pint, ESLint

## Architecture rules
- Modular monolith. Domain code lives in app/Domain/{Content,Catalog,Booking,Event,Payment,Notification}. Each domain has Models, Enums, Actions, Services, Policies, DTOs, Events. Controllers stay thin and call Actions.
- Booking and Event never depend on a payment provider. They depend on the Payment domain (Order, Payment, PaymentProof) and the PaymentMethod interface.
- There is NO online payment gateway. Payment is a manual bank transfer plus a proof-of-payment upload, verified by an admin. Never auto-approve a payment.
- Money is an integer in rupiah. Timestamps are stored in UTC and shown in Asia/Jakarta. A rental stores check_in and check_out as dates (check_out exclusive).
- Double booking is prevented at the database level (PostgreSQL exclusion constraint on date ranges) AND inside a transaction with a lock. Cover this with a concurrency test.
- Status changes go through explicit state-machine Actions that also write a status history row. Never update a status column directly.
- Public pages are server-side rendered with SEO meta tags and schema.org JSON-LD.
- Proof-of-payment files are stored on a private disk and served only through temporary signed URLs to authorized admins. Validate mime type and size.
- Visitors access their booking status through a random token (at least 40 characters) in a private link. Rate limit public write endpoints.
- All notifications are queued. Jobs must be idempotent.
- Interface text is Indonesian. Code, comments, and commit messages are English. Content columns are translatable (id, en).

## Business defaults (config/siwapas.php, never hard-coded)
- Rental unit: day. Slot hold and payment deadline: 24 hours.
- Unique code added to the transfer amount: random 1 to 999.
- Proof upload: JPG, PNG, or PDF, max 5 MB. Verification target: 1x24 hours.

## Commands
- composer test (runs Pest), ./vendor/bin/pint, ./vendor/bin/phpstan analyse
- npm run dev, npm run build
- php artisan queue:work, php artisan schedule:work

## Workflow
- Plan before coding and wait for approval on large changes.
- Small commits using Conventional Commits.
- Every feature ships with tests. Run Pint, Larastan, and Pest before reporting done.
- Do not add packages that are not listed here without asking.
- Never commit .env or any secret.

## Out of scope
- Online payment gateway, visitor accounts, UMKM cart or checkout (UMKM products are display only).
