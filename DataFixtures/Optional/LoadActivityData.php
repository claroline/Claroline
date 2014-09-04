<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\Optional;

use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\Activity\ActivityParameters;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadActivityData extends AbstractFixture implements ContainerAwareInterface
{
    private $name;
    private $primaryResource;
    private $secondaryResources;
    private $description;
    private $creator;
    private $parent;

    public function __construct(
        $name,
        $description,
        array $secondaryResources,
        $creator,
        $parent,
        $primaryResource = null
    )
    {
        $this->creator = $creator;
        $this->parent = $parent;
        $this->name = $name;
        $this->description = $description;
        $this->primaryResource = $primaryResource;
        $this->secondaryResources = $secondaryResources;
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $activityManager = $this->container->get('claroline.manager.activity_manager');
        $resourceManager = $this->container->get('claroline.manager.resource_manager');

        $activity = new Activity();
        $activity->setName($this->name);
        $activity->setTitle($this->name);
        $activity->setDescription($this->description);

        if ($this->primaryResource !== null) {
            $activity->setPrimaryResource($this->getReference($this->primaryResource)->getResourceNode());
        }

        $activityParameters = new ActivityParameters();
        $activityParameters->setActivity($activity);
        $activity->setParameters($activityParameters);

        $parent = $this->getReference('directory/' . $this->parent);
        $workspace = $parent->getWorkspace();
        $resourceType = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findOneByName('activity');

        $resourceManager->create(
            $activity,
            $resourceType,
            $this->getReference('user/' . $this->creator),
            $workspace,
            $parent
        );

        foreach ($this->secondaryResources as $secondaryResource) {
            $activityManager->addResource($activity, $this->getReference($secondaryResource)->getResourceNode());
        }
    }
} 