<?php

namespace Innova\PathBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Claroline\KernelBundle\Bundle\AutoConfigurableInterface;
use Claroline\KernelBundle\Bundle\ConfigurationProviderInterface;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;

/**
 * Bundle class.
 */
class InnovaPathBundle extends PluginBundle implements AutoConfigurableInterface, ConfigurationProviderInterface
{
	public function supports($environment)
    {
        return true;
    }

    public function getConfiguration($environment)
    {
        return new ConfigurationBuilder();
    }

     public function getRequiredFixturesDirectory($environment)
    {
        return 'DataFixtures/Required';
    } 

    public function suggestConfigurationFor(Bundle $bundle, $environment)
    {
        $config = new ConfigurationBuilder();
        $bundles = array(
            'Innova\AngularJSBundle' => 'angularjs'
        );

        if (in_array($bundleClass = get_class($bundle), array_keys($bundles))) {
            if (in_array($environment, array('prod', 'dev', 'test'))) {
                return $config->addContainerResource(
                    __DIR__ . "/Resources/config/suggested/{$bundles[$bundleClass]}_{$environment}.yml"
                );
            }
        }

        // if ($bundle instanceof \Symfony\Bundle\TwigBundle\TwigBundle) {
        //     return $config->addContainerResource(
        //         __DIR__ . "/Resources/config/suggested/twig.yml"
        //     );
        // }
    } 
    
}