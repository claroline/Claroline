<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Claroline\CoreBundle\Entity\Content;
use Claroline\CoreBundle\Entity\Home\SubContent;
use Claroline\CoreBundle\Entity\Home\Region;
use Claroline\CoreBundle\Entity\Home\Content2Region;
use Claroline\CoreBundle\Entity\Home\Type;
use Claroline\CoreBundle\Entity\Home\Content2Type;

class LoadRegionData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $header = array(
            'Sample content' => array(
                'url' => '/type/opengraph',
                'icon' => '',
                'translation' => array('fr' => 'Contenu d\'exemple', 'es' => 'Contenido de ejemplo')
            ),
            'Home' => array(
                'url' => '/',
                'icon' => '',
                'translation' => array('fr' => 'Accueil', 'es' => 'Inicio')
            )
        );

        $footer = array(
            'Contact' => array(
                'url' => 'http://www.claroline.net/type/contact',
                'icon' => '',
                'translation' => array('fr' => 'Contactez-nous', 'es' => 'Contacto')
            ),
            'Consortium' => array(
                'url' => 'http://www.claroline.net/type/consortium',
                'icon' => '',
                'translation' => array('fr' => 'Consortium', 'es' => 'Consorcio')
            ),
            'About Us' => array(
                'url' => 'http://www.claroline.net/type/claroline',
                'icon' => '',
                'translation' => array('fr' => 'À propos de nous', 'es' => '¿Quiénes somos?')
            ),
            'Home' => array(
                'url' => '/',
                'icon' => '',
                'translation' => array('fr' => 'Accueil', 'es' => 'Inicio')
            )
        );

        $this->menuToRegion($manager, $this->menu($manager, 'Header', $header), 'header');
        $this->menuToRegion($manager, $this->menu($manager, 'footer', $footer), 'footer');
    }

    private function menu($manager, $name = 'Menu', $links = null)
    {
        $content = new Content($manager);
        $content->setTitle($name);

        $manager->persist($content);

        $type = $manager->getRepository('ClarolineCoreBundle:Home\Type')->findOneBy(array('name' => 'menu'));

        $first = $manager->getRepository('ClarolineCoreBundle:Home\Content2Type')->findOneBy(
            array('back' => null, 'type' => $type)
        );

        $contentType = new Content2Type($first);
        $contentType->setContent($content);
        $contentType->setType($type);

        $manager->persist($contentType);

        $manager->flush();

        foreach ($links as $name => $link) {
            $linkContent = new Content();
            $linkContent->setTitle($name);
            $linkContent->setContent($link['url']);

            $manager->persist($linkContent);
            $manager->flush();

            foreach ($link['translation'] as $locale => $translation) {
                $linkContent->setTitle($translation);
                $linkContent->setTranslatableLocale($locale);
                $manager->persist($linkContent);
                $manager->flush();
            }

            $first = $manager->getRepository('ClarolineCoreBundle:Home\SubContent')->findOneBy(
                array('back' => null, 'father' => $content)
            );

            $subContent = new SubContent($first);
            $subContent->setFather($content);
            $subContent->setChild($linkContent);

            $manager->persist($subContent);

            $manager->flush();
        }

        return $content;
    }

    public function menuToRegion($manager, $content, $regionName)
    {
        $region = $manager->getRepository('ClarolineCoreBundle:Home\Region')->findOneBy(array('name' => $regionName));

        $first = $manager->getRepository('ClarolineCoreBundle:Home\Content2Region')->findOneBy(
            array('back' => null, 'region' => $region)
        );

        $contentRegion = new Content2Region($first);
        $contentRegion->setContent($content);
        $contentRegion->setRegion($region);

        $manager->persist($contentRegion);

        $manager->flush();
    }
}
