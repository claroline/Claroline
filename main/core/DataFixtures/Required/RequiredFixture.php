<?php

namespace Claroline\CoreBundle\DataFixtures\Required;

use Claroline\AppBundle\Persistence\ObjectManager;

interface RequiredFixture
{
    public function setContainer($container);

    public function load(ObjectManager $manager);
}
