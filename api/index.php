<?php

$storage = getenv('APP_STORAGE_PATH') ?: '/tmp/storage';

$directories = [
    $storage.'/app/backups',
    $storage.'/app/private',
    $storage.'/app/public',
    $storage.'/framework/cache/data',
    $storage.'/framework/sessions',
    $storage.'/framework/views',
    $storage.'/logs',
];

foreach ($directories as $directory) {
    if (! is_dir($directory)) {
        @mkdir($directory, 0777, true);
    }
}

require __DIR__.'/../public/index.php';
