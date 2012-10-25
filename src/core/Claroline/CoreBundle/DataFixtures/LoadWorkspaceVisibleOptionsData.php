<?php

namespace Claroline\CoreBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Entity\Widget\WidgetAdminOption;
use Doctrine\Common\Persistence\ObjectManager;


/**
 * Resource types data fixture.
 */
class LoadWorkspaceVisibleOptionsData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /** @var ContainerInterface $container */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load (ObjectManager $manager)
    {
        $options = array(
            'LOCK_VISIBLE',
            'LOCK_UNVISIBLE',
            'CONFIGURABLE_VISIBLE',
            'CONFIGURABLE_UNVISIBLE');

        foreach ($options as $option){
            $wao = new WidgetAdminOption();
            $wao->setName($option);
            $manager->persist($wao);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 5;
    }

}