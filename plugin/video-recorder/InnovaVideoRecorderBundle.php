<?php

namespace Innova\VideoRecorderBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Claroline\KernelBundle\Bundle\AutoConfigurableInterface;
use Innova\VideoRecorderBundle\Installation\AdditionalInstaller;

/**
 * Bundle class.
 */
class InnovaVideoRecorderBundle extends PluginBundle implements AutoConfigurableInterface
{
    public function supports($environment)
    {
        return true;
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }

    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config->addRoutingResource(__DIR__.'/Resources/config/routing.yml', null, 'video_recorder');
    }

    public function getExtraRequirements()
    {
        return array(
            'libav-tools' => array(
                'test' => function () {
                    $cmd = 'avconv -h';
                    exec($cmd, $output, $return);

                    return count($output) > 0 && $return === 0;
                },
                'failure_msg' => 'libavtools_not_installed',
            ),
        );
    }
}
