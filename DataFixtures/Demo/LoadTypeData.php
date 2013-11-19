<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Claroline\CoreBundle\Entity\Home\Type;

class LoadTypeData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $fixtures = array('opengraph', 'todo');

        foreach ($fixtures as $i => $fixture) {
            $types[$i] = new Type();
            $types[$i]->setName($fixture);

            $manager->persist($types[$i]);
        }

        $manager->flush();
    }
}
