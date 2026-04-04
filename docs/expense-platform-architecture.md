# Expense Platform Foundation

This repository now contains a parallel JavaScript foundation for the requested scalable stack without disturbing the existing PHP application.

## Layout

- `database/postgresql_expense_management_schema.sql`: normalized PostgreSQL schema for users, groups, members, expenses, splits, balances, settlements, receipts, and invitation tokens.
- `backend/`: Express API foundation with JWT auth, split-engine utilities, OCR proxy controller, secure invitation generation, and Dinic-based debt simplification.
- `frontend/`: Next.js-style React/Tailwind foundation with a navy-and-green landing page, an email-first auth gateway, a Recharts dashboard, and a receipt OCR uploader.

## Runtime Boundaries

- Frontend: Next.js app-router structure, React components, Tailwind theme tokens, and client components for charts and uploads.
- Backend: Express controllers expose JSON APIs and treat PostgreSQL as the source of truth for balances, invitations, and settlement operations.
- Database: `balances` stores net debtor-to-creditor exposure per group and currency so dashboards and settlement flows do not need to recalculate from raw splits on every request.

## Core Flows

1. Auth entry:
   The frontend submits an email to `POST /api/auth/entry`. If the email is missing from PostgreSQL, the API responds with `nextStep: "signup"` so the client can immediately route into the registration pipeline.
2. Expense creation:
   The backend split engine works in minor currency units to avoid floating-point errors, supports equal, exact, and percentage modes, and emits deterministic remainders.
3. OCR:
   Receipt images are sent as `multipart/form-data` to the API, which proxies them to Taggun or Google Cloud Vision and normalizes item lines plus totals.
4. Group debt simplification:
   The API builds a debtor-to-creditor flow network only across already-existing debtor relationships, runs Dinic max-flow, and then prunes removable edges to reduce settlement count while preserving the no-new-debt rule.
