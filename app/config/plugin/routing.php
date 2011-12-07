<?php

use Symfony\Component\Routing\RouteCollection;

if (file_exists(__DIR__ . '/routing.yml'))
{
    return $loader->import('routing.yml');
}

return new RouteCollection();