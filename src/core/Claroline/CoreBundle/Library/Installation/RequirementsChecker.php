<?php

namespace Claroline\CoreBundle\Library\Installation;

class RequirementsChecker
{
    private $kernelRootDir;

    public function __construct($kernelRootDir)
    {
        $this->kernelRootDir = $kernelRootDir;
    }

    public function check()
    {
        $ds = DIRECTORY_SEPARATOR;
        $valid = $warning = $errors = array();
        $parametersPath = "{$this->kernelRootDir}{$ds}config{$ds}local{$ds}parameters.yml";
        $localPath = "{$this->kernelRootDir}{$ds}config{$ds}local";
        $filePath = "{$this->kernelRootDir}{$ds}..{$ds}files";
        $testPath = "{$this->kernelRootDir}{$ds}..{$ds}test";
        $themePath = "{$this->kernelRootDir}{$ds}..{$ds}src{$ds}core{$ds}Claroline{$ds}"
            . "CoreBundle{$ds}Resources{$ds}public{$ds}css{$ds}themes";

        //Extension verification.
        (extension_loaded('gd')) ?
            $valid[] = 'The php gd extension is loaded.':
            $warning[] = 'The php gd extension is missing.';
        (extension_loaded('ffmpeg')) ?
            $valid[] = 'The php ffmpeg extension is loaded.':
            $warning[] = 'The php ffmpeg extension is missing.';
        (extension_loaded('fileinfo')) ?
            $valid[] = 'The php fileinfo extension is loaded.':
            $errors[] = 'The php fileinfo extension is missing.';
        (extension_loaded('sqlite3')||extension_loaded('pdo_sqlite')) ?
            $valid[] = 'The extension SQLite3 or PDO_SQLite is loaded.':
            $errors[] = 'You need either the php SQLite3 or PDO_SQLite extension.';
        (file_exists($parametersPath)) ?
            $valid[] = "The {$parametersPath} file exists.":
            $errors[] = "The {$parametersPath} is missing.";
        (is_writable($localPath)) ?
            $valid[] = "The {$localPath} folder is writable":
            $errors[] = "The {$localPath} folder is not writable";
        (is_writable($filePath)) ?
            $valid[] = "The {$filePath} folder is writable":
            $errors[] = "The {$filePath} folder is not writable";
        (is_writable($testPath)) ?
            $valid[] = "The {$testPath} folder is writable":
            $errors[] = "The {$testPath} folder is not writable";
        (is_writable($themePath)) ?
            $valid[] = "The {$themePath} folder is writable":
            $errors[] = "The {$themePath} folder is not writable";
        $errors[] = 'stop';
        $requirements['errors'] = $errors;
        $requirements['warning'] = $warning;
        $requirements['valid'] = $valid;

        return $requirements;
    }
}

