<?php

$root = dirname(__DIR__);
$failures = [];

$expect = static function (bool $condition, string $message) use (&$failures): void {
    if (!$condition) {
        $failures[] = $message;
    }
};

$routes = file_get_contents($root . '/src/routes/artadmin.php');
$login = file_get_contents($root . '/src/Controllers/LoginController.php');
$dashboard = file_get_contents($root . '/src/Controllers/DashboardController.php');
$admins = file_get_contents($root . '/src/Controllers/AdminsController.php');
$my = file_get_contents($root . '/src/Controllers/MyController.php');
$command = file_get_contents($root . '/src/Commands/CreateUser.php');
$config = file_get_contents($root . '/src/config/artadmin.php');
$header = file_get_contents($root . '/src/resources/views/parts/header.blade.php');

$expect(strpos($routes, 'Route::post("/logout"') !== false, 'logout must be POST-only');
$expect(strpos($routes, 'Route::post("/clear_cache"') !== false, 'cache clear must be POST-only');
$expect(substr_count($routes, 'Route::delete(') === 3, 'all three destructive routes must use DELETE');
$expect(strpos($routes, 'Route::get("/logout"') === false, 'logout GET route must not exist');
$expect(strpos($routes, 'Route::get("/clear_cache"') === false, 'cache-clear GET route must not exist');
$expect(strpos($routes, 'Route::get("/roles/delete') === false, 'role-delete GET route must not exist');
$expect(strpos($routes, 'Route::get("/permissions/delete') === false, 'permission-delete GET route must not exist');
$expect(strpos($routes, 'Route::get("/admins/delete') === false, 'admin-delete GET route must not exist');
$expect(strpos($login, 'RateLimiter::tooManyAttempts') !== false, 'login must enforce an attempt limit');
$expect(strpos($login, 'RateLimiter::hit') !== false, 'failed login window must be recorded');
$expect(strpos($dashboard, 'session()->invalidate()') !== false, 'logout must invalidate the session');
$expect(strpos($dashboard, 'session()->regenerateToken()') !== false, 'logout must rotate the CSRF token');
$expect(strpos($command, 'print("password:') === false, 'CLI must not print the plaintext password');
$expect(strpos($config, "'password_min_length'=>12") !== false, 'new and changed admin passwords must default to at least 12 characters');
$expect(strpos($admins, "config('artadmin.password_min_length', 12)") !== false, 'admin create/edit must enforce the configured password minimum');
$expect(strpos($my, "config('artadmin.password_min_length', 12)") !== false, 'self-service password change must enforce the configured password minimum');
$expect(strpos($command, "config('artadmin.password_min_length', 12)") !== false, 'CLI user creation must enforce the configured password minimum');
$expect(strpos($header, '@csrf') !== false, 'state-changing menu actions must include CSRF');

if ($failures !== []) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}

fwrite(STDOUT, "artadmin security contract: ok\n");
