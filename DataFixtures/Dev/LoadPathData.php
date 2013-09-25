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

        $path->setPath('{"name": "My Path Name","description": "This is the path description","steps": [    {    "name": "Parcours",        "type": 1,        "picture": null,        "expanded": true,        "instructions": "This is the step instructions. But I have to tip more word.",        "durationHours": "2",        "durationMinutes": "30",        "who": 1,        "where": 1,        "withTutor": false,        "withComputer": true,        "children": [            {                "resourceId": 1,                "name": "Step-3",                "type": "seq",                "expanded": true,                "instructions": "blabla",                "durationHours": null,                "durationMinutes": null,                "who": null,                "where": null,                "withTutor": false,                "withComputer": true,                "children": [],                "resources": [],                "excludedResources": []            },            {                "resourceId": 1,                "name": "Step-4",                "type": "seq",                "expanded": true,                "instructions": "blabla",                "durationHours": null,                "durationMinutes": null,                "who": null,                "where": null,                "withTutor": false,                "withComputer": true,                "children": [],                "resources": [],                "excludedResources": []            }        ],        "excludedResources": [],        "resources": [            {                "resourceId": 1,                "name": "Texte",                "stepId": 1,                "type": "document",                "subType": "text",                "isDigital": false,                "propagateToChildren": true,                "url": "www.google.fr"            },            {                "resourceId": 1,                "name": "Indifferent",                "type": "tool",                "subType": "indifferent",                "isDigital": false,                "propagateToChildren": true,                "parentStep": "Parcours",                "isExcluded": false            }        ]    }],"progression": {    "global": "todo",    "skills": "todo",    "scenario": "todo",    "validation": "todo",    "planner": "todo"},"id": "137"}');
        $path->setName($name);
        $path->setResourceNode($resourceNode);

        $manager->persist($path);

        $manager->flush();
    }
}
