<?php

namespace Claroline\CoreBundle\DataFixtures\Required;

use Claroline\CoreBundle\Entity\Workspace\Template;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Resource types data fixture.
 */
class LoadTemplateData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /** @var ContainerInterface $container */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /*
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $templatesArray = array(
            array('hash' => 'default.zip', 'name' => 'default')
        );

        foreach ($templatesArray as $templateItem) {
            $template = new Template();
            $template->setName($templateItem['name']);
            $template->setHash($templateItem['hash']);
            $manager->persist($template);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 7;
    }
}
