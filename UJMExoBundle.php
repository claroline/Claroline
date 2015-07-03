<?php

namespace UJM\ExoBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;

class UJMExoBundle extends PluginBundle {

    public function getContainerExtension() {
        return new DependencyInjection\UJMExoExtension();
    }

    public function getConfiguration($environment) {
        $config = new ConfigurationBuilder();

        return $config->addRoutingResource(__DIR__ . '/Resources/config/routing.yml', null, 'exercise');
    }

    public function getRequiredFixturesDirectory($environment) {
        return 'DataFixtures';
    }

    public function suggestConfigurationFor(Bundle $bundle, $environment) {
        $bundleClass = get_class($bundle);
        $config = new ConfigurationBuilder();
        $emptyConfigs = array(
            'Innova\AngularJSBundle\InnovaAngularJSBundle',
            'Innova\AngularUIBootstrapBundle\InnovaAngularUIBootstrapBundle',
            'Innova\AngularUITranslationBundle\InnovaAngularUITranslationBundle',
            'Innova\AngularUIResourcePickerBundle\InnovaAngularUIResourcePickerBundle',
            'Innova\AngularUITinyMCEBundle\InnovaAngularUITinyMCEBundle',
            'Innova\AngularUIPageslideBundle\InnovaAngularUIPageslideBundle',
            'Innova\AngularUISortableBundle\AngularUISortableBundle',
        );
        if (in_array($bundleClass, $emptyConfigs)) {
            return $config;
        }
        return false;
    }

}
