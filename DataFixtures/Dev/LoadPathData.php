<?php

namespace Innova\PathBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Innova\PathBundle\Entity\Path;
use ClarolineCoreBundle:Resource\ResourceType;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use ClarolineCoreBundle:Resource\ResourceIcon;


/**
 * Class LoadPathData
 * @package Innova\PathBundle\DataFixtures\Dev
 */
class LoadPathData extends AbstractFixture
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $name = "Path Dev";

        $resourceNode = new ResourceNode();
        $resourceNode->setName($name);
        $resourceNode->setClass("Innova\PathBundle\Entity\Path");
        //$resourceNode->setCreator($user);
        $resourceNode->setResourceType($manager->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName("path"));
        $resourceNode->setWorkspace($manager->getRepository('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace')->findOneById(2));
        $resourceNode->setWorkspace($workspace);
        $resourceNode->setMimeType("custom/activity");
        $resourceNode->setIcon($manager->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findOneById(1));

        $manager->persist($resourceNode);

        $path = new Path();
        $path->setPath("");
        $path->setName($name);
        $path->setResourceNode($resourceNode);

        $manager->persist($path);

        $manager->flush();
    }
}
