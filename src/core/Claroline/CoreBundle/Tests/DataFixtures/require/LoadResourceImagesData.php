<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Claroline\CoreBundle\DataFixtures\LoadResourceImagesData as ResourceImagesFixture;

class LoadResourceImagesData extends ResourceImagesFixture
{
    public function getOrder()
    {
        return 12;
    }
}