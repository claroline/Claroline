<?php

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        'ordered_imports' => true,
        'array_syntax' => ['syntax' => 'short'],
    ]);
