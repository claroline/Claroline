<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Claroline\CoreBundle\DataFixtures\LoadResourceTypeData as ResourceTypeFixture;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class LoadResourceTypeData extends ResourceTypeFixture implements ContainerAwareInterface
{
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {

    }
    
    public function getOrder()
    {
        return 8;
    }
}