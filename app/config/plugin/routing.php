<?php
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;


if(file_exists(__DIR__ . '/routing.yml'))
{
    return $loader->import('routing.yml');
}
return new RouteCollection();