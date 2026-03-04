# InvoPilot Design Specification

## Overview
InvoPilot is a multi-tenant invoice SaaS application built with Laravel 12, Inertia.js 2, and React 19.

## Database Schema

### users
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, auto-increment |
| name | varchar(255) | NOT NULL |
| email | varchar(255) | UNIQUE, NOT NULL |
| email_verified_at | timestamp | nullable |
| password | varchar(255) | NOT NULL |
| avatar_url | varchar(255) | nullable |
| locale | varchar(5) | NOT NULL, default 'en' |
| timezone | varchar(50) | NOT NULL, default 'UTC' |
| two_factor_secret | text | nullable |
| two_factor_recovery_codes | text | nullable |
| two_factor_confirmed_at | timestamp | nullable |
| remember_token | varchar(100) | nullable |
| created_at | timestamp | nullable |
| updated_at | timestamp | nullable |

### organizations
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, auto-increment |
| name | varchar(255) | NOT NULL |
| slug | varchar(255) | UNIQUE, NOT NULL |
| owner_id | bigint | FK → users.id |
| logo_url | varchar(255) | nullable |
| address | text | nullable |
| phone | varchar(50) | nullable |
| tax_id | varchar(100) | nullable |
| default_currency | varchar(3) | NOT NULL, default 'USD' |
| invoice_prefix | varchar(10) | NOT NULL, default 'INV' |
| invoice_next_number | integer | NOT NULL, default 1 |
| default_payment_terms | integer | NOT NULL, default 30 |
| default_notes | text | nullable |
| created_at | timestamp | nullable |
| updated_at | timestamp | nullable |

### organization_user (pivot)
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, auto-increment |
| organization_id | bigint | FK → organizations.id (CASCADE) |
| user_id | bigint | FK → users.id (CASCADE) |
| role | enum | owner/admin/member |
| created_at | timestamp | nullable |
| updated_at | timestamp | nullable |

**Unique:** (organization_id, user_id)

### clients
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, auto-increment |
| organization_id | bigint | FK → organizations.id (CASCADE) |
| name | varchar(255) | NOT NULL |
| email | varchar(255) | nullable |
| phone | varchar(50) | nullable |
| company | varchar(255) | nullable |
| address | text | nullable |
| tax_id | varchar(100) | nullable |
| notes | text | nullable |
| created_at | timestamp | nullable |
| updated_at | timestamp | nullable |

**Indexes:** (organization_id), (organization_id, email)

### invoices
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, auto-increment |
| organization_id | bigint | FK → organizations.id (CASCADE) |
| client_id | bigint | FK → clients.id (SET NULL), nullable |
| invoice_number | varchar(50) | NOT NULL |
| status | enum | draft/sent/paid/overdue/cancelled |
| issue_date | date | NOT NULL |
| due_date | date | NOT NULL |
| subtotal | decimal(12,2) | NOT NULL, default 0 |
| tax_amount | decimal(12,2) | NOT NULL, default 0 |
| discount_amount | decimal(12,2) | NOT NULL, default 0 |
| total | decimal(12,2) | NOT NULL, default 0 |
| currency | varchar(3) | NOT NULL |
| notes | text | nullable |
| footer | text | nullable |
| sent_at | timestamp | nullable |
| paid_at | timestamp | nullable |
| cancelled_at | timestamp | nullable |
| created_at | timestamp | nullable |
| updated_at | timestamp | nullable |

**Indexes:** (organization_id, status), (organization_id, due_date), (client_id)
**Unique:** (organization_id, invoice_number)

### invoice_items
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, auto-increment |
| invoice_id | bigint | FK → invoices.id (CASCADE) |
| description | varchar(500) | NOT NULL |
| quantity | decimal(10,2) | NOT NULL |
| unit_price | decimal(12,2) | NOT NULL |
| tax_rate | decimal(5,2) | NOT NULL, default 0 |
| amount | decimal(12,2) | NOT NULL |
| sort_order | integer | NOT NULL, default 0 |
| created_at | timestamp | nullable |
| updated_at | timestamp | nullable |

### payments
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, auto-increment |
| invoice_id | bigint | FK → invoices.id (CASCADE) |
| amount | decimal(12,2) | NOT NULL |
| method | enum | bank_transfer/credit_card/cash/check/other |
| reference | varchar(255) | nullable |
| paid_at | timestamp | NOT NULL |
| notes | text | nullable |
| created_at | timestamp | nullable |
| updated_at | timestamp | nullable |

### recurring_invoices
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, auto-increment |
| organization_id | bigint | FK → organizations.id (CASCADE) |
| client_id | bigint | FK → clients.id (CASCADE) |
| frequency | enum | weekly/biweekly/monthly/quarterly/yearly |
| next_issue_date | date | NOT NULL |
| end_date | date | nullable |
| items | jsonb | NOT NULL |
| notes | text | nullable |
| tax_rate | decimal(5,2) | NOT NULL, default 0 |
| is_active | boolean | NOT NULL, default true |
| last_generated_at | timestamp | nullable |
| created_at | timestamp | nullable |
| updated_at | timestamp | nullable |

### activity_logs
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, auto-increment |
| organization_id | bigint | FK → organizations.id (CASCADE) |
| user_id | bigint | FK → users.id, nullable |
| action | varchar(100) | NOT NULL |
| subject_type | varchar(100) | NOT NULL |
| subject_id | bigint | NOT NULL |
| description | varchar(500) | NOT NULL |
| metadata | jsonb | nullable |
| created_at | timestamp | NOT NULL |

**Indexes:** (organization_id, created_at DESC), (subject_type, subject_id)

## Enums

### InvoiceStatus
`Draft`, `Sent`, `Paid`, `Overdue`, `Cancelled`

### PaymentMethod
`BankTransfer`, `CreditCard`, `Cash`, `Check`, `Other`

### Frequency
`Weekly`, `Biweekly`, `Monthly`, `Quarterly`, `Yearly`

### Currency
`USD`, `EUR`, `GBP`, `JPY`, `CAD`, `AUD`, `CHF`, `CNY`, `KRW`, `TWD`

### OrganizationRole
`Owner`, `Admin`, `Member`

## Architecture Patterns

### Multi-tenancy
- Organization-scoped queries via `organization_id`
- Middleware `EnsureOrganizationContext` resolves current org from session
- All data access filtered by current organization

### Authentication & Authorization
- Laravel Fortify for auth (2FA support)
- Policies for model-level authorization
- OrganizationRole enum for role-based access

### Middleware
- `EnsureOrganizationContext`: Resolves `current_organization_id` from session, loads organization, shares via Inertia
- `SetLocale`: Sets app locale from `user.locale`
