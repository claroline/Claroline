<?php

$finder = PhpCsFixer\Finder::create()->in(__DIR__);

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR12' => true,
        '@Symfony' => true,
        'ordered_imports' => ['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'alpha'],
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder);
