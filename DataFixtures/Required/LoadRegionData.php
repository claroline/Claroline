<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\Required;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Claroline\CoreBundle\Entity\Home\Content;
use Claroline\CoreBundle\Entity\Home\Region;

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
