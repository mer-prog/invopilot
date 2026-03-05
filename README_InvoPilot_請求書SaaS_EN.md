# InvoPilot — Multi-Tenant Invoice SaaS

> **What:** A full-stack invoice SaaS for managing clients, invoices, recurring billing, and payments within organization-scoped tenancy
> **Who:** Small businesses and freelancers; also serves as a portfolio piece demonstrating production-grade Laravel + React skills
> **Tech:** PHP 8.5 · Laravel 12 · React 19 · TypeScript · Inertia.js 2 · Tailwind CSS 4 · PostgreSQL 17

- Source Code: [github.com/mer-prog/invopilot](https://github.com/mer-prog/invopilot)

---

## Skills Demonstrated

| Skill | Implementation |
|-------|---------------|
| Full-Stack Architecture | Laravel 12 + Inertia.js 2 + React 19 monolith SPA. Server-side routing with client-side rendering — no separate API layer needed |
| Multi-Tenant Data Isolation | Every query scoped by `organization_id`. Middleware sets session context, Policies enforce org boundaries, Model scopes filter at query level |
| Service Layer Pattern | Controller → Service → Model separation. InvoiceService (168 lines) manages the entire invoice lifecycle: create, calculate, send, pay, duplicate |
| Event-Driven Architecture | 4 domain events (InvoiceCreated/Sent/PaymentRecorded/Overdue) with 3 listeners handling side effects (logging, status updates, email receipts) |
| REST API Design | Sanctum Bearer token auth, Eloquent API Resources for response shaping, org-scoped CRUD for clients and invoices |
| Internationalization | Full Japanese/English support across 10 translation files. `useTrans()` hook shares PHP translations to the React frontend with runtime switching |
| Test-Driven Development | 126 tests with 447 assertions using Pest 4. Covers controllers, services, policies, events, API endpoints, PDF generation, and email notifications |

---

## Tech Stack

| Category | Technology | Purpose |
|----------|-----------|---------|
| Language | PHP 8.5 | Backend runtime |
| Framework | Laravel 12 | Routing, ORM, auth, queues, events |
| Frontend | React 19 + TypeScript 5.7 | UI components and interactivity |
| Bridge | Inertia.js 2 | Server-side routing with client-side SPA rendering |
| Styling | Tailwind CSS 4 + shadcn/ui | Utility-first CSS with Radix UI-based components |
| Database | PostgreSQL 17 (Neon) | Production database. SQLite for local development |
| Auth | Laravel Sanctum 4 + Fortify 1 | Session auth, API tokens, TOTP-based 2FA |
| PDF | barryvdh/laravel-dompdf 3 | A4 invoice PDF download and email attachment |
| Charts | recharts 2 | Dashboard monthly revenue area chart |
| Drag & Drop | dnd-kit | Invoice line item reordering |
| Route Generation | Laravel Wayfinder | Auto-generated TypeScript functions for backend routes |
| Testing | Pest 4 | PHP testing framework |
| Linting | Laravel Pint (PSR-12) + ESLint 9 + Prettier 3 | Code quality enforcement |
| CI/CD | GitHub Actions | Automated testing and linting |
| Deployment | Docker + Render.com | Containerized deployment (Singapore region) |
| Build | Vite 7 | Frontend bundling with HMR |

---

## Architecture Overview

```
┌──────────────────────────────────────────────────────────────┐
│                     Browser (React 19)                        │
│   ┌───────────┐  ┌──────────┐  ┌──────────┐  ┌───────────┐  │
│   │ Dashboard  │  │ Clients  │  │ Invoices │  │ Settings  │  │
│   │ Stats +   │  │ CRUD     │  │ CRUD+PDF │  │ Profile + │  │
│   │ Charts    │  │          │  │ +Payments│  │ Org + 2FA │  │
│   └───────────┘  └──────────┘  └──────────┘  └───────────┘  │
└──────────────────────┬────────────────────────────────────────┘
                       │ Inertia.js 2 (XHR)
┌──────────────────────▼────────────────────────────────────────┐
│                   Laravel 12 Backend                           │
│                                                                │
│  ┌───────────────┐   ┌───────────────┐   ┌────────────────┐  │
│  │  Middleware    │──▶│  Controllers  │──▶│   Services     │  │
│  │ - Auth        │   │  - Web (6)    │   │  - Invoice     │  │
│  │ - Org Context │   │  - API (2)    │   │  - Recurring   │  │
│  │ - Locale      │   │  - Settings(5)│   │  - Dashboard   │  │
│  │ - Inertia     │   └──────┬────────┘   │  - PDF         │  │
│  └───────────────┘          │            └────────┬───────┘  │
│                             │                     │           │
│  ┌───────────────┐   ┌──────▼────────┐   ┌───────▼────────┐  │
│  │   Policies    │   │   Models      │   │    Events      │  │
│  │  - Client     │   │   (8 total)   │◄─▶│  - Created     │  │
│  │  - Invoice    │   │               │   │  - Sent        │  │
│  │  - Recurring  │   └──────┬────────┘   │  - Paid        │  │
│  └───────────────┘          │            │  - Overdue     │  │
│                             │            └───────┬────────┘  │
│                      ┌──────▼────────┐   ┌───────▼────────┐  │
│                      │  PostgreSQL   │   │   Listeners    │  │
│                      │  10 tables    │   │  - ActivityLog │  │
│                      └───────────────┘   │  - StatusUpdate│  │
│                                          │  - Receipt     │  │
│  ┌───────────────┐   ┌───────────────┐   └────────────────┘  │
│  │  Sanctum API  │   │  Queue / Jobs │                       │
│  │  Bearer Auth  │   │  - Email      │                       │
│  │  CRUD x 2     │   │  - PDF        │                       │
│  └───────────────┘   └───────────────┘                       │
└───────────────────────────────────────────────────────────────┘
```

---

## Key Features

### Dashboard
- Summary cards: current/previous month revenue, growth rate, outstanding amount, overdue count, client count
- Monthly revenue trend over the past 12 months rendered with recharts AreaChart
- Recent activity feed showing invoice creation, sending, payment recording, and overdue events
- Overdue invoice alert banner when applicable

### Client Management
- Full CRUD with organization-scoped queries (name, email, phone, company, address, tax ID, notes)
- Client list displays invoice count and total amount summaries via `withCount` / `withSum`
- Client detail page shows the 20 most recent invoices
- All queries enforced by `forOrganization()` model scope

### Invoice Management
- Full CRUD plus send, record payment, duplicate, download PDF, and cancel actions
- Drag-and-drop line item reordering with dnd-kit
- Real-time invoice preview panel reflecting form input as you type
- Status filtering (draft/sent/paid/overdue/cancelled) and date range filtering with sortable columns
- Automatic subtotal, tax, discount, and total calculation in the Service layer
- Type-safe status management via `InvoiceStatus` enum

### Payment Recording
- Supports both partial and full payments
- Payment methods: bank transfer, credit card, cash, check, other (`PaymentMethod` enum)
- Automatically updates invoice status to "paid" when total payments reach invoice total (`UpdateInvoiceStatus` listener)
- Sends payment receipt email to client upon recording (`SendPaymentReceipt` listener)

### PDF Generation
- Blade template renders A4 invoice layout, converted to PDF via dompdf
- Available as browser download and email attachment
- Queue-compatible via `GenerateInvoicePdf` job

### Recurring Invoices
- Frequency options: weekly, biweekly, monthly, quarterly, yearly (`Frequency` enum)
- Configurable next issue date and optional end date
- Active/inactive toggle
- Scheduled command `invoices:process-recurring` runs daily at 8:00 AM for auto-generation
- `invoices:check-overdue` runs daily at 9:00 AM for overdue detection

### Email Notifications
- Invoice sent notification with PDF attachment
- Payment receipt notification
- Overdue reminder notification
- All notifications implement `ShouldQueue` for async processing

### Organization Settings
- Organization details: name, address, phone, tax ID, logo URL
- Invoice defaults: currency, number prefix, payment terms (days), default notes
- Supported currencies: USD, EUR, GBP, JPY, CAD, AUD, CHF, CNY, KRW, TWD (`Currency` enum)

### Authentication & Security
- Laravel Fortify-powered login, registration, password reset, and email verification
- TOTP-based two-factor authentication with QR code, manual setup key, and recovery codes
- Session auth (web) and Sanctum token auth (API)
- Policy-based authorization enforcing organization boundaries

### REST API
- Sanctum Bearer token authentication
- Full CRUD for clients and invoices
- Eloquent API Resources for consistent response formatting
- Organization-scoped data isolation

### Internationalization
- Complete Japanese (default) and English support
- 10 translation files covering all UI text (common, auth, invoices, clients, recurring_invoices, dashboard, settings, payments, activity, navigation)
- `useTrans()` hook delivers translations to the entire React frontend
- Runtime language switching via header toggle button

### Dark Mode
- Three modes: system, light, dark
- Persisted via cookie

---

## Database Design

```
┌──────────┐       ┌───────────────────┐       ┌──────────┐
│  users   │◄─────▶│ organization_user │◄─────▶│organizat-│
│          │  M:N  │   (role pivot)    │  M:N  │  ions     │
└────┬─────┘       └───────────────────┘       └────┬─────┘
     │                                              │
     │ 1:N                                    1:N   │ 1:N
     ▼                                         ┌────▼─────┐
┌──────────┐                                   │ clients  │
│ activity │                                   └────┬─────┘
│ _logs    │                                        │
└──────────┘                                   1:N  │ 1:N
                                                ┌───▼────┐
                   ┌────────────┐               │invoices│
                   │ recurring  │               └───┬────┘
                   │ _invoices  │                   │
                   └────────────┘              1:N  │ 1:N
                                            ┌───────┴──────┐
                                            ▼              ▼
                                     ┌──────────┐  ┌──────────┐
                                     │ invoice  │  │ payments │
                                     │ _items   │  └──────────┘
                                     └──────────┘
```

| Table | Key Columns | Description |
|-------|------------|-------------|
| users | name, email, locale, timezone, 2FA fields | User accounts with Fortify integration |
| organizations | name, slug, default_currency, invoice_prefix, invoice_next_number | Tenant unit with invoice configuration |
| organization_user | organization_id, user_id, role | M:N pivot with Owner/Admin/Member roles |
| clients | organization_id, name, email, company, address, tax_id | Customer records scoped to organization |
| invoices | organization_id, client_id, invoice_number, status, 4 money columns, 3 date columns | Invoices with all money fields as `decimal(12,2)` |
| invoice_items | invoice_id, description, quantity, unit_price, tax_rate, amount | Line items with sort_order for drag-drop ordering |
| payments | invoice_id, amount, method, reference, paid_at | Payment records linked to invoices |
| recurring_invoices | organization_id, client_id, frequency, next_issue_date, items (JSON) | Recurring invoice templates |
| activity_logs | organization_id, user_id, action, subject (polymorphic), metadata (JSON) | Audit trail for all invoice operations |
| personal_access_tokens | tokenable (polymorphic), name, token, abilities | Sanctum API tokens |

---

## API Endpoints

All endpoints require Sanctum Bearer token authentication.

```
Authorization: Bearer <token>
```

| Method | Endpoint | Description | Response |
|--------|----------|-------------|----------|
| GET | `/api/clients` | List clients | ClientResource collection |
| POST | `/api/clients` | Create client | ClientResource |
| GET | `/api/clients/{id}` | Show client | ClientResource |
| PUT | `/api/clients/{id}` | Update client | ClientResource |
| DELETE | `/api/clients/{id}` | Delete client | 204 |
| GET | `/api/invoices` | List invoices | InvoiceResource collection |
| POST | `/api/invoices` | Create invoice | InvoiceResource |
| GET | `/api/invoices/{id}` | Show invoice | InvoiceResource (includes items, client) |
| PUT | `/api/invoices/{id}` | Update invoice | InvoiceResource |
| DELETE | `/api/invoices/{id}` | Delete invoice | 204 |

---

## Project Structure

```
invopilot/
├── app/
│   ├── Console/Commands/          # Artisan commands (2)
│   │   ├── CheckOverdueInvoices.php
│   │   └── ProcessRecurringInvoices.php
│   ├── Enums/                     # Type-safe enums (5)
│   │   ├── InvoiceStatus.php      # Draft/Sent/Paid/Overdue/Cancelled
│   │   ├── PaymentMethod.php      # BankTransfer/CreditCard/Cash/Check/Other
│   │   ├── Frequency.php          # Weekly/Biweekly/Monthly/Quarterly/Yearly
│   │   ├── Currency.php           # USD/EUR/GBP/JPY + 6 more currencies
│   │   └── OrganizationRole.php   # Owner/Admin/Member
│   ├── Events/                    # Domain events (4)
│   ├── Http/
│   │   ├── Controllers/           # Controllers (13)
│   │   │   ├── DashboardController.php
│   │   │   ├── ClientController.php
│   │   │   ├── InvoiceController.php      # 182 lines: CRUD + send/pay/duplicate/pdf
│   │   │   ├── RecurringInvoiceController.php
│   │   │   ├── LocaleController.php
│   │   │   ├── Api/               # API controllers (2)
│   │   │   └── Settings/          # Settings controllers (5)
│   │   ├── Middleware/            # Middleware (4)
│   │   └── Requests/              # Form requests (14)
│   ├── Jobs/                      # Queued jobs (2)
│   ├── Listeners/                 # Event listeners (3)
│   ├── Models/                    # Eloquent models (8)
│   ├── Notifications/             # Email notifications (3)
│   ├── Policies/                  # Authorization policies (3)
│   └── Services/                  # Business logic (4)
│       ├── InvoiceService.php     # 168 lines: invoice lifecycle management
│       ├── RecurringInvoiceService.php
│       ├── DashboardService.php   # 119 lines: stats and chart data
│       └── PdfService.php
├── resources/js/
│   ├── pages/                     # React pages (24)
│   │   ├── dashboard.tsx          # 290 lines: stat cards + chart + activity feed
│   │   ├── clients/               # List / create / edit / show (4)
│   │   ├── invoices/              # List / create / edit / show (4)
│   │   │   └── show.tsx           # 559 lines: detail + payments + actions
│   │   ├── recurring-invoices/    # List / create / edit (3)
│   │   ├── settings/              # 6 pages (profile/password/appearance/org/2fa/tokens)
│   │   └── auth/                  # 7 pages (login/register/reset/verify/2fa)
│   ├── components/                # React components (60+)
│   │   ├── invoices/
│   │   │   ├── invoice-form.tsx   # 435 lines: dnd-kit enabled form
│   │   │   └── invoice-preview.tsx # 214 lines: real-time preview
│   │   └── ui/                    # shadcn/ui component library
│   ├── hooks/                     # Custom hooks (5)
│   ├── layouts/                   # Layout components (4)
│   └── types/                     # TypeScript type definitions
├── lang/
│   ├── en/                        # English translations (10 files)
│   └── ja/                        # Japanese translations (10 files)
├── database/
│   ├── migrations/                # Migrations (15)
│   ├── factories/                 # Factories (7)
│   └── seeders/                   # Seeder (185 lines of demo data)
├── tests/
│   └── Feature/                   # Tests (28 files, 2,064 lines)
├── Dockerfile                     # Multi-stage build
├── render.yaml                    # Render.com deployment config
└── .github/workflows/ci.yml      # CI pipeline
```

**Codebase size:** PHP 3,504 lines / TypeScript 23,710 lines / Tests 2,064 lines

---

## Screen Specifications

| Screen | Path | Description |
|--------|------|-------------|
| Login | `/login` | Email/password auth with remember me and 2FA challenge flow |
| Registration | `/register` | Name, email, password with auto organization creation |
| Password Reset | `/forgot-password` → `/reset-password` | Email-based token reset flow |
| Email Verification | `/verify-email` | Verification email resend |
| Two-Factor Challenge | `/two-factor-challenge` | 6-digit OTP or recovery code input |
| Dashboard | `/dashboard` | 4 stat cards + 12-month revenue chart + activity feed |
| Client List | `/clients` | Paginated list with invoice count and total summaries |
| Client Create | `/clients/create` | Client form |
| Client Detail | `/clients/{id}` | Client info + invoice history |
| Client Edit | `/clients/{id}/edit` | Edit form |
| Invoice List | `/invoices` | Status filter + date range filter + sortable columns |
| Invoice Create | `/invoices/create` | Drag-and-drop line items + live preview |
| Invoice Detail | `/invoices/{id}` | Details + payment history + send/pay/duplicate/PDF/delete |
| Invoice Edit | `/invoices/{id}/edit` | Same UI as create page |
| Recurring List | `/recurring-invoices` | List with active/inactive toggle |
| Recurring Create | `/recurring-invoices/create` | Frequency selection + line items |
| Recurring Edit | `/recurring-invoices/{id}/edit` | Template editing |
| Profile Settings | `/settings/profile` | Name/email update + account deletion |
| Password Settings | `/settings/password` | Current password verification + new password |
| Appearance | `/settings/appearance` | Light/dark/system theme selection |
| Organization | `/settings/organization` | Organization info + invoice defaults |
| Two-Factor Settings | `/settings/two-factor` | Enable/disable 2FA + recovery codes |
| API Tokens | `/settings/api-tokens` | Token creation, listing, and revocation |

---

## Testing

| Category | Coverage | Files |
|----------|----------|-------|
| Authentication | Login, registration, password reset, email verification, 2FA | 6 |
| Settings | Profile update, password change, 2FA configuration | 3 |
| Controllers | Dashboard, client CRUD, invoice CRUD, recurring invoice CRUD | 4 |
| API | Client API, invoice API, token management | 3 |
| Features | PDF generation, payment recording, email notifications, overdue checks, recurring processing | 5 |
| Events | Event dispatch and listener execution verification | 1 |
| Authorization | Policy tests ensuring cross-organization access denial | 1 |
| **Total** | **126 tests / 447 assertions** | **28** |

Running tests:
```bash
php artisan test --compact              # All tests
php artisan test --filter=InvoiceTest   # Specific tests
```

Test environment: SQLite in-memory with `RefreshDatabase` trait for per-test isolation.

---

## CI/CD Pipeline

GitHub Actions runs two jobs in parallel:

| Job | Steps |
|-----|-------|
| tests | PHP 8.5 + Node 22 setup → composer install → npm ci → npm run build → `php artisan test` |
| lint | `vendor/bin/pint --test` (PHP) + `prettier --check` (TypeScript) + `npm run lint` (ESLint) |

---

## Seed Data

`php artisan migrate --seed` generates the following demo data:

| Data | Details |
|------|---------|
| Test user | `test@example.com` (Japanese locale, Tokyo timezone) |
| Organizations | 3 orgs (Acme Corp USD / Sakura Tech JPY / Global Trading EUR) |
| Clients | 5-10 per organization |
| Invoices | 20-50 per organization (status mix: 2 draft / 3 sent / 4 paid / 2 overdue / 1 cancelled) |
| Line items | 1-5 per invoice |
| Payments | Payment record for each paid invoice |
| Recurring invoices | 2-5 per organization |
| Activity logs | Invoice creation audit entries |

---

## Security Design

| Measure | Implementation |
|---------|---------------|
| Multi-Tenant Isolation | All queries scoped by `organization_id`. Middleware sets org ID in session |
| Authorization | 3 Policies verify organization boundary. `AuthorizesRequests` trait invoked from controllers |
| Sort Column Validation | User-supplied sort columns validated against a whitelist to prevent SQL injection |
| CSRF Protection | Laravel's built-in `VerifyCsrfToken` middleware |
| API Authentication | Sanctum token auth with org ownership verification in API controllers |
| Password Hashing | bcrypt (12 rounds production, 4 rounds testing) |
| Two-Factor Auth | TOTP + recovery codes via Fortify |
| XSS Prevention | Blade `{{ }}` auto-escaping and React JSX auto-escaping |
| Input Validation | Dedicated FormRequest class for every endpoint (14 total) |

---

## Setup

### Prerequisites
- PHP 8.5+ (pdo_pgsql, intl, gd, zip, bcmath extensions)
- Node.js 22+
- Composer 2
- PostgreSQL 17 (production) or SQLite (local development)

### Installation

```bash
git clone https://github.com/mer-prog/invopilot.git
cd invopilot

cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed

npm install
npm run dev

# In a separate terminal
php artisan serve
```

Visit `http://localhost:8000`. Test credentials: `test@example.com`

### Environment Variables

| Variable | Description | Required |
|----------|-------------|----------|
| APP_KEY | Application encryption key | Yes (auto-generated via `key:generate`) |
| DB_CONNECTION | Database driver (`sqlite` / `pgsql`) | Yes |
| DB_HOST | PostgreSQL host | Production only |
| DB_PORT | PostgreSQL port (default: 5432) | Production only |
| DB_DATABASE | Database name | Production only |
| DB_USERNAME | Database user | Production only |
| DB_PASSWORD | Database password | Production only |
| MAIL_MAILER | Mail driver (`log` / `smtp`) | No (default: log) |
| APP_LOCALE | Default locale (`ja` / `en`) | No (default: ja) |

---

## Design Decisions

| Decision | Rationale |
|----------|-----------|
| Inertia.js monolith over separate SPA | Eliminates the overhead of maintaining a separate API contract. Wayfinder provides type-safe route generation for the frontend |
| Service layer extraction | Prevents controller bloat. InvoiceService consolidates the entire invoice lifecycle (create, calculate, send, pay, duplicate) in one place |
| Event-driven side effects | Payment recording triggers status update, receipt email, and activity logging through separate listeners. Improves testability and separation of concerns |
| PHP enums for type safety | Status, currency, frequency, payment method, and role values constrained at the type level. Invalid values rejected before runtime |
| Organization-scoped queries | Most reliable approach to prevent data leakage. Enforced at three layers: middleware, policy, and model scope |
| SQLite (local) + PostgreSQL (production) | Balances local development simplicity with production scalability. DashboardService detects the DB driver and adjusts queries accordingly |
| `useTrans()` hook pattern | Shares PHP translation files as Inertia shared data to the React frontend, maintaining a single source of truth for all translations |
| shadcn/ui + Tailwind CSS 4 | Copy-paste customizable UI components with no vendor lock-in. Full control over component internals |
| Pest 4 over PHPUnit | More concise test syntax with `it()` + `expect()` chains. Better readability without sacrificing capability |
| dompdf over wkhtmltopdf | Pure Composer package with no external binary dependency. Simplifies container deployment significantly |

---

## Running Costs

| Service | Plan | Monthly Cost |
|---------|------|-------------|
| Render.com | Free (Docker) | $0 |
| Neon PostgreSQL | Free Tier | $0 |
| GitHub Actions | Free (public repo) | $0 |
| **Total** | | **$0** |

---

## Author

[@mer-prog](https://github.com/mer-prog)
