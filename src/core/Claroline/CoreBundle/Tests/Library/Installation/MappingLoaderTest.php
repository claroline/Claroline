<?php

namespace Claroline\CoreBundle\Library\Installation;

use Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class MappingLoaderTest extends MockeryTestCase
{
    private $bundle;
    private $container;
    private $loader;

    protected function setUp()
    {
        parent::setUp();
        $this->container = $this->mock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->loader = new MappingLoader($this->container);
    }

    public function testRegisterMapping()
    {
        $em = $this->mock('Doctrine\ORM\EntityManagerInterface');
        $config = $this->mock('Doctrine\ORM\Configuration');
        $driverImpl = $this->mock('Doctrine\ORM\Mapping\Driver\DriverChain');
        $driver = $this->mock('Doctrine\ORM\Mapping\Driver\AnnotationDriver');
        $bundle = $this->mock('Symfony\Component\HttpKernel\Bundle\Bundle');

        $this->container->shouldReceive('get')->with('doctrine.orm.entity_manager')->andReturn($em);
        $bundle->shouldReceive('getPath')->once()->andReturn('/bundle/path');
        $bundle->shouldReceive('getNamespace')->once()->andReturn('Bundle\Namespace');
        $em->shouldReceive('getConfiguration')->once()->andReturn($config);
        $config->shouldReceive('getMetadataDriverImpl')->once()->andReturn($driverImpl);
        $driverImpl->shouldReceive('getDrivers')->once()->andReturn(array($driver));
        $driver->shouldReceive('addPaths')->once()->with(array('/bundle/path/Entity'));
        $driverImpl->shouldReceive('addDriver')->once()->with($driver, 'Bundle\Namespace\Entity');
        $config->shouldReceive('getEntityNamespaces')->once()->andReturn(array('Foo\Bar'));
        $config->shouldReceive('setEntityNamespaces')->once()->with(array('Foo\Bar', 'Bundle\Namespace\Entity'));
        $this->container->shouldReceive('set')->with('doctrine.orm.entity_manager', $em);

        $this->loader->registerMapping($bundle);
    }
}
