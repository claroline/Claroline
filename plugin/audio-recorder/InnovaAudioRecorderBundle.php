<?php

namespace Innova\AudioRecorderBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;
use Innova\AudioRecorderBundle\Installation\AdditionalInstaller;

/**
 * Bundle class.
 */
class InnovaAudioRecorderBundle extends DistributionPluginBundle
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
