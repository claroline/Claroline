<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

include __DIR__ . '/authorize.php';

$logFile = $_GET['logFile'];
$file = '../../app/logs/' . $logFile;
echo @file_get_contents($file);
