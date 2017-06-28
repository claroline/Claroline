<?php

namespace Innova\AudioRecorderBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;
use Claroline\KernelBundle\Bundle\AutoConfigurableInterface;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Innova\AudioRecorderBundle\Installation\AdditionalInstaller;

/**
 * Bundle class.
 */
class InnovaAudioRecorderBundle extends DistributionPluginBundle implements AutoConfigurableInterface
{
    public function supports($environment)
    {
        return true;
    }

    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config->addRoutingResource(__DIR__.'/Resources/config/routing.yml', null, 'audio_recorder');
    }

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

                    return count($output) > 0 && $return === 0;
                },
                'failure_msg' => 'libavtools_not_installed',
            ],
        ];
    }
}
