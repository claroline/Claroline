<?php

/**
 * This script creates a parameters.yml file (if it doesn't exist) from
 * values provided by the user or available as environment variables.
 *
 * If a "--default" option is passed, user input and environment variables
 * are ignored and the parameters are given their default values.
 *
 * The list of parameters is stored in $params, where each parameter has the
 * following attributes:
 *
 *   - name of the parameter in the parameters.yml(.dist) file
 *   - name of the corresponding environment variable, if any
 *   - default/dist value of the parameter
 */

$params = [
    ['database_host', 'DB_HOST', 'localhost'],
    ['database_name', 'DB_NAME', 'claroline'],
    ['database_user', 'DB_USER', 'root'],
    ['database_password', 'DB_PASSWORD', null],
    ['secret', 'SECRET', 'change_me']
];

$paramFile = __DIR__ . '/../app/config/parameters.yml';

set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

if (file_exists($paramFile)) {
    writeln('Nothing to configure, parameter file already exists', true);
}

$fileContent = file_get_contents("{$paramFile}.dist");
$forceDefault = in_array('--default', $argv);

writeln('Please provide a value for the following parameters:');

foreach ($params as $paramData) {
    $value = getParameter($paramData, $forceDefault);
    $value = empty($value) ? '~' : $value;
    $pattern = "/( +{$paramData[0]} *: *)([^ ]+) *\\n/";
    $replace = "\${1}{$value}\n";
    $newContent = preg_replace($pattern, $replace, $fileContent, 1);

    if ($value !== $paramData[2] && $fileContent === $newContent) {
        throw new \Exception("Cannot set param {$paramData[0]}");
    }

    $fileContent = $newContent;
}

file_put_contents($paramFile, $fileContent);

writeln('Config file app/config/parameters.yml written', true);

function getParameter(array $paramData, $forceDefault)
{
    if ($forceDefault) {
        writeln("{$paramData[0]} -> forced default value");

        return $paramData[2];
    }

    if ($value = getenv($paramData[1])) {
        writeln("{$paramData[0]} -> provided by environment");

        return $value;
    }

    $defaultText = $paramData[2] ? " ({$paramData[2]})" : '';
    echo("{$paramData[0]}{$defaultText}: ");
    $input = stream_get_line(STDIN, 1024, PHP_EOL);

    return $input ?: $paramData[2];
}

function writeln($msg, $exit = false)
{
    echo "{$msg}\n";

    if ($exit) {
        exit(0);
    }
}
