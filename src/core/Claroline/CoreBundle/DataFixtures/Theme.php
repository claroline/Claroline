<?php

namespace Claroline\CoreBundle\DataFixtures;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Claroline\CoreBundle\Entity\Theme\Theme;

class Themes extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $names = array('bootstrap-default', 'bootswatch-cyborg', 'claroline', 'visual');
        $path = array(
            'ClarolineCoreBundle:less:bootstrap-default/theme.html.twig',
            'ClarolineCoreBundle:less:bootstrap-cyborg/theme.html.twig',
            'ClarolineCoreBundle:less:claroline/theme.html.twig',
            'ClarolineCoreBundle:less:visual/theme.html.twig'
        );

        foreach ($names as $i => $name) {
            $theme[$i] = new Theme();
            $theme[$i]->setName($name);
            $theme[$i]->setPath($path[$i]);

            $manager->persist($theme[$i]);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 12; // the order in which fixtures will be loaded
    }
}
