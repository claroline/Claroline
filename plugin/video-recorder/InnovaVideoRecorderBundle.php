<?php

namespace Innova\VideoRecorderBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;
use Innova\VideoRecorderBundle\Installation\AdditionalInstaller;

/**
 * Bundle class.
 */
class InnovaVideoRecorderBundle extends DistributionPluginBundle
{
    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }

    public function getExtraRequirements()
    {
        return [
            'libav-tools' => [
                'test' => function () {
                    $cmd = 'avconv -h';
                    exec($cmd, $output, $return);

                    return count($output) > 0 && 0 === $return;
                },
                'failure_msg' => 'libavtools_not_installed',
            ],
        ];
    }
}
