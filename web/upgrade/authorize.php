<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$maintenanceMode = file_exists(__DIR__ . '/../../app/config/.update');
$authorized = false;

if (file_exists($file = __DIR__ . '/../../app/config/ips')) {
    $authorizedIps = file($file, FILE_IGNORE_NEW_LINES);
    $authorized = in_array($_SERVER['REMOTE_ADDR'], $authorizedIps);
}

if (!$authorized) {
    $url = $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '/../../app.php';
    header("Location: http://{$url}");
}