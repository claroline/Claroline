<?php

namespace Innova\PathBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Innova\PathBundle\Entity\Path;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;

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

        $resourceNode->setCreator($manager->getRepository('ClarolineCoreBundle:User')->findOneById(1));

        $resourceNode->setResourceType($manager->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName("path"));
        $workspace = $manager->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->findOneById(2);
        $resourceNode->setWorkspace($workspace);
        $resourceNode->setMimeType("custom/activity");
        $resourceNode->setIcon($manager->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findOneById(1));
        $root = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findWorkspaceRoot($workspace);
        $resourceNode->setParent($root);

        $manager->persist($resourceNode);

        $path = new Path();
        $path->setPath('{"name":"My Path Name","description":"This is the path description","steps":[{"name":"Step","parentId":null,"type":"seq","expanded":true,"data":"activity","dataId":123,"templateId":null,"children":[{"name":"Step-1","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[]},{"name":"Step-2","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[{"name":"Step-2-1","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[]},{"name":"Step-2-2","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[{"name":"Step-2-2-1","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[]},{"name":"Step-2-2-2","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[{"name":"Step-2-2-2-1","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[{"name":"Step-2-2-2-1-1","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[{"name":"Step-2-2-2-1-1-1","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[{"name":"Step-2-2-2-1-1-1-1","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[{"name":"Step-2-2-2-1-1-1-1-1","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[]}]}]}]}]},{"name":"Step-2-2-2-2","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[]},{"name":"Step-2-2-2-3","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[]},{"name":"Step-2-2-2-4","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[]},{"name":"Step-2-2-2-5","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[]},{"name":"Step-2-2-2-6","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[]},{"name":"Step-2-2-2-7","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[]}]},{"name":"Step-2-2-3","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[]},{"name":"Step-2-2-4","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[]},{"name":"Step-2-2-5","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[]}]},{"name":"Step-2-3","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[]},{"name":"Step-2-4","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[]},{"name":"Step-2-5","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[]},{"name":"Step-2-6","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[]}]},{"name":"Step-3","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[]},{"name":"Step-4","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[]},{"name":"Step-5","parentId":null,"type":"seq","expanded":true,"dataType":null,"dataId":null,"templateId":null,"children":[]}]}],"actvities":[{"123":{"name":"My Activity Name","resourceIn":[{"id":321,"name":"toto","isInstanciated":false,"uri":"www.google.com"}]}}]}');
        $path->setName($name);
        $path->setResourceNode($resourceNode);

        $manager->persist($path);

        $manager->flush();
    }
}
