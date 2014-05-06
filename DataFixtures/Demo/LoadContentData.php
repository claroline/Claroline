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
            'ClarolineConnect©',
            '',
            '',
            'Demo',
            'Youtube',
            'Vimeo',
            'Wikipedia'
        );

        $textDir = __DIR__. '/files/homepage';
        $locales = array('en', 'fr', 'es');

        foreach ($locales as $locale) {
            $text[$locale] = array(
                file_get_contents($textDir . '/text1.' . $locale . '.html', 'r'),
                file_get_contents($textDir . '/text2.' . $locale . '.html', 'r'),
                file_get_contents($textDir . '/text3.' . $locale . '.html', 'r'),
                file_get_contents($textDir . '/text4.' . $locale . '.html', 'r'),
                file_get_contents($textDir . '/text3.' . $locale . '.html', 'r'),
                file_get_contents($textDir . '/text5.' . $locale . '.html', 'r'),
                file_get_contents($textDir . '/text6.' . $locale . '.html', 'r')
            );
        }

        $types = array('home', 'home', 'home', 'home', 'opengraph', 'opengraph', 'opengraph', 'opengraph');
        $sizes = array(
            'content-6', 'content-6', 'content-5', 'content-7', 'content-12', 'content-12', 'content-12', 'content-12'
        );

        foreach ($titles as $i => $title) {
            $type = $manager->getRepository('ClarolineCoreBundle:Home\Type')->findOneBy(array('name' => $types[$i]));

            $content[$i] = new Content();
            $content[$i]->setTitle($title);

            foreach ($locales as $locale) {
                $content[$i]->setContent($text[$locale][$i]);
                $content[$i]->setTranslatableLocale($locale);
                $manager->persist($content[$i]);
                $manager->flush();
            }

            $first = $manager->getRepository('ClarolineCoreBundle:Home\Content2Type')->findOneBy(
                array('back' => null, 'type' => $type)
            );

            $contentType = new Content2Type($first);

            $contentType->setContent($content[$i]);
            $contentType->setType($type);
            $contentType->setSize($sizes[$i]);

            $manager->persist($contentType);

            $manager->flush();
        }
    }
}
