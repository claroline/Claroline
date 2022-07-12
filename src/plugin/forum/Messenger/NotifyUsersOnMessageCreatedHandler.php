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

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\CatalogEvents\MessageEvents;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Validation\User as UserValidation;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class NotifyUsersOnMessageCreatedHandler implements MessageHandlerInterface
{
    /** @var StrictDispatcher */
    private $dispatcher;
    private $templateManager;
    private $routing;
    private $om;

    public function __construct(
        StrictDispatcher $dispatcher,
        TemplateManager $templateManager,
        RoutingHelper $routing,
        ObjectManager $om
    ) {
        $this->dispatcher = $dispatcher;
        $this->templateManager = $templateManager;
        $this->routing = $routing;
        $this->om = $om;
    }

    public function __invoke(NotifyUsersOnMessageCreated $forumNotification)
    {
        $message = $this->om->getRepository(Message::class)->find($forumNotification->getMessageId());
        if (empty($message)) {
            return;
        }

        $subject = $message->getSubject();
        $forum = $subject->getForum();

        /** @var UserValidation[] $usersValidate */
        $usersValidate = $this->om
            ->getRepository(UserValidation::class)
            ->findBy(['forum' => $forum, 'notified' => true]);

        $placeholders = [
            'forum' => $forum->getName(),
            'forum_url' => $this->routing->resourcePath($forum->getResourceNode()),
            'subject' => $subject->getTitle(),
            'subject_url' => $this->routing->resourcePath($forum->getResourceNode()).'/subjects/show/'.$subject->getUuid(),
            'message' => $message->getContent(),
            'date' => $message->getCreationDate() ? $message->getCreationDate()->format('d/m/Y H:m:s') : null,
            'author' => $message->getCreator() ? $message->getCreator()->getFullName() : $message->getAuthor(),
            'workspace' => $forum->getResourceNode()->getWorkspace()->getName(),
            'workspace_url' => $this->routing->workspacePath($forum->getResourceNode()->getWorkspace()),
        ];

        $subject = $this->templateManager->getTemplate('forum_new_message', $placeholders, null, 'title');
        $body = $this->templateManager->getTemplate('forum_new_message', $placeholders);

        $this->dispatcher->dispatch(MessageEvents::MESSAGE_SENDING, SendMessageEvent::class, [
            $body,
            $subject,
            array_map(function (UserValidation $userValidate) {
                return $userValidate->getUser();
            }, $usersValidate),
            $message->getCreator(),
        ]);
    }
}
