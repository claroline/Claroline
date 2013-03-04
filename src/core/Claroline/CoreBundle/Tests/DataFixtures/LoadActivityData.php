<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

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
     * @param string $name     The activity name
     * @param string $parent   The parent reference(without 'directory/')
     * @param type $creator    The creator reference(without 'user/')
     * @param array $resources an array of resource ids.
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
            ->get('claroline.resource.manager')
            ->create(
                $activity,
                $this->getReference('directory/'.$this->parent)->getId(),
                'activity',
                $this->getReference('user/'.$this->creator)
            );

        for ($i = 0, $count = count($this->resources), $order = 1; $i < $count; $i++, $order++) {
                $resource = $manager->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
                    ->find($this->resources[$i]);
                $rs = new ResourceActivity;
                $rs->setActivity($activity);
                $rs->setResource($resource);
                $rs->setSequenceOrder($order);
                $manager->persist($rs);
        }

        $manager->flush();
        $this->addReference('activity/'.$this->name, $activity);
    }

}

