<?php

namespace Claroline\CoreBundle\DataFixtures\Required;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Claroline\CoreBundle\Entity\Home\Content;
use Claroline\CoreBundle\Entity\Home\SubContent;
use Claroline\CoreBundle\Entity\Home\Region;
use Claroline\CoreBundle\Entity\Home\Content2Region;
use Claroline\CoreBundle\Entity\Home\Type;
use Claroline\CoreBundle\Entity\Home\Content2Type;

class LoadRegionData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $names = array('header', 'left', 'content', 'right', 'footer');

        foreach ($names as $name) {
            $region = new Region();

            $region->setName($name);

            $manager->persist($region);
        }

        $manager->flush();
    }
}
