# cPanel Deployment Guide (No Composer PHP MVC)

## 1) Upload files
- Upload the project to your domain root or a subfolder.
- Ensure these exist on server:
  - `index.php`
  - `.htaccess`
  - `app/`, `routes/`, `config/`, `database/`, `css/`, `js/`, `images/`

## 2) Configure environment
- Copy `.env.example` to `.env`
- Set:
  - `APP_BASE_URL` (example: `https://yourdomain.com` or `https://yourdomain.com/subfolder`)
  - `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

## 3) Create database
- Create a MySQL database + user in cPanel.
- Assign user to database with ALL privileges.

## 4) Import schema + seed
- Open **phpMyAdmin**
- Import `database/schema.sql`
- Import `database/seed.sql`

## 5) Admin access
- URL: `/admin/login`
- Default:
  - Email: `admin@example.com`
  - Password: `admin123`
- Change the admin password immediately by updating `admins.password_hash` with a new `password_hash()` value.

## 6) Pretty URLs (important)
- Confirm `.htaccess` is working.
- If installed in a subfolder, set `APP_BASE_URL` accordingly.

## 7) Security checklist
- Remove any unused old `.html` files from web root if you don’t need them.
- Keep `.env` **not publicly accessible** (default Apache should block dotfiles; verify on your hosting).
- Use strong DB passwords.

## 8) Performance
- Enable PHP OPcache in cPanel (if available).
- Optimize image sizes and keep `loading="lazy"`.
