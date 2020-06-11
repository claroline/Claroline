<?php

namespace Claroline\AuthenticationBundle\Tests;

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
        $container->registerExtension(new ClarolineAuthenticationExtension());

        (new ClarolineAuthenticationBundle())->build($container);

        $container->getCompilerPassConfig()->setRemovingPasses([]);

        $container->compile();
        $this->addToAssertionCount(1); // container compiled successfully

        return $container;
    }
}
