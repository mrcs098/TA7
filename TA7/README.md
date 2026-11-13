[README.md](https://github.com/user-attachments/files/34816187/README.md)
# Counter POS TFA7

Counter POS is a Laravel application for **IT0049 Web System Technologies — Technical Formative Assessment 7: Making It Editable in Laravel**. It extends the TFA6 application with customer and user account creation, editing, form validation, and prepared avatar uploads while retaining the original interface and sample records.

## Project links

| Resource | URL |
|---|---|
| GitHub repository | [github.com/mrcs098/TA7](https://github.com/mrcs098/TA7) |
| Live application | [ta7-bo21.onrender.com](https://ta7-bo21.onrender.com) |

The application source is inside the repository's **`TA7/` directory**. Paths in the implementation sections below are relative to that application directory.

## Features

- Home page with customer and user totals retrieved from the database.
- About page and separate customer and user account listings.
- Create and edit forms with pre-filled values for existing records.
- Server-side validation with field errors and previous input redisplayed through `old()`.
- Unique usernames and email validation consistent with the existing database schema.
- CSRF protection and PUT method fields for updates.
- User avatar uploads with image validation, thumbnail preparation, public storage, and a placeholder image.
- Existing passwords and avatars retained when their edit fields are left blank.
- Sample seeding that inserts missing records without overwriting edited accounts.

## Technology stack

| Component | Technology |
|---|---|
| Application | Laravel 12 and PHP 8.2 or newer |
| Views | Laravel Blade and CSS |
| Database | SQLite through Eloquent ORM |
| Image preparation | Intervention Image v3 with PHP GD |
| File storage | Laravel Storage public disk |
| Hosting | Render with Docker and Apache |

## Requirements

- PHP 8.2 or newer with Laravel's required extensions.
- PHP GD, Fileinfo, Mbstring, and PDO SQLite enabled. GD must support the image formats being uploaded.
- Composer 2 and internet access for dependency installation.

The project uses SQLite and does not require a separate MySQL server or a Node.js asset build. Composer generates `composer.lock` on the first installation if it is absent; include the generated lock file when committing resolved dependencies.

## Local setup

Clone the repository and enter the application directory:

```bash
git clone https://github.com/mrcs098/TA7.git
cd TA7/TA7
composer install
php scripts/setup.php
php artisan serve --host=127.0.0.1 --port=8000
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000) in a browser.

The setup script creates `.env` from `.env.example` when needed, generates a missing application key, creates a missing SQLite database, runs migrations and seeders, creates the public storage link, and compiles the Blade views. Existing application keys and database files are retained.

On Windows, `setup-windows.bat` in the application directory provides dependency checks, installation, database setup, and server startup. Keep its command window open while using the application. Use `php --ini` to locate the active PHP configuration and `php -m` to check enabled extensions.

If configuration has been cached before running local setup, clear it first:

```bash
php artisan config:clear
```

If Windows cannot create the public storage link, enable Developer Mode or run `php artisan storage:link --force` from an administrator terminal, then rerun setup.

## Database and validation

Migrations create the customer and user tables and add the nullable `avatar` column to `users`. The seeder supplies five fictional customers and five fictional users. Running it again preserves existing account details.

TFA6 stores full names in a `name` column. The TFA7 forms submit `full_name`, which the controllers map to that existing column. New accounts receive generated codes beginning with `C-` or `U-`; existing codes are retained.

| Form | Main validation rules |
|---|---|
| Customer | Full name required; email required, valid, and unique. Phone and address are optional. |
| Create user | Full name and unique username required. The existing schema also requires a valid unique email and a password. Password confirmation must match; passwords must have at least 8 characters. |
| Edit user | The current username and email are allowed when unchanged. A replacement password is optional. |
| Avatar | Image required when a file is selected; maximum 2 MB and 4096 × 4096 pixels. Supported input formats are JPEG, PNG, GIF, and WebP. |

Passwords are hashed before storage and are not repopulated after validation errors. Text fields and selections use `old()` to retain submitted values; file inputs must be selected again.

### Reusing a TFA6 database

Before setup, copy the existing TFA6 `.env` and SQLite database into the new application's directory. Update `DB_DATABASE` if it contains an absolute path pointing to the previous copy. Running `php scripts/setup.php` then applies pending migrations, including the avatar column, without recreating the account tables.

## Avatar workflow

1. Open an existing user's **Edit** page and select a picture.
2. Validate its image type, size, and dimensions.
3. Use Intervention Image with GD to prepare a square **256 × 256 JPEG**. Animated input uses one frame.
4. Write the prepared image with `Storage::disk('public')` under a generated path such as `avatars/<uuid>.jpg`.
5. Save only that relative path in the user's `avatar` column and display the prepared image on the User Accounts listing.

A bundled placeholder is displayed when no available avatar exists. Saving without a new file retains the current picture. A successful replacement removes the previous prepared file; a database save failure triggers cleanup of the newly prepared file.

The image package used for this activity must meet the instructor's approval requirement.

## Routes

| Method | URL | Purpose |
|---|---|---|
| GET | `/` | Home and account totals |
| GET | `/about` | Application information |
| GET | `/customers` | Customer listing |
| GET | `/customers/create` | Create customer form |
| POST | `/customers` | Validate and save a customer |
| GET | `/customers/{customer}/edit` | Pre-filled customer edit form |
| PUT | `/customers/{customer}` | Validate and update a customer |
| GET | `/users` | User listing with avatars |
| GET | `/users/create` | Create user form |
| POST | `/users` | Validate and save a user |
| GET | `/users/{user}/edit` | Pre-filled user edit and avatar form |
| PUT | `/users/{user}` | Validate and update a user and optional avatar |

## Code organization

| Responsibility | Location |
|---|---|
| HTTP routes | `routes/web.php` |
| Account workflows | `app/Http/Controllers/CustomerController.php` and `UserController.php` |
| Request validation | `app/Http/Requests/CustomerRequest.php` and `UserRequest.php` |
| Eloquent models | `app/Models/Customer.php` and `User.php` |
| Image preparation and storage | `app/Services/AvatarService.php` |
| Database structure and sample records | `database/migrations/` and `database/seeders/DatabaseSeeder.php` |
| Blade pages and forms | `resources/views/` |
| Styles and placeholder image | `public/css/app.css` and `public/images/avatar-placeholder.svg` |
| Local setup | `scripts/setup.php` and `setup-windows.bat` |
| Deployment | `Dockerfile`, `docker/`, and `render.yaml` |
| Integration checks | `tests/run.php` |

## Testing

After local setup, run:

```bash
composer test
```

Alternatively, run `php tests/run.php`. The integration runner uses Laravel's HTTP kernel, an in-memory SQLite database, and a temporary upload directory. It checks account creation and updates, validation and old input, pre-filled forms, password hashing, invalid and oversized uploads, thumbnail dimensions, avatar replacement, placeholders, and seeding behavior. It refuses to run against a normal application database. Clear cached configuration first if requested.

## Render deployment

The deployed repository keeps the application in `TA7/`. Its Render configuration is:

| Setting | Value |
|---|---|
| Repository | `mrcs098/TA7` |
| Branch | `main` |
| Language or Runtime | Docker |
| Root Directory | Empty |
| Dockerfile Path | `TA7/Dockerfile` |
| Docker Build Context Directory | `TA7` |
| Compute plan | Free |
| Live URL | [https://ta7-bo21.onrender.com](https://ta7-bo21.onrender.com) |

The Docker image installs dependencies and PHP image extensions. Its startup script runs migrations, seeds missing sample accounts, links public storage, caches configuration and views, and starts Apache. Production environment values are supplied by the Docker configuration. The public application URL is taken from `RENDER_EXTERNAL_URL` unless `APP_URL` is explicitly configured.

For a stable encryption key across deployments, set `APP_KEY` in Render using a value generated locally with `php artisan key:generate --show`. When unset, the startup script generates a key for that container.

Ensure these files are included in the GitHub application directory:

| File | Purpose |
|---|---|
| `TA7/.env.example` | Environment template used during setup and container startup |
| `TA7/public/.htaccess` | Apache rewrite rules for Laravel routes |
| `TA7/.gitignore` | Excludes local credentials, dependencies, databases, and generated files from Git commits |
| `TA7/.dockerignore` | Controls files included in the Docker build context |

When the application is moved to the repository root, update the Dockerfile path to `./Dockerfile` and the build context to `.`.

### Hosted data storage

Render Free uses an ephemeral filesystem. SQLite changes and uploaded avatars are lost when the service spins down, restarts, or redeploys. Local development keeps these files on the developer's computer.

For retained hosted data, the included startup script supports a persistent disk mounted at `/var/data` with `APP_DATA_DIR=/var/data`. This stores both the SQLite database and prepared avatars under that mount. A persistent disk requires a supported paid service; the included free configuration does not provision one.

### Deployment troubleshooting

| Symptom | Check |
|---|---|
| Docker cannot find `docker/apache.conf` or `docker/php.ini` | Set Docker Build Context Directory to `TA7` for the current repository layout. |
| Startup cannot find `.env.example` | Confirm `TA7/.env.example` is committed to `main`. |
| Home opens but other pages return an Apache 404 | Confirm `TA7/public/.htaccess` is committed and the deployed image enables Apache rewrite rules. |

## Submission

Submit the [GitHub repository](https://github.com/mrcs098/TA7) and the [hosted application](https://ta7-bo21.onrender.com). The repository includes the raw Laravel source, migration and seeder files, setup instructions, and verification steps.

## References

- [Laravel validation](https://laravel.com/docs/12.x/validation)
- [Laravel file storage](https://laravel.com/docs/12.x/filesystem)
- [Intervention Image v3](https://image.intervention.io/v3)
- [Render Docker deployment](https://render.com/docs/docker)
- [Render monorepo settings](https://render.com/docs/monorepo-support)
- [Render free service limits](https://render.com/docs/free)
