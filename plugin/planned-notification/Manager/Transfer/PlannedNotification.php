<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PlannedNotificationBundle\Manager\Transfer;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Workspace\Transfer\Tools\ToolImporterInterface;
use Claroline\PlannedNotificationBundle\Entity\Message;
use Claroline\PlannedNotificationBundle\Entity\PlannedNotification as Planned;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @DI\Service("claroline.transfer.claroline_planned_notification_tool")
 */
class PlannedNotification implements ToolImporterInterface
{
    /**
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"  = @DI\Inject("security.token_storage"),
     *     "om"            = @DI\Inject("claroline.persistence.object_manager"),
     *     "finder"        = @DI\Inject("claroline.api.finder"),
     *     "crud"          = @DI\Inject("claroline.api.crud")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        FinderProvider $finder,
        ObjectManager $om,
        Crud $crud
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->finder = $finder;
        $this->crud = $crud;
    }

    public function serialize(Workspace $workspace, array $options): array
    {
        return [
            'planned' => $this->finder->search(Planned::class, ['filters' => ['workspace' => $workspace->getUuid()]])['data'],
            'messages' => $this->finder->search(Message::class, ['filters' => ['workspace' => $workspace->getUuid()]])['data'],
        ];
    }

    public function deserialize(array $data, Workspace $workspace, array $options, FileBag $bag)
    {
        foreach ($data['messages'] as $message) {
            $this->om->startFlushSuite();
            $new = $this->crud->create(Message::class, $message, [Options::REFRESH_UUID]);
            $new->setWorkspace($workspace);
            $new->emptyNotifications();

            $this->om->persist($new);
            $this->om->endFlushSuite();
        }
    }

    public function prepareImport(array $orderedToolData, array $data): array
    {
        return $data;
    }
}
