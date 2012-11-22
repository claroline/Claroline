<?php

/**
 * When phpunit executes a test in a dedicated php process, it receives the result
 * back in a serialized form, and unserialize it with the php 'unserialize' function.
 * If the serialized data contains references to unknown classes, which cannot be loaded,
 * this function will throw a 'PHP_Incomplete_Class error'. The problem is that there is
 * a few classes (app kernel, container, doctrine proxies, stub test classes) which are
 * not covered by the autoloader, but required 'manually' by the application at runtime.
 * If those classes are included in the test result (e.g. as part of an exception stack
 * trace or as the context of a partial/skipped test), they can be loaded by the phpunit
 * parent process and the unserialization fails. The following function is used as a
 * fallback in such case.
 *
 * @param string $class
 *
 * @throws Exception if the class cannot be required
 */
function fallback($class)
{
    switch ($class) {
        case 'AppKernel':
            require_once __DIR__ . '/AppKernel.php';
            break;
        case 'appTestDebugProjectContainer':
            require_once __DIR__ . '/cache/test/appTestDebugProjectContainer.php';
            break;
        default:
            if (strpos($class, 'Proxies\__CG__') === 0) {
                $testProxyDir = __DIR__ . '/cache/test/doctrine/orm/Proxies';
                $file = $testProxyDir . '/' . str_replace('\\', '', str_replace('Proxies\\', '', $class)) . '.php';
                file_exists($file) && require_once $file;
                break;
            }

            if (file_exists($file = __DIR__ . '/../src/core/Claroline/CoreBundle/Tests/Stub/plugin/' . str_replace('\\', '/', $class) . '.php')) {
                require_once $file;
            }
    }

    if (!class_exists($class)) {
        throw new Exception("Failed to autoload or require class '{$class}'");
    }
}

ini_set('unserialize_callback_func', 'fallback');

require_once __DIR__ . '/bootstrap.php.cache';