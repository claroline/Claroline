<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Subscriber\Tool;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\API\Serializer\User\ProfileSerializer;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Event\CatalogEvents\ToolEvents;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\CoreBundle\Manager\UserManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommunitySubscriber implements EventSubscriberInterface
{
    const NAME = 'community';

    /** @var FinderProvider */
    private $finder;
    /** @var ParametersSerializer */
    private $parametersSerializer;
    /** @var ProfileSerializer */
    private $profileSerializer;
    /** @var UserManager */
    private $userManager;

    public function __construct(
        FinderProvider $finder,
        ParametersSerializer $parametersSerializer,
        ProfileSerializer $profileSerializer,
        UserManager $userManager
    ) {
        $this->finder = $finder;
        $this->parametersSerializer = $parametersSerializer;
        $this->profileSerializer = $profileSerializer;
        $this->userManager = $userManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ToolEvents::getEventName(ToolEvents::OPEN, Tool::DESKTOP, static::NAME) => 'onOpen',
            ToolEvents::getEventName(ToolEvents::OPEN, Tool::WORKSPACE, static::NAME) => 'onOpen',
            ToolEvents::getEventName(ToolEvents::OPEN, Tool::ADMINISTRATION, static::NAME) => 'onOpenAdministration',
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

    public function onOpenAdministration(OpenToolEvent $event)
    {
        $event->setData([
            // todo : put it in the async load of form
            'parameters' => $this->parametersSerializer->serialize(),
            'profile' => $this->profileSerializer->serialize(),
            'platformRoles' => $this->finder->search(Role::class, [
                'filters' => ['type' => Role::PLATFORM_ROLE],
            ]),
            'usersLimitReached' => $this->userManager->hasReachedLimit(),
        ]);

        $event->stopPropagation();
    }
}
