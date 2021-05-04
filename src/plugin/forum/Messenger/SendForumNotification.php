<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\ForumBundle\Entity\ForumNotification;
use Claroline\ForumBundle\Entity\Validation\User as UserValidation;
use Claroline\MessageBundle\Manager\MessageManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SendForumNotification implements MessageHandlerInterface
{
    private $messageManager;
    private $templateManager;
    private $helper;
    private $om;

    public function __construct(
        MessageManager $messageManager,
        TemplateManager $templateManager,
        RoutingHelper $helper,
        ObjectManager $om
    ) {
        $this->messageManager = $messageManager;
        $this->templateManager = $templateManager;
        $this->helper = $helper;
        $this->om = $om;
    }

    public function __invoke(ForumNotification $forumNotification)
    {
        $message = $forumNotification->getMessage();
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
}
