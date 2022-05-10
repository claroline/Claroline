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

use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\API\Serializer\User\ProfileSerializer;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\CoreBundle\Manager\UserManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommunitySubscriber implements EventSubscriberInterface
{
    const NAME = 'community';

    /** @var ParametersSerializer */
    private $parametersSerializer;
    /** @var ProfileSerializer */
    private $profileSerializer;
    /** @var UserManager */
    private $userManager;

    public function __construct(
        ParametersSerializer $parametersSerializer,
        ProfileSerializer $profileSerializer,
        UserManager $userManager
    ) {
        $this->parametersSerializer = $parametersSerializer;
        $this->profileSerializer = $profileSerializer;
        $this->userManager = $userManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ToolEvents::getEventName(ToolEvents::OPEN, Tool::DESKTOP, static::NAME) => 'onOpen',
            ToolEvents::getEventName(ToolEvents::OPEN, Tool::WORKSPACE, static::NAME) => 'onOpen',
        ];
    }

    public function onOpen(OpenToolEvent $event)
    {
        $event->setData([
            'profile' => $this->profileSerializer->serialize(),
            'parameters' => $this->parametersSerializer->serialize()['profile'],
            'usersLimitReached' => $this->userManager->hasReachedLimit(),
        ]);

        $event->stopPropagation();
    }
}
