# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

Pending Order System: a Laravel 13 / PHP 8.3 app (Blade views, Vite + Tailwind 4, SQLite) that tracks purchase-order (PO) line items and how much of each has been delivered via delivery challans. The README is the stock Laravel one and carries no project-specific info.

## Commands

- `composer setup` — install deps, copy `.env`, generate key, migrate, build assets
- `composer dev` — runs `artisan serve`, queue listener, `pail` logs and Vite together
- `composer test` — clears config then runs `php artisan test` (PHPUnit; tests use in-memory SQLite)
- Single test: `php artisan test --filter=TestName` or `php artisan test tests/Feature/ExampleTest.php`
- `vendor/bin/pint` — code style (Laravel Pint)
- `npm run build` / `npm run dev` — Vite assets
- PO PDF uploads go to the `public` disk, so `php artisan storage:link` is needed for the "PDF" links on the order page to work.

## Architecture

All routes are in `routes/web.php`, behind the `auth` middleware except login (single `LoginController`; there is no registration). Controllers are thin, query-heavy, and render Blade directly (no API layer, no form requests, validation is inline in controllers).

Domain model (`database/migrations/2026_04_19_000001_create_tracking_tables.php` plus later alterations):

- `Client` → has many `Order` (`orders.client_id`, nullable only for legacy rows, shown as "Unassigned") and many `DeliveryChallan`. Both FKs are `restrictOnDelete`; `ClientController::destroy` refuses to delete a client that has orders or challans.
- `Order` → has many `OrderItem`. An order is a per-client container; the real data is on the items: `item_name`, `po_number`, `quantity`, optional `notes`, optional `po_pdf_path`.
- `DeliveryChallan` → has many `DeliveryChallanLine`, each pointing at one `OrderItem` with a `quantity`. Unique on (challan, order_item).
- **Pending quantity is never stored.** It is always derived: `OrderItem.quantity − SUM(delivery_challan_lines.quantity)`. Reports and the dashboard get it from `App\Services\PendingStockService` (filters come from `App\Support\StockFilters`, built from the query string); `OrderItem::pendingQuantity()` and `DeliveryChallanController::create/store` compute it separately, so keep them consistent when changing the rule.
- **Client isolation:** a challan may only dispatch lines whose order belongs to the challan's client (enforced in `DeliveryChallanController::store` inside the locked transaction, and mirrored by client-filtering in `challans/create`). An order's client can't change if a challan for another client already delivers against it.

Key behaviors:

- `DeliveryChallanController::store` runs in a transaction, locks the selected `order_items` rows with `lockForUpdate` (ordered by id to avoid deadlocks), re-checks each requested quantity against current pending, and throws `RuntimeException` (caught and returned as a validation error) on over-delivery. Challan numbers are generated as `DC-{year}-{0001}` by `nextChallanNumber()`.
- `ReportController` serves the item-wise, item-and-po-wise (grouped by client + PO + item) and client-summary reports through `PendingStockService`, filterable by client, order, PO, PO date range and status (default: pending only). Each is shown as HTML and as a landscape PDF via `barryvdh/laravel-dompdf`; PDF links carry the same query string. PDF views live in `resources/views/reports/pdf/` and share `layout.blade.php`; they are separate templates from the on-screen report views, so changes to a report usually need edits in both.
- Orders can be created, viewed and edited (client/reference/notes only); challans are create/read only; clients have full CRUD except `show`.

Session, cache and queue default to the `database` driver in `.env.example`, so migrations must be run for the app to boot.
