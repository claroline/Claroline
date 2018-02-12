<?php

$data = json_decode(file_get_contents(__DIR__.'/npm-github-js.json', true));
$nodeModulesDir = $subfolder = __DIR__.'/../../../node_modules/';

foreach ($data as $subfolder => $url) {
    update($subfolder, $url, $nodeModulesDir);
}

/**
 * Adds a js dependencies in node modules without packages.json file.
 *
 * @param string $subfolder      - subfolder in nodemodules
 * @param string $url            - the git url
 * @param string $nodeModulesDir - the node module dir
 */
function update($subfolder, $url, $nodeModulesDir)
{
    $parts = explode('#', $url);
    $subfolder = $nodeModulesDir.$subfolder;

    //update
    if (realpath($subfolder)) {
        echo "go to {$subfolder}\n";
        chdir($subfolder);
        exec('git pull');

        if (isset($parts[1])) {
            exec('git checkout '.$parts[1]);
        }
    } else {
        //clone
        exec('git clone '.$parts[0].' '.$subfolder);

        if (isset($parts[1])) {
            chdir($subfolder);
            exec('git checkout '.$parts[1]);
        }
    }
}
