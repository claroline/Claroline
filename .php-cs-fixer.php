<?php

$finder = PhpCsFixer\Finder::create()->in(__DIR__);

$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        '@PSR12' => true,
        '@Symfony' => true,
        'ordered_imports' => ['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'alpha'],
        'array_syntax' => ['syntax' => 'short'],
        'nullable_type_declaration_for_default_null_value' => false, // this is deprecated and will be treated as true
        'trailing_comma_in_multiline' => ['elements' => ['array_destructuring', 'arrays', 'match']], // excludes func arguments and parameters
    ])
    ->setFinder($finder);
