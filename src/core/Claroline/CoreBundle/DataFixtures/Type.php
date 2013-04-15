<?php

namespace Claroline\CoreBundle\DataFixtures;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Claroline\CoreBundle\Entity\Home\Type;

class Types extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $fixtures = array('home', 'menu', 'users', 'html', 'todo');

        foreach ($fixtures as $i => $fixture) {
            $types[$i] = new Type();
            $types[$i]->setName($fixture);

            $manager->persist($types[$i]);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 7; // the order in which fixtures will be loaded
    }
}
