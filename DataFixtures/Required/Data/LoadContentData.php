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

use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\DataFixtures\Required\RequiredFixture;
use Claroline\CoreBundle\Entity\Content;

class LoadContentData implements RequiredFixture
{
    public function load(ObjectManager $manager)
    {
        $frTitle = 'Inscription à %platform_name%';
        $frContent = "<div>Votre nom d'utilisateur est %username%</div></br>";
        $frContent .= "<div>Votre mot de passe est %password%</div>";

        $enTitle = 'Registration to %platform_name%';
        $enContent = "<div>You username is %username%</div></br>";
        $enContent .= "<div>Your password is %password%</div>";

        $type = 'claro_mail_registration';

        $fr = new Content();
        $fr->setTitle($frTitle);
        $fr->setContent($frContent);
        $fr->setType($type);
        $fr->setTranslatableLocale('fr');

        $en = new Content();
        $en->setTitle($enTitle);
        $en->setContent($enContent);
        $en->setType($type);
        $en->setTranslatableLocale('en');

        $manager->persist($en);
        $manager->persist($fr);

        $manager->flush();
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }
}
