<?php

set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

exec('ln -s '.__DIR__.'/../files/data '.__DIR__.'/../web');
