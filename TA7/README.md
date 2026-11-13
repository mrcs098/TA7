# Counter POS TFA7

Counter POS extends the supplied TA6 Laravel application with customer and user creation, editing, server validation, and prepared user avatars. The original green and white design, Home and About pages, SQLite database structure, and sample accounts are retained.

## Included features

- Create customers at `/customers/create` and edit existing customer records.
- Create users at `/users/create` and edit existing user records.
- Required full names, valid emails, unique usernames, and feedback beside invalid fields.
- Submitted text and selections redisplayed with `old()` after validation fails.
- Pre-filled edit forms, CSRF tokens, and PUT method fields.
- An additive migration introducing the nullable `users.avatar` column.
- User edit uploads validated as images of 2 MB or less, with a 4096 by 4096 pixel limit.
- JPEG, PNG, GIF, and WebP input prepared as a square 256 by 256 JPEG using Intervention Image v3 and GD. Animated input uses one frame.
- Prepared copies written using `Storage::disk('public')`; only the generated relative path is saved to the database.
- Avatars on the users list, with a bundled placeholder for accounts without an available picture.
- Existing pictures and passwords retained when their edit fields are left blank.
- Previous prepared files removed after successful replacement.
- Sample seeding that preserves previously edited accounts.

## Requirements

- PHP 8.2 or newer and Composer 2.
- PHP extensions required by Laravel and Composer, plus GD, Fileinfo, Mbstring, and PDO SQLite. GD needs JPEG and PNG support; WebP support is needed for WebP input.
- Internet access during the first dependency installation.
- No Node.js build, npm install, or MySQL server is needed for this SQLite application.

The image package is `intervention/image` v3. The assignment requires an instructor-approved image package but does not name one. Confirm that Intervention Image is approved for your class before submission.

The incompatible TA6 lock file is not included because it lacks the new dependency. The first `composer install` resolves the dependencies and writes a new `composer.lock`. Commit that generated lock with your TA7 source files. Composer targets PHP 8.2 compatibility to avoid generating a lock that unnecessarily requires PHP 8.4 on Windows.

## Run on Windows

1. Extract the ZIP and open the inner `TA7` folder containing `artisan` and `composer.json`.
2. Double-click `setup-windows.bat`.
3. Wait for the dependencies, database migrations, sample data, storage link, and view compilation.
4. Open <http://127.0.0.1:8000> and keep the command window open.

If an extension is missing, run `php --ini` to find the active `php.ini`. Enable the named extension there, reopen the terminal, and run setup again. For GD this is normally `extension=gd`; for SQLite it is `extension=pdo_sqlite`. Use `php -m` to confirm the active extensions.

## Run manually on Windows macOS or Linux

From the folder containing `artisan`:

```bash
composer install
php scripts/setup.php
php artisan serve --host=127.0.0.1 --port=8000
```

The setup script creates `.env` only when absent, preserves an existing application key and SQLite file, runs pending migrations, inserts missing sample records, links public storage, and compiles the views. To clear cached configuration before local setup, use `php artisan config:clear`.

If Windows cannot create the storage link, enable Windows Developer Mode or run `php artisan storage:link --force` from an administrator terminal, then rerun setup.

If PHP rejects an oversized upload before Laravel can inspect it, the form shows an upload error. The Docker image allows larger HTTP uploads so Laravel can show the specific 2 MB validation message. For equivalent local behavior, set `upload_max_filesize=8M` and `post_max_size=12M` in the active PHP configuration and restart the development server.

## Continue with an existing TA6 database

The attached TA6 source ZIP contains migrations and sample data, rather than a database with your later local edits. A new setup creates the same five sample customers and five sample users.

To retain later records from your own working copy, copy its `.env` and `database/database.sqlite` into this project before setup. If your `.env` sets `DB_DATABASE` to an absolute path, update that path to the database copy you intend to use. Run `composer install`, followed by `php scripts/setup.php`. Pending migrations add the avatar column without recreating the customer or user tables. Existing names and account codes are retained.

TA6 uses a `name` database column. TA7 forms submit `full_name`; the controllers map this input into `name`, so a duplicate full-name column or destructive schema change is unnecessary. Newly created account codes use a `C-` or `U-` prefix and a random suffix. Old codes such as `C-001` remain unchanged.

Customers require full name and email. The existing schema also makes email unique. Phone and address are optional in the form and are stored as empty strings when cleared. User creation also requests an email and password because the original users table requires them. Passwords are hashed. Editing a user allows an unchanged username or email and an optional new password.

## Routes

| Method | URL | Purpose |
| --- | --- | --- |
| GET | `/` | Home with live account totals |
| GET | `/about` | Application description |
| GET | `/customers` | Customer listing |
| GET | `/customers/create` | Create customer form |
| POST | `/customers` | Validate and save customer |
| GET | `/customers/{customer}/edit` | Pre-filled customer edit form |
| PUT | `/customers/{customer}` | Validate and update customer |
| GET | `/users` | User listing with avatars |
| GET | `/users/create` | Create user form |
| POST | `/users` | Validate and save user |
| GET | `/users/{user}/edit` | Pre-filled user edit and avatar form |
| PUT | `/users/{user}` | Validate and update user and optional avatar |

## Main changed files

| Area | Files |
| --- | --- |
| HTTP routes | `routes/web.php` |
| Account workflows | `app/Http/Controllers/CustomerController.php`, `UserController.php` |
| Validation | `app/Http/Requests/CustomerRequest.php`, `UserRequest.php` |
| Image preparation and storage | `app/Services/AvatarService.php` |
| Persistence and avatar URL | `app/Models/Customer.php`, `User.php` |
| Avatar schema upgrade | `database/migrations/2026_11_13_000001_add_avatar_to_users_table.php` |
| Sample data | `database/seeders/DatabaseSeeder.php` |
| Create and edit views | `resources/views/customers/`, `resources/views/users/` |
| Error summary | `resources/views/partials/form-errors.blade.php` |
| Listing pages and layout | `resources/views/customers.blade.php`, `users.blade.php`, `layouts/app.blade.php` |
| Appearance and placeholder | `public/css/app.css`, `public/images/avatar-placeholder.svg` |
| Local setup | `setup-windows.bat`, `scripts/setup.php` |
| Docker hosting | `Dockerfile`, `docker/start.sh`, `docker/php.ini`, `render.yaml` |
| Integration checks | `tests/run.php` |

## Verification

After setup, run:

```bash
composer test
```

Alternatively, run `php tests/run.php`. The runner uses Laravel's real HTTP kernel, an in-memory SQLite database, and a temporary upload directory. It checks page responses, required fields, invalid email, duplicate usernames, old input redisplay, creation and updates, pre-filled forms, password hashing and preservation, invalid and oversized uploads, thumbnail dimensions, replacement cleanup, placeholder URLs, and seeding without overwriting edits. It refuses to run against a normal application database. Clear cached configuration first if requested.

Runtime verification was not performed in the preparation workspace because PHP and Composer were unavailable. Run the supplied checks and the browser checklist below on your computer before submission.

Browser checklist:

1. Open Home, About, Customers, and Users.
2. Submit an empty customer form and a customer with an invalid email; confirm clear errors and preserved input.
3. Save a valid customer, then edit it; confirm the listing and Home total update.
4. Submit a duplicate username and confirm it is rejected.
5. Create a valid user, then edit it without changing its username or email.
6. Upload an image of 2 MB or less from Edit User; confirm the square picture appears in the users list.
7. Try a text file and an image above 2 MB; confirm rejection and retention of the current picture.
8. Edit the user without selecting a picture or entering a password; confirm both are retained.
9. Replace the picture and confirm the listing shows the replacement.
10. Repeat the main create, edit, and upload flows on the hosted URL.

## GitHub submission

Create or use your intended TA7 repository. Put the contents of the inner `TA7` folder at the repository root so `artisan`, `composer.json`, and `Dockerfile` are directly visible. Include the generated `composer.lock`, all migrations, and the seeder. `.gitignore` excludes local credentials, installed dependencies, the local SQLite database, generated caches, and uploaded avatar files. The migration and seeder files satisfy the requested database source submission.

TA7 repository URL: **Add your actual TA7 repository URL here after uploading.**

TA7 live application URL: **Add your actual TA7 hosted URL here after deployment.**

Original TA6 repository: <https://github.com/mrcs098/TA6>. Its previous live deployment is a TA6 reference until you choose to deploy this updated source. This package has not been pushed or deployed on your behalf.

## Render hosting

The Dockerfile includes the PHP image extensions and dependency installation. The startup script runs migrations, inserts missing samples, creates the public storage link, and compiles Laravel caches.

1. Push the TA7 raw source files to your repository.
2. In Render create a Web Service connected to that repository, or select the service you intend to update.
3. Select Docker, keep the Dockerfile path as `./Dockerfile`, and leave Root Directory empty when the project is at the repository root.
4. Set `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_DRIVER=file`, and `CACHE_STORE=file` if not already supplied by the included configuration.
5. Generate an application key locally with `php artisan key:generate --show` and put that exact value in Render's `APP_KEY` environment variable.
6. Deploy and test the generated HTTPS address. `RENDER_EXTERNAL_URL` is used automatically for avatar URLs unless you explicitly set `APP_URL`. Set `APP_URL` to your public HTTPS address when using a custom domain.
7. Fill in the real TA7 URLs above and submit both the repository and hosted links.

Render Free has an ephemeral filesystem. SQLite changes and uploaded avatars are lost when that service restarts or redeploys; they are not permanent storage. To retain both, use a service with a persistent disk mounted at `/var/data` and set `APP_DATA_DIR=/var/data`. The included script then stores the database and prepared avatars under that mount. The free blueprint does not provision a paid disk.

This activity remains an account-management demonstration. Authentication, account deletion, and sales transactions are outside its implemented scope.

## References

- Laravel validation: <https://laravel.com/docs/12.x/validation>
- Laravel public file storage: <https://laravel.com/docs/12.x/filesystem>
- Intervention Image v3: <https://image.intervention.io/v3>
- Render persistent disks: <https://render.com/docs/disks>
