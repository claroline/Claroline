<?php

/*******************************************************************************
 * This script is a config file for PHP-CS-Fixer designed for travis builds.
 * It reads a list of targets (supposably files changed by the push/PR) from
 * a file located in the root directory and passed them to the CS config (with
 * default fixer level: Symfony).
 ******************************************************************************/

$pkgDir = realpath(__DIR__.'/../../../..');
$targetFile = "{$pkgDir}/git_diff_files.txt";

if (!file_exists($targetFile)) {
    echo "Cannot find file listing CS targets (looked for {$targetFile})\n";
    exit(1);
}

$targets = array_filter(file($targetFile), function ($line) {
    $line = trim($line);
    $length = strlen($line);

    return $length > 4 && strpos($line, '.php') === $length - 4;
});

$files = array_map(function ($filePath) use ($pkgDir) {
    return "{$pkgDir}/".trim($filePath);
}, $targets);

$finder = PhpCsFixer\Finder::create()->append($files);

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        'ordered_imports' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder);
