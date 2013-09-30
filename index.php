<?php

require_once __DIR__ . '/../../autoload.php';

session_start();
$container = new Claroline\WebInstaller\Container(__DIR__, $_SERVER['SCRIPT_NAME']);
$router = new Claroline\WebInstaller\Router($container);
$container->getTranslator()->setLanguage(isset($_SESSION['language']) ? $_SESSION['language'] : 'en');
$path = isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] !== '/' ? rtrim($_SERVER['PATH_INFO'], '/') : '/';
$router->dispatch($path, $_SERVER['REQUEST_METHOD']);
