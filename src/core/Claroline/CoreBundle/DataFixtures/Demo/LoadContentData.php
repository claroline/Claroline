<?php

namespace Claroline\CoreBundle\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Claroline\CoreBundle\Entity\Home\Content;
use Claroline\CoreBundle\Entity\Home\Type;
use Claroline\CoreBundle\Entity\Home\Content2Type;

class LoadContentData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $titles = array(
            '',
            'ClarolineConnect© : plateforme Claroline de nouvelle génération.',
            '',
            'ClarolineConnect© Demo',
            'Youtube',
            'Vimeo',
            'Simple Website',
            'Wikipedia'
        );

        $textDir = __DIR__. '/files/homepage';

        $texts = array(
            'http://fr.slideshare.net/batier/claroline-connect',
            file_get_contents("{$textDir}/text1.txt", 'r'),
            "http://www.youtube.com/watch?v=4mlWeQed0_I",
            file_get_contents("{$textDir}/text4.txt", 'r'),
            'http://youtu.be/4mlWeQed0_I',
            'http://vimeo.com/63773788',
            'http://www.opengraph.be/',
            'http://fr.wikipedia.org/wiki/Claroline'
        );

        $generated = array(
            file_get_contents("{$textDir}/text2.txt", 'r'),
            '',
            file_get_contents("{$textDir}/text3.txt", 'r'),
            '',
            file_get_contents("{$textDir}/text3.txt", 'r'),
            file_get_contents("{$textDir}/text5.txt", 'r'),
            file_get_contents("{$textDir}/text6.txt", 'r'),
            file_get_contents("{$textDir}/text7.txt", 'r')
        );

        $types = array("home", "home", "home", "home", "opengraph", "opengraph", "opengraph", "opengraph");
        $sizes = array(
            "col-lg-5", "col-lg-7", "col-lg-8", "col-lg-4", "col-lg-12", "col-lg-12", "col-lg-12", "col-lg-12"
        );

        foreach ($titles as $i => $title) {
            $type = $manager->getRepository("ClarolineCoreBundle:Home\Type")->findOneBy(array('name' => $types[$i]));

            $content[$i] = new Content();
            $content[$i]->setTitle($title);
            $content[$i]->setContent($texts[$i]);
            $content[$i]->setGeneratedContent($generated[$i]);

            $first = $manager->getRepository("ClarolineCoreBundle:Home\Content2Type")->findOneBy(
                array('back' => null, 'type' => $type)
            );

            $contentType = new Content2Type($first);

            $contentType->setContent($content[$i]);
            $contentType->setType($type);
            $contentType->setSize($sizes[$i]);

            $manager->persist($contentType);
            $manager->persist($content[$i]);

            $manager->flush();
        }
    }
}
