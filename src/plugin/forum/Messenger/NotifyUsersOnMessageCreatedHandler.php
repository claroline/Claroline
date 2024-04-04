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
use Claroline\CoreBundle\Event\CatalogEvents\MessageEvents;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Validation\User as UserValidation;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class NotifyUsersOnMessageCreatedHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly TemplateManager $templateManager,
        private readonly RoutingHelper $routing,
        private readonly ObjectManager $om
    ) {
    }

    public function __invoke(NotifyUsersOnMessageCreated $forumNotification): void
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

        $placeholders = array_merge([
                'forum' => $forum->getName(),
                'subject' => $subject->getTitle(),
                'message' => $message->getContent(),
                'date' => $message->getCreationDate() ? $message->getCreationDate()->format('d/m/Y H:m:s') : null,
                'author' => $message->getCreator() ? $message->getCreator()->getFullName() : null,
                'workspace' => $forum->getResourceNode()->getWorkspace()->getName(),

                'workspace_url' => $this->routing->workspaceUrl($forum->getResourceNode()->getWorkspace()),
                'forum_url' => $this->routing->resourceUrl($forum->getResourceNode()),
                'subject_url' => $this->routing->resourceUrl($forum->getResourceNode()).'/subjects/show/'.$subject->getUuid(),
            ],
            $this->templateManager->formatDatePlaceholder('post', $message->getCreationDate()),
        );

        $subject = $this->templateManager->getTemplate('forum_new_message', $placeholders, null, 'title');
        $body = $this->templateManager->getTemplate('forum_new_message', $placeholders);

        $event = new SendMessageEvent(
            $body,
            $subject,
            array_map(function (UserValidation $userValidate) {
                return $userValidate->getUser();
            }, $usersValidate),
            $message->getCreator()
        );

        $this->dispatcher->dispatch($event, MessageEvents::MESSAGE_SENDING);
    }
}
