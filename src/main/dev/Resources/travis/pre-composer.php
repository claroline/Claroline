<?php

/*******************************************************************
 * This script updates the main composer.json file of the project
 * with a reference to a package located in the filesystem in
 * order to test that package in the full context of the Claroline
 * platform.
 *
 * It is intended to be launched from the travis configuration
 * file immediately before the execution of composer.
 *
 * Note that in order to work properly, this script MUST be
 * executed from the root directory of the platform.
 ******************************************************************/

// convert errors to exceptions
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

if ($argc < 4) {
    echo "Expected three arguments: package_name package_path target_ref\n";
    exit(1);
}

$packageName = $argv[1];
$packagePath = $argv[2];
$targetRef = $argv[3];
$composerFile = getcwd().'/composer.json';

if (!file_exists($composerFile)) {
    echo "No composer.json found (looked for {$composerFile})\n";
    exit(1);
}

$data = json_decode(file_get_contents($composerFile));
$data->require->{$packageName} = $targetRef;

if (!isset($data->repositories)) {
    $data->repositories = [];
}

$data->repositories[] = [
    'type' => 'path',
    'url' => $packagePath,
    'options' => [
        'symlink' => false,
    ],
];

$content = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
file_put_contents($composerFile, $content);

echo "Updated composer.json with content:\n{$content}\n";
