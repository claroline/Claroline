<?php

namespace Claroline\CoreBundle\DataFixtures\Required;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Claroline\CoreBundle\Entity\Theme\Theme;

class LoadThemeData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $themes = array(
            'Claroline' => 'ClarolineCoreBundle:less:claroline/theme.html.twig',
            'Claroline Orange' => 'ClarolineCoreBundle:less:claroline-orange/theme.html.twig',
            'Claroline Mint' => 'ClarolineCoreBundle:less:claroline-mint/theme.html.twig',
            'Claroline Gold' => 'ClarolineCoreBundle:less:claroline-gold/theme.html.twig',
            'Claroline Ruby' => 'ClarolineCoreBundle:less:claroline-ruby/theme.html.twig',
            'Claroline Black' => 'ClarolineCoreBundle:less:claroline-black/theme.html.twig',
            'Claroline Dark' => 'ClarolineCoreBundle:less:claroline-dark/theme.html.twig',
            'Bootstrap Default' => 'ClarolineCoreBundle:less:bootstrap-default/theme.html.twig'
        );

        foreach ($themes as $name => $path) {
            $theme[$name] = new Theme();
            $theme[$name]->setName($name);
            $theme[$name]->setPath($path);

            $manager->persist($theme[$name]);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 8;
    }
}

