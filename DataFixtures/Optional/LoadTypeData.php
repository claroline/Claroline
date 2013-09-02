<?php

namespace Claroline\CoreBundle\DataFixtures\Optional;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Claroline\CoreBundle\Entity\Home\Type;

class LoadTypeData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $fixtures = array('home', 'menu', 'opengraph', 'todo');

        foreach ($fixtures as $i => $fixture) {
            $types[$i] = new Type();
            $types[$i]->setName($fixture);

            $manager->persist($types[$i]);
        }

        $manager->flush();
    }
}
