<?php

namespace UJM\ExoBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Claroline\KernelBundle\Bundle\ConfigurationProviderInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use UJM\ExoBundle\Installation\AdditionalInstaller;

class UJMExoBundle extends PluginBundle implements ConfigurationProviderInterface
{
    public function getContainerExtension()
    {
        return new DependencyInjection\UJMExoExtension();
    }

    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config->addRoutingResource(__DIR__.'/Resources/config/routing.yml', null, 'exercise');
    }

    public function getRequiredFixturesDirectory($environment)
    {
        return 'DataFixtures';
    }

    public function suggestConfigurationFor(Bundle $bundle, $environment)
    {
        $emptyConfigs = [
            'Innova\AngularJSBundle\InnovaAngularJSBundle',
            'Innova\AngularUIBootstrapBundle\InnovaAngularUIBootstrapBundle',
            'Innova\AngularUITranslationBundle\InnovaAngularUITranslationBundle',
            'Innova\AngularUIResourcePickerBundle\InnovaAngularUIResourcePickerBundle',
            'Innova\AngularUITinyMCEBundle\InnovaAngularUITinyMCEBundle',
            'Innova\AngularUIPageslideBundle\InnovaAngularUIPageslideBundle',
            'Innova\AngularUISortableBundle\AngularUISortableBundle',
        ];

        if (in_array(get_class($bundle), $emptyConfigs)) {
            return new ConfigurationBuilder();
        }

        return false;
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }
}
