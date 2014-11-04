<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\Required\Data;

use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Home\Type;
use Claroline\CoreBundle\DataFixtures\Required\RequiredFixture;

class LoadTypeData implements RequiredFixture
{
    public function load(ObjectManager $manager)
    {
        $fixtures = array('home', 'menu');

        foreach ($fixtures as $i => $fixture) {
            $types[$i] = new Type();
            $types[$i]->setName($fixture);
            $types[$i]->setPublish(true);

            $manager->persist($types[$i]);
        }
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }
}
