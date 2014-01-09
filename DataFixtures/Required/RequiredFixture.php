<?php

namespace Claroline\CoreBundle\DataFixtures\Required;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Persistence\ObjectManager;

interface RequiredFixture
{
    public function setContainer($container);
    public function load(ObjectManager $manager);
}