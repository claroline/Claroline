<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Manager;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Subject;
use Claroline\ForumBundle\Entity\Validation\User as UserValidation;
use Claroline\MessageBundle\Manager\MessageManager;

class ForumManager
{
    /** @var RoutingHelper */
    private $helper;

    /** @var ObjectManager */
    private $om;

    /** @var FinderProvider */
    private $finder;

    /** @var MessageManager */
    private $messageManager;

    /** @var TemplateManager */
    private $templateManager;

    private $messageRepo;

    private $subjectRepo;

    /**
     * ForumManager constructor.
     */
    public function __construct(
        RoutingHelper $helper,
        FinderProvider $finder,
        ObjectManager $om,
        MessageManager $messageManager,
        TemplateManager $templateManager
    ) {
        $this->helper = $helper;
        $this->finder = $finder;
        $this->om = $om;
        $this->messageManager = $messageManager;
        $this->templateManager = $templateManager;

        $this->messageRepo = $om->getRepository(Message::class);
        $this->subjectRepo = $om->getRepository(Subject::class);
    }

    public function getHotSubjects(Forum $forum)
    {
        $date = new \DateTime();
        $date->modify('-1 week');

        /** @var Message[] $messages */
        $messages = $this->finder->fetch(Message::class, [
            'createdAfter' => $date,
            'forum' => $forum->getUuid(),
        ]);

        $totalMessages = count($messages);

        if (0 === $totalMessages) {
            return [];
        }

        $subjects = [];

        foreach ($messages as $message) {
            $total = isset($subjects[$message->getSubject()->getUuid()]) ?
              $subjects[$message->getSubject()->getUuid()] + 1 : 1;

            $subjects[$message->getSubject()->getUuid()] = $total;
        }

        $totalSubjects = count($subjects);
        $avg = $totalMessages / $totalSubjects;

        foreach ($subjects as $subject => $count) {
            if ($count < $avg) {
                unset($subjects[$subject]);
            }
        }

        return array_keys($subjects);
    }

    public function getValidationUser(User $creator, Forum $forum)
    {
        $user = $this->om->getRepository('ClarolineForumBundle:Validation\User')->findOneBy([
          'user' => $creator,
          'forum' => $forum,
        ]);

        if (!$user) {
            $user = new UserValidation();
            $user->setForum($forum);
            $user->setUser($creator);
            $this->om->persist($user);
            $this->om->flush();
        }

        return $user;
    }

    public function notifyMessage(Message $message)
    {
        $subject = $message->getSubject();
        $forum = $subject->getForum();

        /** @var UserValidation[] $usersValidate */
        $usersValidate = $this->om
            ->getRepository(UserValidation::class)
            ->findBy(['forum' => $forum, 'notified' => true]);

        $placeholders = [
            'forum' => $forum->getName(),
            'forum_url' => $this->helper->resourcePath($forum->getResourceNode()),
            'subject' => $subject->getTitle(),
            'subject_url' => $this->helper->resourcePath($forum->getResourceNode()).'/subjects/show/'.$subject->getUuid(),
            'message' => $message->getContent(),
            'date' => $message->getCreationDate() ? $message->getCreationDate()->format('d/m/Y H:m:s') : null,
            'author' => $message->getCreator() ? $message->getCreator()->getFullName() : $message->getAuthor(),
            'workspace' => $forum->getResourceNode()->getWorkspace()->getName(),
            'workspace_url' => $this->helper->workspacePath($forum->getResourceNode()->getWorkspace()),
        ];

        $subject = $this->templateManager->getTemplate('forum_new_message', $placeholders, null, 'title');
        $body = $this->templateManager->getTemplate('forum_new_message', $placeholders);

        $toSend = $this->messageManager->create(
            $body,
            $subject,
            array_map(function (UserValidation $userValidate) {
                return $userValidate->getUser();
            }, $usersValidate)
        );

        $this->messageManager->send($toSend);
    }

    /**
     * Find all content for a given user and the replace him by another.
     *
     * @return int
     */
    public function replaceSubjectUser(User $from, User $to)
    {
        $subjects = $this->subjectRepo->findBy(['creator' => $from]);
        if (count($subjects) > 0) {
            foreach ($subjects as $subject) {
                $subject->setCreator($to);
            }
            $this->om->flush();
        }

        return count($subjects);
    }

    /**
     * Find all content for a given user and the replace him by another.
     *
     * @return int
     */
    public function replaceMessageUser(User $from, User $to)
    {
        $messages = $this->messageRepo->findBy(['creator' => $from]);
        if (count($messages) > 0) {
            foreach ($messages as $message) {
                $message->setCreator($to);
                $message->setAuthor($to->getFirstName().' '.$to->getLastName());
            }
            $this->om->flush();
        }

        return count($messages);
    }
}
