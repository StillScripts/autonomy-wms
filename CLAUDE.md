# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

**Start Development Environment:**

```bash
composer run dev  # Runs server, queue, logs, and vite concurrently
```

**Individual Services:**

```bash
php artisan serve          # Laravel server
php artisan queue:listen    # Queue worker
php artisan pail           # Real-time logs
npm run dev                # Vite frontend dev server
```

**Database:**

```bash
php artisan migrate --seed  # Run migrations and seeders
php artisan migrate:fresh --seed  # Fresh database with seed data
```

**Code Quality:**

```bash
npm run lint       # ESLint with auto-fix
npm run format     # Prettier formatting
npm run types      # TypeScript type checking
php artisan test   # Run Pest test suite
```

**Build:**

```bash
npm run build      # Production build
npm run build:ssr  # SSR production build
```

## Architecture Overview

**Tech Stack:**

- **Backend:** Laravel 12 + PHP 8.2+
- **Frontend:** React 19 + TypeScript + Inertia.js
- **Database:** PostgreSQL (use locally for production parity)
- **UI:** Tailwind CSS v4 + shadcn/ui + Radix UI
- **Forms:** TanStack React Form + Arktype validation
- **Payment:** Stripe integration
- **File Storage:** AWS S3 via Flysystem
- **Testing:** Pest framework

**Multi-Organization SaaS Structure:**

```
Organisation → User (many-to-many with roles)
Organisation → Website → Page → ContentBlock
Organisation → Product → Customer → Payment
Organisation → ThirdPartyProvider (configurable integrations)
```

## Key Conventions

**File Naming:**

- PHP: PascalCase (`ContentBlockController.php`)
- Frontend: kebab-case (`content-block-form.tsx`)

**Form Handling:**

- Use TanStack React Form with Arktype validation
- Reusable forms support both create/edit modes via `ReusableForm<T>` interface
- After successful `store`: redirect to `edit` route
- After successful `update`: redirect to `index` route

**Database:**

- Always use PostgreSQL locally (matches production)
- Use migrations: `php artisan make:migration CreateExampleTable`
- Models in `app/Models/`, Controllers in `app/Http/Controllers/`

**TypeScript:**

- Model types in `resources/js/types/models.ts`
- Path alias `@/*` points to `resources/js/*`
- Use inferred types, avoid redundancy

**UI Components:**

- ShadCN components in `resources/js/components/ui/`
- Custom components in `resources/js/components/`
- Form fields use context from `form-context.tsx`

## Important Patterns

**Multi-tenancy:** All models scope to organizations automatically via policies and middleware.

**Payment Flow:** Stripe webhooks per organization, signature verification required.

**Content Management:** Global content blocks can be reused across pages within an organization.

**Authentication:** Laravel Sanctum with customer/admin separation and role-based access.

**File Uploads:** Private files stored in S3, public access via signed URLs.

## Development Setup Notes

- Requires PostgreSQL locally (not SQLite) for production parity
- Configure `.env` with Postgres credentials
- Stripe webhook configuration needed per organization
- Ideas API integration for AI-powered page generation

## Testing

- Feature tests for API endpoints and authentication flows
- Uses Pest framework with Laravel plugin
- Test against PostgreSQL (configure `.env.testing`)
