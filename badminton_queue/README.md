# Badminton Player Queueing System (PHP + PDO + MySQL)

## Setup
1. Install XAMPP/Laragon, start Apache + MySQL.
2. Copy this folder to `htdocs/badminton_queue`.
3. Open phpMyAdmin > Import > `schema.sql`.
4. Edit `config/db.php` if your MySQL user/password differ.
5. Visit http://localhost/badminton_queue/ (redirects to login if not logged in).

## Entities
- users (login/register/logout, personal info)
- courts (PARENT) - added_by, last_updated_by, last_updated
- queue_entries (CHILD, many per court) - added_by, last_updated_by, last_updated

## Steps for the report (item 1)
1. Created the `users` table (schema.sql) with personal-info columns.
2. Added `added_by`, `last_updated_by`, `last_updated` columns (with foreign keys to users) to courts and queue_entries.
3. Built register.php (password_hash), login.php (password_verify + session), logout.php (session_destroy).
4. Added `require_login()` at the top of index.php and queue.php to redirect guests to login.php.
5. Saved `$_SESSION['user_id']` into added_by / last_updated_by on every INSERT/UPDATE.

## Screenshot checklist
Take full-desktop screenshots: register (empty form, filled form, success), login (3), courts insert/update/delete,
queue insert/update/delete (showing "Added by" and "Last updated by" columns), logout (2), phpMyAdmin schema of `users`.
Try opening index.php while logged out to show the redirect.
