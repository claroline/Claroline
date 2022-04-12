<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Subscriber\Tool;

use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class WorkspacesSubscriber implements EventSubscriberInterface
{
    const NAME = 'workspaces';

    /** @var AuthorizationCheckerInterface */
    private $authorization;

    public function __construct(
        AuthorizationCheckerInterface $authorization
    ) {
        $this->authorization = $authorization;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ToolEvents::getEventName(ToolEvents::OPEN, Tool::DESKTOP, static::NAME) => 'onOpen',
        ];
    }

    public function onOpen(OpenToolEvent $event)
    {
        $event->setData([
            'creatable' => $this->authorization->isGranted('CREATE', new Workspace()),
        ]);

        $event->stopPropagation();
    }
}
