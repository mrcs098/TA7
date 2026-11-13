<?php

// Run after composer install. Existing database files and APP_KEY are kept.
use Illuminate\Contracts\Console\Kernel;

chdir(dirname(__DIR__));

if (! is_file('vendor/autoload.php')) {
    fwrite(STDERR, "Run composer install first.\n");
    exit(1);
}

if (! is_file('.env') && ! copy('.env.example', '.env')) {
    fwrite(STDERR, "Could not create .env. Check folder permissions.\n");
    exit(1);
}

foreach (['bootstrap/cache', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs', 'storage/app/private', 'storage/app/public'] as $directory) {
    if (! is_dir($directory)) {
        mkdir($directory, 0775, true);
    }
}

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

if ($app->configurationIsCached()) {
    fwrite(STDERR, "Run php artisan config:clear before local setup, then run this command again.\n");
    exit(1);
}

$run = function (string $command, array $arguments = []) use ($kernel): void {
    $exitCode = $kernel->call($command, $arguments);
    echo $kernel->output();

    if ($exitCode !== 0) {
        throw new RuntimeException($command.' failed. See the output above.');
    }
};

try {
    if (! config('app.key')) {
        $run('key:generate', ['--force' => true]);
    }

    if (config('database.default') === 'sqlite') {
        $database = config('database.connections.sqlite.database');

        if ($database !== ':memory:' && ! is_file($database)) {
            if (! is_dir(dirname($database))) {
                mkdir(dirname($database), 0775, true);
            }
            touch($database);
        }
    }

    $run('migrate', ['--force' => true]);
    $run('db:seed', ['--force' => true]);
    $run('storage:link', ['--force' => true]);
    $run('view:cache');
    echo "\nSetup complete. Start the application with php artisan serve.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage()."\n");
    exit(1);
}
