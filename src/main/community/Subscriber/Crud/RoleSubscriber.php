<?php

namespace Claroline\CommunityBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\PatchEvent;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\AddRoleEvent;
use Claroline\CoreBundle\Event\Security\RemoveRoleEvent;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RoleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Connection $conn,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'pre', Role::class) => 'preCreate',
            Crud::getEventName('create', 'post', Role::class) => 'postCreate',
            Crud::getEventName('patch', 'post', Role::class) => 'postPatch',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        /** @var Role $role */
        $role = $event->getObject();

        if (empty($role->getName())) {
            switch ($role->getType()) {
                case Role::WS_ROLE:
                    if ($role->getWorkspace()) {
                        $role->setName(strtoupper('role_ws_'.TextNormalizer::toKey($role->getTranslationKey())).'_'.$role->getWorkspace()->getUuid());
                    }
                    break;
                case Role::USER_ROLE:
                    if (!empty($role->getUsers())) {
                        // user roles are only assigned to one user
                        $owner = $role->getUsers()[0];
                        $role->setName(strtoupper('role_user_'.strtoupper(TextNormalizer::toKey($owner->getUsername()))));
                    }
                    break;
                default:
                    $role->setName(strtoupper('role_'.TextNormalizer::toKey($role->getTranslationKey())));
            }
        }
    }

    public function postCreate(CreateEvent $event): void
    {
        /** @var Role $role */
        $role = $event->getObject();

        if (Role::WS_ROLE === $role->getType() && $role->getWorkspace()) {
            // give open access to all the workspace resource
            $this->conn
                ->prepare('
                    INSERT INTO claro_resource_rights (role_id, mask, resourceNode_id)
                        SELECT :roleId, 1, resource.id 
                        FROM claro_resource_node AS resource
                        WHERE resource.workspace_id = :workspaceId
                ')
                ->executeQuery([
                    'roleId' => $role->getId(),
                    'workspaceId' => $role->getWorkspace()->getId(),
                ]);

            // init access rights for the workspace tools
            $this->conn
                ->prepare('
                    INSERT INTO claro_tool_rights (role_id, mask, ordered_tool_id)
                        SELECT :roleId, 0, ot.id 
                        FROM claro_ordered_tool AS ot
                        WHERE ot.context_id = :contextId
                ')
                ->executeQuery([
                    'roleId' => $role->getId(),
                    'contextId' => $role->getWorkspace()->getUuid(),
                ]);
        } elseif (Role::PLATFORM_ROLE === $role->getType()) {
            // init access rights for the desktop tools
            $this->conn
                ->prepare('
                    INSERT INTO claro_tool_rights (role_id, mask, ordered_tool_id)
                        SELECT :roleId, 0, ot.id 
                        FROM claro_ordered_tool AS ot
                        WHERE ot.context_id IS NULL
                ')
                ->executeQuery([
                    'roleId' => $role->getId(),
                ]);

            $this->conn
                ->prepare("
                    INSERT INTO claro_tool_rights (role_id, mask, ordered_tool_id)
                    SELECT {$role->getId()}, 0, ot.id 
                    FROM claro_ordered_tool AS ot
                    WHERE ot.context_id IS NULL AND ot.context_name = {DesktopContext::getName()}
                ")
                ->execute();
        }
    }

    public function postPatch(PatchEvent $event): void
    {
        if (in_array($event->getProperty(), ['user', 'group'])) {
            $role = $event->getObject();
            $users = [];

            if ($event->getValue() instanceof User) {
                $users[] = $event->getValue();
            } elseif ($event->getValue() instanceof Group) {
                foreach ($event->getValue()->getUsers() as $user) {
                    if ($user->isEnabled() && !$user->isRemoved() && !$user->hasRole($role->getName(), false)) {
                        $users[] = $user;
                    }
                }
            }

            if (!empty($users)) {
                if ('add' === $event->getAction()) {
                    $this->eventDispatcher->dispatch(new AddRoleEvent($users, $role), SecurityEvents::ADD_ROLE);
                } elseif ('remove' === $event->getAction()) {
                    $this->eventDispatcher->dispatch(new RemoveRoleEvent($users, $role), SecurityEvents::REMOVE_ROLE);
                }
            }
        }
    }
}
