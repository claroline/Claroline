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
use Claroline\CoreBundle\Entity\Home\Type;
use Claroline\CoreBundle\Entity\Home\Content2Type;

class LoadContentData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $titles = array(
            'ClarolineConnect© : plateforme Claroline de nouvelle génération.',
            '',
            '',
            'ClarolineConnect© Demo',
            'Youtube',
            'Vimeo',
            'Wikipedia'
        );

        $textDir = __DIR__. '/files/homepage';

        $texts = array(
            file_get_contents("{$textDir}/text1.txt", 'r'),
            file_get_contents("{$textDir}/text2.txt", 'r'),
            file_get_contents("{$textDir}/text3.txt", 'r'),
            file_get_contents("{$textDir}/text4.txt", 'r'),
            file_get_contents("{$textDir}/text3.txt", 'r'),
            file_get_contents("{$textDir}/text5.txt", 'r'),
            file_get_contents("{$textDir}/text6.txt", 'r')
        );

        $types = array('home', 'home', 'home', 'home', 'opengraph', 'opengraph', 'opengraph', 'opengraph');
        $sizes = array(
            'content-6', 'content-6', 'content-5', 'content-7', 'content-12', 'content-12', 'content-12', 'content-12'
        );

        foreach ($titles as $i => $title) {
            $type = $manager->getRepository('ClarolineCoreBundle:Home\Type')->findOneBy(array('name' => $types[$i]));

            $content[$i] = new Content();
            $content[$i]->setTitle($title);
            $content[$i]->setContent($texts[$i]);

            $first = $manager->getRepository('ClarolineCoreBundle:Home\Content2Type')->findOneBy(
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
