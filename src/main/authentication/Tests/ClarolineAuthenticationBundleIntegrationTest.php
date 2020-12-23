<?php

namespace Claroline\AuthenticationBundle\Tests;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AuthenticationBundle\ClarolineAuthenticationBundle;
use Claroline\AuthenticationBundle\DependencyInjection\ClarolineAuthenticationExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/*
 * Basic integration test ensuring that the container compiles correctly.
 */
class ClarolineAuthenticationBundleIntegrationTest extends TestCase
{
    public function testContainerCompilation()
    {
        $container = new ContainerBuilder();
        $container->setParameter('claroline.param.config_directory', 'dummy');
        $container->setParameter('secret', 'dummy');
        $container->register(AbstractCrudController::class, AbstractCrudController::class)->setAbstract(true);

        $container->registerExtension(new ClarolineAuthenticationExtension());
        $container->loadFromExtension('claroline_authentication', []);

        (new ClarolineAuthenticationBundle())->build($container);

        // prevents errors for missing parent services coming from AppBundle
        $container->getCompilerPassConfig()->setAfterRemovingPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);

        $container->compile();
        $this->addToAssertionCount(1); // container compiled successfully

        return $container;
    }
}
