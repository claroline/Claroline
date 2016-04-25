<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\DataFixtures;

use Claroline\CoreBundle\Entity\Content;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadRequiredData extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $om)
    {
        $contentTransRepo = $om->getRepository('Claroline\CoreBundle\Entity\ContentTranslation');

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

        $om->persist($content);
        $om->flush();
    }
}
