<?php

// Converts all translation files to JSON (one-shot, kept as an example).

$rootDir = realpath(__DIR__.'/../../../../../../..');

require_once "{$rootDir}/vendor/autoload.php";

$loader = new Symfony\Component\Translation\Loader\YamlFileLoader();
$dumper = new Symfony\Component\Translation\Dumper\JsonFileDumper();

$finder = new Symfony\Component\Finder\Finder();
$finder
    ->in("{$rootDir}/vendor/claroline/distribution")
    ->path('/Resources\/translations\/.*\.yml/');

foreach ($finder as $file) {
    $origin = $file->getRealPath();
    $name = $file->getFilename();
    preg_match('/(.+)\.(.+)\.yml$/', $name, $matches);
    $catalogue = $loader->load($origin, $matches[2], $matches[1]);
    $dumper->dump($catalogue, ['path' => $file->getPath()]);
    unlink($origin);
}
