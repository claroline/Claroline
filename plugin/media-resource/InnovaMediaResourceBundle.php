<?php

namespace Innova\MediaResourceBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;

/**
 * Bundle class.
 */
class InnovaMediaResourceBundle extends DistributionPluginBundle
{
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
