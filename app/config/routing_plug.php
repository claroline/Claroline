<?php

use Symfony\Component\Routing\RouteCollection;

if (file_exists(__DIR__ . '/local/plugin/routing.yml'))
{
    return $loader->import('local/plugin/routing.yml');
}

return new RouteCollection();