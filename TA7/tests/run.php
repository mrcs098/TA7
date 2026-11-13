<?php

// Integration checks using Laravel's real HTTP kernel, without extra test packages.
// The database is SQLite :memory: and uploads go to a temporary directory.
use App\Models\Customer;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

chdir(dirname(__DIR__));
if (! is_file('vendor/autoload.php')) {
    fwrite(STDERR, "Run composer install before running the checks.\n");
    exit(1);
}
if (is_file('bootstrap/cache/config.php')) {
    fwrite(STDERR, "Run php artisan config:clear before running these checks.\n");
    exit(1);
}

foreach ([
    'APP_ENV' => 'testing',
    'APP_DEBUG' => 'false',
    'APP_KEY' => 'base64:'.base64_encode(random_bytes(32)),
    'APP_URL' => 'http://localhost',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => ':memory:',
    'SESSION_DRIVER' => 'array',
    'CACHE_STORE' => 'array',
    'LOG_CHANNEL' => 'stderr',
] as $name => $value) {
    putenv($name.'='.$value);
    $_ENV[$name] = $_SERVER[$name] = $value;
}

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(ConsoleKernel::class)->bootstrap();

if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== ':memory:') {
    fwrite(STDERR, "Stopped: the checks require an in-memory database.\n");
    exit(1);
}

$temporary = sys_get_temp_dir().'/counter-pos-ta7-'.bin2hex(random_bytes(8));
mkdir($temporary.'/public', 0775, true);
config([
    'filesystems.disks.public.root' => $temporary.'/public',
    'filesystems.disks.public.url' => 'http://localhost/storage',
    'hashing.bcrypt.rounds' => 4,
]);
Storage::forgetDisk('public');
$kernel = $app->make(HttpKernel::class);
$checks = 0;

function check(bool $condition, string $message): void
{
    global $checks;
    if (! $condition) {
        throw new RuntimeException($message);
    }
    $checks++;
    echo 'PASS '.$message.PHP_EOL;
}

function send(string $method, string $path, array $data = [], array $files = [], string $referer = '/'): Response
{
    global $kernel;
    // Keep a browser-like session across requests so old() and error bags can be checked.
    $cookies = [];
    if (Session::isStarted()) {
        $cookies[config('session.cookie')] = Session::getId();
    }
    $request = Request::create('http://localhost'.$path, $method, $data, $cookies, $files, [
        'HTTP_ACCEPT' => 'text/html',
        'HTTP_REFERER' => 'http://localhost'.$referer,
    ]);
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);

    return $response;
}

function hasError(string $field): bool
{
    $errors = Session::get('errors');

    return $errors && $errors->getBag('default')->has($field);
}

function picture(string $filename): UploadedFile
{
    global $temporary;
    $path = $temporary.'/'.$filename;
    $image = imagecreatetruecolor(640, 480);
    imagefill($image, 0, 0, imagecolorallocate($image, 35, 91, 69));
    imagepng($image, $path);
    imagedestroy($image);

    return new UploadedFile($path, $filename, 'image/png', UPLOAD_ERR_OK, true);
}

function removeTemporaryDirectory(string $directory): void
{
    if (! is_dir($directory)) {
        return;
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $file) {
        if ($file->isDir()) {
            rmdir($file->getPathname());
        } else {
            unlink($file->getPathname());
        }
    }
    rmdir($directory);
}

$exitCode = 0;
try {
    check(Artisan::call('migrate', ['--force' => true]) === 0, 'All migrations run on an empty SQLite database');
    check(Artisan::call('db:seed', ['--force' => true]) === 0, 'The sample data can be seeded');
    check(Customer::count() === 5 && User::count() === 5, 'The five TA6 customers and five users are retained');
    check(\Illuminate\Support\Facades\Schema::hasColumn('users', 'avatar'), 'The avatar migration adds the nullable column');

    foreach (['/', '/about', '/customers', '/users', '/customers/create', '/users/create', '/customers/1/edit', '/users/1/edit'] as $path) {
        check(send('GET', $path)->getStatusCode() === 200, 'Page renders: '.$path);
    }
    check(send('GET', '/users/999999/edit')->getStatusCode() === 404, 'Missing records return 404');

    $response = send('POST', '/customers', ['full_name' => 'Keep This Name', 'email' => 'invalid'], [], '/customers/create');
    check($response->getStatusCode() === 302 && Customer::count() === 5, 'An invalid email does not insert a customer');
    check(hasError('email'), 'Email validation produces a clear field error');
    check(Session::getOldInput('full_name') === 'Keep This Name', 'Validation flashes the entered full name for old()');
    $redisplayed = send('GET', '/customers/create')->getContent();
    check(str_contains($redisplayed, 'value="Keep This Name"') && str_contains($redisplayed, 'aria-invalid="true"'), 'The form redisplays old input and validation feedback');

    send('POST', '/customers', ['full_name' => '   ', 'email' => 'blank@example.com'], [], '/customers/create');
    check(hasError('full_name') && Customer::count() === 5, 'A blank customer name is rejected');

    $customerData = ['full_name' => 'New Customer', 'email' => 'new.customer@example.com', 'phone' => '09123456789', 'address' => 'Quezon City', 'status' => 'Active'];
    check(send('POST', '/customers', $customerData)->getStatusCode() === 302, 'A valid customer form redirects after saving');
    $customer = Customer::where('email', $customerData['email'])->firstOrFail();
    check($customer->name === 'New Customer' && (bool) $customer->account_code, 'full_name maps to the TA6 name column and a code is generated');
    check(str_contains(send('GET', '/customers/'.$customer->id.'/edit')->getContent(), 'value="New Customer"'), 'Customer edit fields are pre-filled');
    $customerData['full_name'] = 'Updated Customer';
    send('PUT', '/customers/'.$customer->id, $customerData);
    check($customer->fresh()->name === 'Updated Customer', 'Customer edits save while allowing the current email');
    send('POST', '/customers', $customerData, [], '/customers/create');
    check(hasError('email') && Customer::count() === 6, 'Duplicate customer emails are rejected before the unique database constraint');

    $userData = ['full_name' => 'New Team Member', 'username' => 'new.member', 'email' => 'new.member@example.com', 'password' => 'Example-pass-123', 'password_confirmation' => 'Example-pass-123', 'role' => 'Cashier', 'status' => 'Active'];
    check(send('POST', '/users', $userData)->getStatusCode() === 302, 'A valid user form redirects after saving');
    $user = User::where('username', 'new.member')->firstOrFail();
    check(Hash::check($userData['password'], $user->password), 'New passwords are hashed');
    check($user->avatar === null && str_contains($user->avatar_url, 'avatar-placeholder.svg'), 'A user without a picture uses the placeholder image');
    $userForm = send('GET', '/users/'.$user->id.'/edit')->getContent();
    check(str_contains($userForm, 'multipart/form-data') && str_contains($userForm, 'value="new.member"'), 'User edit supports file upload and pre-fills the username');

    $duplicate = $userData;
    $duplicate['email'] = 'different@example.com';
    send('POST', '/users', $duplicate, [], '/users/create');
    check(hasError('username') && User::count() === 6, 'Duplicate usernames are rejected');
    $missing = $userData;
    $missing['username'] = '';
    $missing['full_name'] = '';
    send('POST', '/users', $missing, [], '/users/create');
    check(hasError('username') && hasError('full_name'), 'Both required user name fields are validated');

    $update = $userData;
    $update['full_name'] = 'Updated Team Member';
    $update['password'] = $update['password_confirmation'] = '';
    $originalHash = $user->password;
    send('PUT', '/users/'.$user->id, $update);
    check($user->fresh()->name === 'Updated Team Member', 'User edits allow unchanged unique values');
    check($user->fresh()->password === $originalHash, 'Leaving the password blank keeps its existing hash');

    $invalidPath = $temporary.'/not-an-image.txt';
    file_put_contents($invalidPath, 'This is not an image.');
    $invalid = new UploadedFile($invalidPath, 'fake.jpg', 'image/jpeg', UPLOAD_ERR_OK, true);
    send('PUT', '/users/'.$user->id, $update, ['avatar' => $invalid], '/users/'.$user->id.'/edit');
    check(hasError('avatar') && $user->fresh()->avatar === null, 'A text file disguised as a picture is rejected');

    $oversized = picture('oversized.png');
    file_put_contents($oversized->getPathname(), str_repeat('x', 2048 * 1024), FILE_APPEND);
    clearstatcache();
    send('PUT', '/users/'.$user->id, $update, ['avatar' => $oversized], '/users/'.$user->id.'/edit');
    check(hasError('avatar') && $user->fresh()->avatar === null, 'An image larger than 2 MB is rejected');

    $response = send('PUT', '/users/'.$user->id, $update, ['avatar' => picture('first.png')]);
    $user = $user->fresh();
    check($response->getStatusCode() === 302 && is_string($user->avatar), 'A valid avatar upload saves successfully');
    check((bool) preg_match('/\Aavatars\/[a-f0-9-]+\.jpg\z/', $user->avatar), 'The database holds only the generated relative JPEG path');
    check(Storage::disk('public')->exists($user->avatar), 'The prepared avatar is stored on the public disk');
    $size = getimagesize(Storage::disk('public')->path($user->avatar));
    check($size[0] === 256 && $size[1] === 256 && $size['mime'] === 'image/jpeg', 'The saved copy is a 256 by 256 JPEG thumbnail');
    $firstAvatar = $user->avatar;

    send('PUT', '/users/'.$user->id, $update);
    check($user->fresh()->avatar === $firstAvatar, 'Saving without a new file keeps the existing avatar');
    send('PUT', '/users/'.$user->id, $update, ['avatar' => picture('second.png')]);
    $user = $user->fresh();
    check($user->avatar !== $firstAvatar && ! Storage::disk('public')->exists($firstAvatar), 'Replacing an avatar removes the previous prepared file');
    check(str_contains(send('GET', '/users')->getContent(), $user->avatar), 'The users list displays the prepared avatar URL');

    $seeded = Customer::where('account_code', 'C-001')->firstOrFail();
    $seeded->update(['name' => 'Keep My Edited Sample']);
    Artisan::call('db:seed', ['--force' => true]);
    check($seeded->fresh()->name === 'Keep My Edited Sample', 'Re-seeding preserves edited TA6 sample records');
    check(Customer::count() === 6 && User::count() === 6, 'Re-seeding does not duplicate records');
    echo PHP_EOL.$checks." checks passed. The real application database was not used.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, 'FAIL '.$exception->getMessage().PHP_EOL);
    $exitCode = 1;
} finally {
    DB::disconnect();
    removeTemporaryDirectory($temporary);
}
exit($exitCode);
