<?php

require_once __DIR__.'/../app/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;

$maintenanceMode = file_exists(__DIR__.'/../app/config/.update');
$authorized = false;

if (file_exists($file = __DIR__.'/../app/config/ip_white_list.yml')) {
    $ips = Yaml::parse($file);
    $authorized = false;

    if (is_array($ips)) {
        foreach ($ips as $ip) {
            if ($ip === $_SERVER['REMOTE_ADDR']) {
                $authorized = true;
            }
        }
    }
}

if (!$maintenanceMode || $authorized) {
    $request = Request::createFromGlobals();
    $kernel = new AppKernel('prod', false);
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);
} else {
    $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
    $url = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'/../maintenance.php';
    header("Location: {$url}");
}
