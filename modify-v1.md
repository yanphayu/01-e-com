# modify-v1 — Make TRINITY Better

Monorepo: backend (Laravel 13) + frontend (Vue 3 storefront) + admin-frontend (Vue 3 admin).
Goal: realtime, honest, unified, polished — with fewer moving parts to run.

## Phase 1 — Realtime notifications (backend) ✅
- [x] 1.1 Use per-user private channel `App.Models.User.{id}` for admins too (no separate channel needed — admins are Users). Verified by BroadcastChannelAuthorizationTest
- [x] 1.2 New report → `ProductReported` already database+broadcasts to each admin (existing mechanism verified)
- [x] 1.3 New product → added `App\Notifications\NewProductCreated`, dispatched from `ProductController::store`
- [x] 1.4 Chat unread → added `App\Events\ChatUnreadEvent` (broadcastAs `chat.unread`) on recipient `App.Models.User.{id}`, dispatched from `ChatController::send`
- [x] 1.5 `.env.example`: `BROADCAST_CONNECTION=reverb` + REVERB dev values
- [x] 1.6 Tests: `NewProductCreatedTest`, `BroadcastChannelAuthorizationTest` (6 new). Fixed pre-existing: missing `remember_token` column, missing test APP_KEY, `ChatBlockedUsersTest` session→Sanctum auth, `welcome.blade.php`. Full suite 25/25 green

## Phase 2 — Retired the Vue admin SPA ✅
- [x] 2.1 Deleted `admin-frontend/` (Vue app, `node_modules`, built `dist`, `.env`) and `backend/public/admin-assets/` — users with the OLD `looker.adm`/IndexedDB leftover should whitelist-clear it; nothing references it anymore
- [x] 2.2 Admin is now server-rendered Blade (no separate admin frontend to build/deploy); all admin routes live in `routes/web.php` under `/admin/*` with `auth` + `admin` (`AdminMiddleware`) middleware — the middleware redirects guests to `admin.login` and `abort(403)` for non-admins
- [x] 2.3 `Admin\AuthController` does session login/logout at `/admin/login` (POST throttled 6/min) — form posts, server validation, flash; no bearer tokens; `AdminMiddleware` + app bootstrap `redirectGuestsTo(route('admin.login'))`
- [x] 2.4 Admin pages (Blade `resources/views/admin/**`): dashboard (KPIs, 30-day traffic line chart, sparklines, pending reports), analytics, products (index by status, show, approve/reject/toggle/hide, delete), users, reports (resolve), and full catalog CRUD (categories, subcategories, brands, models, attributes) — all with `@csrf` forms, resource controllers, implicit model binding
- [x] 2.5 Realtime admin notification bell (real): `laravel-echo` + `pusher-js` added to the backend, `resources/js/admin.js` subscribes to the user's private channel `App.Models.User.{id}` (using the shared `AppServiceProvider` view composer the bell renders the badge count via `/admin/notifications/unread-count`), live events prepend into the dropdown, mark-read/mark-all-routes; `VITE_REVERB_*`/`VITE_REVERB_APP_KEY` in `.env(.example)`; Reverb + `php artisan serve` only (no extra Vite dev server for admin — Vite just bundles `admin.js` into `public/build`)
- [x] 2.6 `BackendBroadcastChannelAuthorizationTest` + admin tests updated for the Blade flow (`AdminAuthTest`, `AdminPagesRenderTest`, rewritten `ProductApprovalTest`/`ProductReportTest` as web form posts; full suite 32 green)

## Phase 3 — Storefront UX/UI
- [ ] 3.1 Global toast system; route all ad-hoc success/error states through it
- [ ] 3.2 Shared loading / empty / error pattern (replace hand-rolled per-view)
- [ ] 3.3 Voice messages: implement MediaRecorder (or remove the dead toggle)
- [ ] 3.4 Split ChatView (3,500 lines) into: MessageRow, ConversationItem, DetailsDrawer, Composer
- [ ] 3.5 Unify admin + storefront design tokens (single palette)

## Phase 4 — Dev orchestration + quality
- [ ] 4.1 Root `package.json` + `concurrently`: one `npm run dev` boots API + queue + Reverb + both Vites
- [ ] 4.2 Single-deploy production wiring (Laravel serves admin + built storefront)
- [ ] 4.3 ESLint + Prettier for both Vue apps; Vitest smoke tests
- [ ] 4.4 PHPUnit coverage: auth, admin gating, moderation, chat

## Cross-cutting notes
- No raw tokens in localStorage after Phase 3 (session cookies): admin uses `/sanctum/csrf-cookie` → session login; storefront still uses bearer tokens.
- `php artisan serve` (built-in web server) CANNOT serve path-like URLs under a real directory (e.g. `/admin/login` with `public/admin/` present) → 404/PATH_INFO mangling. So the SPA lives under a **virtual** `/admin/*` (Laravel route) with static assets in `public/admin-assets/`; never create `public/admin/`. Apache/Nginx deep links fall through to the catch-all route fine.
- Tests run non-stateful (`SANCTUM_STATEFUL_DOMAINS=disabled` in `phpunit.xml`); SPA session flow covered by `AdminSessionAuthTest` (login establishes session, no token created, non-stateful still returns a token).
- `ProductController::store` auto-approves `status='approved'` — decide: keep or route to moderation.
- `.env.example` must default to `reverb`, not `log`, for realtime to work out of the box.
- Cache/queue/session are `database` drivers — but broadcasts are `ShouldBroadcastNow` and notifications are not `ShouldQueue`, so **no queue worker is needed** for realtime; only the API server (`artisan serve`) + Reverb must run in dev (`:8080`).