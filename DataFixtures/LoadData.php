<?php

namespace Innova\PathBundle\DataFixtures\;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Plugin;

/**
 * Class LoadData
 * @package Innova\PathBundle\DataFixtures\ORM
 */
class LoadData implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
      $plugin = $this->em
            ->getRepository('Claroline\CoreBundle\Entity\Plugin')
            ->findOneByShort_name("PathBundle");

            
        $resourceType1 = new ResourceType();
        $resourceType1->setName("path");
        $manager->persist($resourceType1);


        $resourceType2 = new ResourceType();
        $resourceType2->setName("step");
        $manager->persist($resourceType2);

        


        $manager->flush();
    }
}
