# Bhs Union API (PHP + MySQL)

This backend matches the Flutter app flows: members contribute, admins confirm contributions and record withdrawals. Announcements and chat are stored in MySQL. Chat messages are retained for 8 days on the server.

## Deploy (InfinityFree)
1) Create a MySQL database and user in your InfinityFree control panel.
2) Import `database.sql` into the database.
3) Upload the `api/` folder into `htdocs/api` (or `public_html/api`).
4) Edit `api/config/config.php` with your DB credentials.
5) Set your mobile app base URL to: `https://your-domain.com/api`.

## Deploy (Render - PHP + Apache, MySQL on InfinityFree)
1) Deploy the `Hosting/` folder as a Render Docker service.
2) Set environment variables in Render:
   - `DB_HOST=sql306.infinityfree.com`
   - `DB_NAME=if0_40807765_bhsUnion`
   - `DB_USER=if0_40807765`
   - `DB_PASS=your_password`
   - `ALLOWED_ORIGINS=*` (or your app domain)
3) Use the Render service URL in the Flutter app `ApiConfig.baseUrl`.

## Create the first admin
Run this SQL once (replace values):

```sql
INSERT INTO users (email, password_hash, name, role, status)
VALUES ('admin@bhs.local', '$2y$10$HASH_FROM_PHP_PASSWORD_HASH', 'Admin', 'admin', 'active');
```

Generate the hash with:
```php
<?php echo password_hash('YourPassword', PASSWORD_DEFAULT); ?>
```

## Auth
- `POST /auth/login` -> returns `token`.
- Use `Authorization: Bearer <token>` for all protected routes.
- `POST /auth/change-password`

## Core Endpoints
- `POST /auth/login`
- `GET /auth/me`
- `POST /auth/logout`

Members
- `GET /member/profile`
- `PUT /member/profile`
- `POST /member/contributions`
- `GET /member/contributions`

Admin
- `GET /admin/members?status=pending|active|disabled`
- `POST /admin/members`
- `PATCH /admin/members/{id}/status`
- `POST /admin/members/{id}/reset-password`
- `GET /admin/contributions?status=pending|confirmed`
- `POST /admin/contributions/{id}/confirm`
- `POST /admin/withdrawals`
- `GET /admin/withdrawals`
- `GET /admin/reports`
- `GET /admin/messages`
- `POST /admin/settings/monthly-amount`
- `POST /admin/settings/current-period`
- `PUT /admin/about`
- `GET /admin/audit`
- `GET /admin/audit/export`
- `POST /admin/audit/rotate`
- `GET /admin/exports/contributions`
- `GET /admin/exports/withdrawals`

Announcements
- `GET /announcements`
- `POST /admin/announcements`
- `PUT /admin/announcements/{id}`
- `DELETE /admin/announcements/{id}`

Chat (8-day retention enforced server-side)
- `GET /chat`
- `POST /chat`
- `PUT /chat/{id}`
- `DELETE /chat/{id}`
- `POST /chat/{id}/reactions`

Settings
- `GET /settings/monthly-amount?month=September&year=2025`
- `GET /settings/current-period`
- `GET /about`

## Notes
- Chat retention is enforced in `ChatController` by deleting messages older than 8 days on read/write.
- For production, set `ALLOWED_ORIGINS` in `api/config/config.php` to your domain.
 - Default rate limit: 120 requests/minute per IP (login: 10/minute).
