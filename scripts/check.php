<?php

/**
 * This script ensures the system meets the minimal requirements for the
 * installation of the platform before executing composer install scripts
 * (i.e. which itself will perform more checks).
 *
 * Currently, it means:
 *
 *   - an app/config/parameters.yml file
 *   - a recent version of node and npm
 */

if (!file_exists(__DIR__ . '/../app/config/parameters.yml')) {
    abort(
        'The configuration file app/config/parameter.yml is missing '
         . '(execute "php configure.php" to build it interactively)'
    );
}

ensureVersion('node', 'node -v', '5.6');
ensureVersion('npm', 'npm -v', '3.7');

function abort($msg)
{
    fwrite(STDERR, "Pre-install check failed: {$msg}\n");
    exit(1);
}

function ensureVersion($executable, $versionCmd, $minExpected)
{
    @exec($versionCmd, $output, $code);

    if ($code === 127) {
        abort("{$executable} executable seems missing from your system");
    }

    if ($code !== 0) {
        abort("Command'{$versionCmd}' returned a non-zero code ({$code})");
    }

    $output = implode("\n", $output);
    $versionPattern = '/v?(\d+)\.(\d+)\..+/';

    if (!preg_match($versionPattern, $output, $matches)) {
        abort("Cannot parse version '{$output}' from '{$versionCmd}'");
    }

    $expected = explode('.', $minExpected);

    if ($matches[1] < $expected[0] || $matches[2] < $expected[1]) {
        abort(sprintf(
            'Expected %s >= %s, found %s.%s',
            $executable,
            $minExpected,
            $matches[1],
            $matches[2]
        ));
    }
}
