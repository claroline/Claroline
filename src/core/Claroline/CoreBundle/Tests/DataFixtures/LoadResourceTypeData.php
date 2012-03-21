<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Claroline\CoreBundle\DataFixtures\LoadResourceTypeData as ResourceTypeFixture;

class LoadResourceTypeData extends ResourceTypeFixture
{   
    public function getOrder()
    {
        return 8;
    }
}