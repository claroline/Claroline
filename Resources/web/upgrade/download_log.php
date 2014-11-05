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
$file = '../../app/logs/' . $logFile . '.log';

header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=claroline.log");
header("Content-Type: application/zip");
header("Content-Transfer-Encoding: binary");

// read the file from disk
readfile($file);
