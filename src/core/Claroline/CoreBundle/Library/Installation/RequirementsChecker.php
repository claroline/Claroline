<?php

namespace Claroline\CoreBundle\Library\Installation;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.installation.requirements_checker")
 */
class RequirementsChecker
{
    private $kernelRootDir;

    /**
     * @DI\InjectParams({
     *     "kernelRootDir" = @DI\Inject("%kernel.root_dir%")
     * })
     */
    public function __construct($kernelRootDir)
    {
        $this->kernelRootDir = $kernelRootDir;
    }

    public function check()
    {
        $ds = DIRECTORY_SEPARATOR;
        $valid = $warning = $errors = array();
        $parametersPath = "{$this->kernelRootDir}{$ds}config{$ds}local{$ds}parameters.yml";

        $extensions = array(
            array('name' => 'gd', 'required' => false),
            array('name' => 'ffmpeg', 'required' => false),
            array('name' => 'fileinfo', 'required' => true),
        );

        //Extension verification.
        foreach ($extensions as $extension) {
            if (extension_loaded($extension['name'])) {
                $valid[] = "The {$extension['name']} extension is loaded";
            } else {
                $extension['required'] ?
                    $errors[] = "The {$extension['name']} is missing":
                    $warning[] = "The {$extension['name']} is missing";
            }
        }

        (file_exists($parametersPath)) ?
            $valid[] = "The {$parametersPath} file exists.":
            $errors[] = "The {$parametersPath} is missing.";

        $requirements['errors'] = $errors;
        $requirements['warning'] = $warning;
        $requirements['valid'] = $valid;

        return $requirements;
    }
}
