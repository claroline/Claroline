<<<<<<< HEAD
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
        $names = array('Claroline', 'Bootstrap Default', 'Bootstrap Edit', 'Bootswatch Cyborg');
        $path = array(
            'ClarolineCoreBundle:less:claroline/theme.html.twig',
            'ClarolineCoreBundle:less:bootstrap-default/theme.html.twig',
            'ClarolineCoreBundle:less:bootstrap-edit/theme.html.twig',
            'ClarolineCoreBundle:less:bootswatch-cyborg/theme.html.twig'
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
        return 8;
    }
}
||||||| merged common ancestors
=======
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
            'Bootstrap Default' => 'ClarolineCoreBundle:less:bootstrap-default/theme.html.twig',
            'Claroline Dark' => 'ClarolineCoreBundle:less:claroline-dark/theme.html.twig'
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
>>>>>>> main-core
