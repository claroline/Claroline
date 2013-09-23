<?php

namespace Innova\PathBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceType;

/**
 * Class LoadResourceTypeData
 * @package Innova\PathBundle\DataFixtures\ORM
 */
class LoadResourceTypeData extends AbstractFixture
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
  
        $resourceType1 = new ResourceType();
        $resourceType1->setName("path");
        $resourceType1->setExportable(true);
        $manager->persist($resourceType1);


        $resourceType2 = new ResourceType();
        $resourceType2->setName("step");
        $resourceType2->setExportable(true);
        $manager->persist($resourceType2);

        $resourceType3 = new ResourceType();
        $resourceType3->setName("non digital resource");
        $resourceType3->setExportable(true);
        $manager->persist($resourceType3);

        $manager->flush();
    }
}
