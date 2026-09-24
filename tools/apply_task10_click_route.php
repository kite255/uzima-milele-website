<?php

$path = dirname(__DIR__).'/routes/web.php';
$contents = file_get_contents($path);

if ($contents === false) {
    throw new RuntimeException('Unable to read routes/web.php');
}

$controllerImport = "use App\\Http\\Controllers\\EmailCampaignClickController;\n";

if (! str_contains($contents, $controllerImport)) {
    $anchor = "use App\\Http\\Controllers\\DevotionController;\n";

    if (! str_contains($contents, $anchor)) {
        throw new RuntimeException('Controller import anchor not found.');
    }

    $contents = str_replace(
        $anchor,
        $anchor.$controllerImport,
        $contents
    );
}

$route = <<<'PHP'

/*
|--------------------------------------------------------------------------
| Email Campaign Click Tracking
|--------------------------------------------------------------------------
*/
Route::get(
    '/email/click/{token}',
    EmailCampaignClickController::class
)->name('email-campaigns.click');
PHP;

if (! str_contains($contents, "/email/click/{token}")) {
    $anchor = <<<'PHP'
Route::get(
    '/email/open/{token}.gif',
    [EmailCampaignTrackingController::class, 'open']
)->name('email-campaigns.open');
PHP;

    if (! str_contains($contents, $anchor)) {
        throw new RuntimeException('Open tracking route anchor not found.');
    }

    $contents = str_replace(
        $anchor,
        $anchor.$route,
        $contents
    );
}

file_put_contents($path, $contents);

echo "Task 10 click route patch applied successfully.\n";
