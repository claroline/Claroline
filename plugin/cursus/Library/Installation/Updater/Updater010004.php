<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Library\Installation\Updater;

use Claroline\CoreBundle\Entity\Content;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater010004 extends Updater
{
    private $contentManager;
    private $om;

    public function __construct(ContainerInterface $container)
    {
        $this->contentManager = $container->get('claroline.manager.content_manager');
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->createConfirmationMail();
    }

    private function createConfirmationMail()
    {
        $this->log('Creating confirmation email...');
        $content = $this->contentManager->getContent(
            ['type' => 'claro_cursusbundle_mail_confirmation']
        );

        if (is_null($content)) {
            $contentTransRepo = $this->om->getRepository('Claroline\CoreBundle\Entity\ContentTranslation');
            $frTitle = 'Confirmation de votre inscription';
            $frContent = '<div>Vous avez bien été inscrit au cours %course% pour la session %session% du %start_date% au %end_date%.</div>';
            $enTitle = 'Registration confirmation';
            $enContent = '<div>You have been registered to course %course% in session %session% from %start_date% to %end_date%.</div>';
            $content = new Content();
            $content->setTitle($enTitle);
            $content->setContent($enContent);
            $content->setType('claro_cursusbundle_mail_confirmation');
            $contentTransRepo->translate($content, 'title', 'fr', $frTitle);
            $contentTransRepo->translate($content, 'content', 'fr', $frContent);

            $this->om->persist($content);
            $this->om->flush();
        }
    }
}
