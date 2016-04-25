<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater030100 extends Updater
{
    private $em;
    private $forumRepo;
    private $messageRepo;

    public function __construct(ContainerInterface $container)
    {
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->forumRepo = $this->em->getRepository('ClarolineForumBundle:Forum');
        $this->messageRepo = $this->em->getRepository('ClarolineForumBundle:Message');
    }

    public function postUpdate()
    {
        $this->updateAuthors();
    }

    private function updateAuthors()
    {
        $this->log('Updating forum subjects authors...');
        $subjects = $this->forumRepo->findSubjectsWithNoAuthor();

        foreach ($subjects as $subject) {
            $creator = $subject->getCreator();
            $author = $creator->getFirstName().' '.$creator->getLastName();
            $subject->setAuthor($author);
            $this->em->persist($subject);
        }

        $this->log('Updating forum messages authors...');
        $messages = $this->messageRepo->findMessagesWithNoAuthor();

        foreach ($messages as $message) {
            $creator = $message->getCreator();
            $author = $creator->getFirstName().' '.$creator->getLastName();
            $message->setAuthor($author);
            $this->em->persist($message);
        }

        $this->em->flush();
    }
}
