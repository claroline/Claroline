<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\Required\Data;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\DataFixtures\Required\RequiredFixture;
use Claroline\CoreBundle\Entity\Content;

class LoadContentData implements RequiredFixture
{
    public function load(ObjectManager $manager)
    {
        $repository = $manager->getRepository('Claroline\CoreBundle\Entity\ContentTranslation');

        //mails
        $frTitle = 'Inscription à %platform_name%';
        $frContent = "<div>Votre nom d'utilisateur est %username%</div></br>";
        $frContent .= '<div>Votre mot de passe est %password%</div>';
        $frContent .= '<div>%validation_mail%</div>';
        $enTitle = 'Registration to %platform_name%';
        $enContent = '<div>You username is %username%</div></br>';
        $enContent .= '<div>Your password is %password%</div>';
        $enContent .= '<div>%validation_mail%</div>';
        $type = 'claro_mail_registration';
        $content = new Content();
        $content->setTitle($enTitle);
        $content->setContent($enContent);
        $content->setType($type);
        $repository->translate($content, 'title', 'fr', $frTitle);
        $repository->translate($content, 'content', 'fr', $frContent);
        $manager->persist($content);

        //layout
        $frLayout = '<div></div>%content%<div></hr>Powered by %platform_name%</div>';
        $enLayout = '<div></div>%content%<div></hr>Powered by %platform_name%</div>';
        $layout = new Content();
        $layout->setContent($enLayout);
        $layout->setType('claro_mail_layout');
        $repository->translate($layout, 'content', 'fr', $frLayout);
        $manager->persist($layout);

        $manager->flush();
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }
}
