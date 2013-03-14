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
        (file_exists($parametersPath)) ?
            $valid[] = "The {$parametersPath} file exists.":
            $errors[] = "The {$parametersPath} is missing.";
            
           
        $requirements['errors'] = $errors;
        $requirements['warning'] = $warning;
        $requirements['valid'] = $valid;

        return $requirements;
    }
}
