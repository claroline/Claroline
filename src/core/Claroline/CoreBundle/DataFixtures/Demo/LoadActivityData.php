<?php

namespace Claroline\CoreBundle\DataFixtures\Demo;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\Resource\ResourceActivity;

class LoadActivityData extends AbstractFixture implements ContainerAwareInterface
{
    private $name;
    private $parent;
    private $creator;
    private $resources;

    /**
     * Constructor.
     *
     * @param string $name      The activity name
     * @param string $parent    The parent reference(without 'directory/')
     * @param type   $creator   The creator reference(without 'user/')
     * @param array  $resources an array of resource ids.
     */
    public function __construct($name, $parent, $creator, array $resources)
    {
        $this->name = $name;
        $this->parent = $parent;
        $this->creator = $creator;
        $this->resources = $resources;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $activity = new Activity();
        $activity->setName($this->name);
        $activity->setInstructions(
            $this->container
                ->get('claroline.utilities.lipsum_generator')
                ->generateLipsum(100)
        );
        $activity = $this->container
            ->get('claroline.manager.resource_manager')
            ->create(
                $activity,
                $manager->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('activity'),
                $this->getReference('user/'.$this->creator),
                $this->getReference('directory/'.$this->parent)->getWorkspace(),
                $this->getReference('directory/'.$this->parent)
            );

        for ($i = 0, $count = count($this->resources), $order = 1; $i < $count; $i++, $order++) {
                $resource = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
                    ->find($this->resources[$i]);
                $rs = new ResourceActivity;
                $rs->setActivity($activity);
                $rs->setResourceNode($resource);
                $rs->setSequenceOrder($order);
                $manager->persist($rs);
        }

        $manager->flush();
        $this->addReference('activity/'.$this->name, $activity);
    }
}
