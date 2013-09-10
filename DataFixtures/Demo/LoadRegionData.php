<?php

namespace Claroline\CoreBundle\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Claroline\CoreBundle\Entity\Home\Content;
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
            "Contenu d'exemple" => array("url" => "/type/opengraph", "icon" => ""),
            "Accueil" => array("url" => "/", "icon" => "")
        );

        $footer = array(
            "Services" => array("url" => "http://www.claroline.net/communaute-2/", "icon" => ""),
            "Communauté" => array("url" => "http://www.claroline.net/worldwide/", "icon" => ""),
            "Consortium" => array("url" => "http://www.claroline.net/consortium/", "icon" => ""),
            "À propos nous" => array("url" => "http://www.claroline.net/breve-presentation/", "icon" => ""),
            "Accueil" => array("url" => "/", "icon" => "")
        );

        $this->menuToRegion($manager, $this->menu($manager, "Entête", $header), 'header');
        $this->menuToRegion($manager, $this->menu($manager, "Pied de page", $footer), 'footer');
    }

    private function menu($manager, $name = "Menu", $links = null)
    {
        $content = new Content($manager);
        $content->setTitle($name);

        $manager->persist($content);

        $type = $manager->getRepository("ClarolineCoreBundle:Home\Type")->findOneBy(array('name' => 'menu'));

        $first = $manager->getRepository("ClarolineCoreBundle:Home\Content2Type")->findOneBy(
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

            $first = $manager->getRepository("ClarolineCoreBundle:Home\SubContent")->findOneBy(
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
        $region = $manager->getRepository("ClarolineCoreBundle:Home\Region")->findOneBy(array('name' => $regionName));

        $first = $manager->getRepository("ClarolineCoreBundle:Home\Content2Region")->findOneBy(
            array('back' => null, 'region' => $region)
        );

        $contentRegion = new Content2Region($first);
        $contentRegion->setContent($content);
        $contentRegion->setRegion($region);

        $manager->persist($contentRegion);

        $manager->flush();
    }
}
