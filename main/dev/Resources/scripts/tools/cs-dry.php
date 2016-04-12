#!/usr/bin/php
<?php

require_once __DIR__.'/../tool-header.php';

$cmd = "{$binDir}/php-cs-fixer fix {$argv[1]} --dry-run --diff --config-file {$configDir}/cs.php";
system($cmd, $returnCode);
exit($returnCode === 0 ? 0 : 1);
