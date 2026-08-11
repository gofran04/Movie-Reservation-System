# 🎬 Movie Reservation System API

![Laravel](https://img.shields.io/badge/Laravel-12-red)
![Tests](https://img.shields.io/badge/tests-passing-brightgreen)
![CI](https://github.com/gofran04/Movie-Reservation-System/actions/workflows/tests.yml/badge.svg)

# 📖 Table of Contents
- [Project Overview](#-project-overview)
- [Features](#-features)
- [Architecture-Overview](#-architecture-overview)
- [Tech-Stack](#-tech-stack)
- [Database-Design](#-database-design)
- [Installation](#-installation)
- [Environment-Configuration](#-environment-configuration)
- [database-Setups](#-database-setup)
- [Running-the-Applications](#-running-the-application)
- [API-Endpoints](#-api-endpoints)
- [Stripe-Integration](#-stripe-integration)
- [Webhooks](#-webhooks)
- [Scheduled-Jobs](#-scheduled-jobs)
- [Testing](#-testing)
- [Security-Considerations](#-security-considerations)
- [Docker](#-docker)
- [Deployment](#-deployment)



# 🚀 Project Overview

A backend API for managing cinema seat reservations and handling payments using Stripe.

This project simulates a real-world movie booking platform where users can reserve seats for showtimes, complete payments through Stripe, and receive automatic reservation management through background jobs and webhook processing.

The system is designed with **clean architecture principles**, **service-based design**, and **robust automated testing** to demonstrate production-ready backend practices.

---

# 🚀 Features

* Movie showtime seat reservation
* Seat availability validation
* Automatic reservation expiration
* Stripe Checkout payment integration
* Stripe webhook handling
* Refund lifecycle management
* Idempotent webhook processing
* Background cleanup jobs
* Policy-based authorization
* Comprehensive feature tests

---

# 🏗 Architecture Overview

The system follows a layered structure separating responsibilities between controllers, services, and external gateways.

```
Controllers
    ↓
Services
    ↓
Gateways
    ↓
External APIs (Stripe)
```

### Reservation Lifecycle

1. User selects seats for a showtime.
2. Reservation is created and seats are locked.
3. User proceeds to payment through Stripe Checkout.
4. Stripe sends webhook after payment completion.
5. System updates payment status and confirms reservation.
6. If reservation expires before payment, seats are released automatically.

### Core Services

* `CreateReservationService`
* `CreateSeatService`
* `StripeCheckoutService`
* `StripePaymentWebhookService`
* `StripeRefundWebhookService`
* `ProcessPaymentService`
* `ProcessRefundService`

### Background Jobs

* Expired reservation cleanup
* Seat release handling

### Policies

Authorization rules ensure users can only access their own reservations and payments.

---

# 🧰 Tech Stack

* PHP 8.3
* Laravel 12
* PostgreSQL (Neon)
* Redis
* Stripe API
* Docker & Docker Compose
* Nginx
* Render
* cron-job.org
* Laravel Sanctum
* Laravel Queues
* Laravel Scheduler
* PHPUnit Feature Testing
---

# 🗄 Database Design

Core tables:

```
users
movies
halls
seats
showtimes
reservations
reservation_seats
payments
```

---

# ⚙ Installation

Clone the repository:

```bash
git clone https://github.com/gofran04/Movie-Reservation-System.git
cd movie-reservation-system
```

Install dependencies:

```bash
composer install
```

Or run the application using Docker:

```bash
docker compose -f docker-compose.prod.yml up --build
```

Create environment file:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

---

# 🧪 Environment Configuration

Configure database and Stripe credentials inside `.env`.

Example:

```
DB_CONNECTION=pgsql
DB_DATABASE=movie_reservation
DB_USERNAME=postgres
DB_PASSWORD=secret

STRIPE_KEY=your_stripe_public_key
STRIPE_SECRET=your_stripe_secret_key
STRIPE_WEBHOOK_SECRET=your_webhook_secret
```

---

# 🗃 Database Setup

Run migrations:

```bash
php artisan migrate
```

Seed initial data:

```bash
php artisan db:seed
```

---

# ▶ Running the Application

## Local Development

Start the Laravel development server:

```bash
php artisan serve
```

Start the queue worker:

```bash
php artisan queue:work
```

Run scheduled tasks:

```bash
php artisan schedule:work
```

## Docker

Build and start all services:

```bash
docker compose -f docker-compose.prod.yml up --build
```

Run the application in detached mode:

```bash
docker compose -f docker-compose.prod.yml up -d
```

Stop containers:

```bash
docker compose -f docker-compose.prod.yml down
```
---

# 📡 API Endpoints
### API (📬 Postman Collection)
You can test all API endpoints easily using the official Postman collection:

🔗 **[Download Collection](https://github.com/gofran04/Movie-Reservation-System/tree/dev/docs/Movie_Reservation_System.postman_collection.json)**

> Includes:
> - All available API endpoints  
> - Pre-filled examples  
> - Ready-to-send request bodies  

#### 🛠️ How to Use:
1. Open [Postman](https://www.postman.com/)
2. Click **Import** → **Upload Files**
3. Select the downloaded JSON file
4. Start testing the API!


# 💳 Stripe Integration

The system integrates with Stripe Checkout to process payments.

## Testing Stripe Webhooks Locally

Stripe webhooks require Stripe to send HTTP requests to your server.
When developing locally, you can use the Stripe CLI to forward webhook events to your Laravel application.

### Install Stripe CLI

Follow the installation guide from Stripe.

### Login to Stripe

```bash
stripe login
```

### Forward events to local server

```bash
stripe listen --forward-to localhost:8000/api/stripe/webhook
```

The CLI will output a webhook signing secret. Add it to your `.env` file:

```text
STRIPE_WEBHOOK_SECRET=whsec_...
```

### Trigger test events

You can simulate Stripe events using:

```bash
stripe trigger checkout.session.completed
```

This will send a webhook to your local application and trigger the payment success flow.

---

### Payment Flow

1. User creates reservation
2. Payment session is created via Stripe Checkout
3. User completes payment on Stripe
4. Stripe sends webhook event
5. System updates payment status

---

# 🔔 Webhooks

Stripe webhooks are used to update payment and refund states.

Webhook endpoint:

```
POST /api/stripe/webhook
```

Handled events:

* `checkout.session.completed`
* `checkout.session.expired`
* `charge.refunded`

Webhook handlers ensure **idempotent processing** to prevent duplicate state updates.

---

# ⏱ Scheduled Jobs

The application automatically cleans up expired reservations and releases their reserved seats.

## Cleanup Command

The cleanup is handled by:

```bash
php artisan reservations:cleanup-expired
```
The command dispatches the CleanupExpiredReservationsJob, which:

- Finds pending reservations whose expires_at has passed
- Changes their status from pending to cancelled
- Releases their reserved seats

## Scheduling

Laravel's scheduler is configured to run the cleanup command every minute:
```bash
Schedule::command('reservations:cleanup-expired')->everyMinute();
```
In production, an external cron service is used to trigger the cleanup because the Render free tier does not provide a native Cron Job service.

The cron service periodically sends a request to the application's protected cleanup endpoint:
```bash
POST /api/internal/cron/cleanup-expired
```

The endpoint is protected using a secret request header and triggers the Laravel cleanup command.

## Production Flow
External Cron Service
        ↓
/api/internal/cron/cleanup-expired
        ↓
Laravel Cleanup Command
        ↓
CleanupExpiredReservationsJob
        ↓
Expired Reservations → Cancelled
Reserved Seats → Released

This ensures expired reservations are automatically cleaned up in the deployed production environment.

---

# 🧪 Testing

The project includes comprehensive 
## feature tests.

Run tests locally:

```bash
php artisan test
```

Covered scenarios:

* Reservation creation
* Seat validation
* Payment success flow
* Payment retry flow
* Refund processing
* Webhook idempotency
* Authorization rules

## Continuous Integration (CI)

This project uses GitHub Actions for Continuous Integration to ensure code quality and prevent regressions.

Every push or pull request triggers an automated pipeline that:

1. Installs PHP dependencies using Composer
2. Sets up the Laravel environment
3. Runs database migrations
4. Executes the full automated test suite

If any test fails, the CI pipeline will fail and the change should be fixed before merging.

### CI Workflow

Location of workflow configuration:

```bash
.github/workflows/tests.yml
```
### Pipeline Steps

The CI pipeline performs the following steps:

- Checkout repository
- Setup PHP environment
- Install dependencies
- Prepare .env file
- Run migrations
- Execute PHPUnit tests

### Example Workflow Trigger

CI runs automatically when:

- Code is pushed to supported branches
- A Pull Request is opened or updated

### CI Status

You can view workflow runs in the Actions tab of the repository.

---

# 🔐 Security Considerations

* Stripe webhook signature verification
* Database transactions for consistency
* Row-level locking during payment updates
* Idempotent webhook handlers

---

# 🐳 Docker

The application is fully containerized for local development and production deployment.

## Services

The Docker environment includes:

| Service | Purpose |
|----------|---------|
| Laravel App | Main application |
| Nginx | Reverse proxy |
| PostgreSQL | Database |
| Redis | Queue & Cache |
| Queue Worker | Processes queued jobs |

## Build

```bash
docker compose -f docker-compose.prod.yml up --build
```

## Run

```bash
docker compose -f docker-compose.prod.yml up -d
```

## Stop

```bash
docker compose -f docker-compose.prod.yml down
```

Laravel automatically performs:

- Database migrations
- Storage symlink creation

during container startup through the application entrypoint.

---

# 📦 Deployment

The application is deployed on **Render** using Docker.

Production services include:

- **Render** — Application hosting
- **Neon PostgreSQL** — Managed PostgreSQL database
- **Stripe** — Payment processing

## Deployment Flow

1. Push code to GitHub
2. Render automatically builds the Docker image
3. Application starts
4. Database migrations execute automatically
5. Storage link is created automatically
6. Laravel application starts serving requests

The production deployment uses environment variables for all sensitive configuration including:

- Application key
- Database credentials
- Stripe credentials
- Queue configuration

No secrets are committed to the repository.

---

# 🌐 Live Demo

Application:

https://movie-reservation-system-u6dl.onrender.com
