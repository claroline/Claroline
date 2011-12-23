<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\ClassLoader\DebugUniversalClassLoader;
use Symfony\Component\HttpKernel\Debug\ErrorHandler;
use Symfony\Component\HttpKernel\Debug\ExceptionHandler;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\DoctrineBundle\DoctrineBundle(),
            new Symfony\Bundle\DoctrineMigrationsBundle\DoctrineMigrationsBundle(),
            new Symfony\Bundle\DoctrineFixturesBundle\DoctrineFixturesBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Claroline\CommonBundle\ClarolineCommonBundle(),
            new Claroline\InstallBundle\ClarolineInstallBundle(),
            new Claroline\UserBundle\ClarolineUserBundle(),
            new Claroline\WorkspaceBundle\ClarolineWorkspaceBundle(),
            new Claroline\ResourceBundle\ClarolineResourceBundle(),
            new Claroline\SecurityBundle\ClarolineSecurityBundle(),
            new Claroline\PluginBundle\ClarolinePluginBundle(),
            new Claroline\HomeBundle\ClarolineHomeBundle(),
            new Claroline\DesktopBundle\ClarolineDesktopBundle(),
            new Claroline\AdminBundle\ClarolineAdminBundle(),
        );

        if (file_exists(__DIR__.'/config/plugin/bundles'))
        {
            foreach (file(__DIR__.'/config/plugin/bundles', FILE_IGNORE_NEW_LINES) as $bundle)
            {
                $bundles[] = new $bundle;
            }
        }

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}