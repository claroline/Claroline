<?php

namespace Claroline\CommunityBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\PatchEvent;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\AddRoleEvent;
use Claroline\CoreBundle\Event\Security\RemoveRoleEvent;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RoleSubscriber implements EventSubscriberInterface
{
    private Connection $conn;
    private StrictDispatcher $dispatcher;

    public function __construct(
        Connection $conn,
        StrictDispatcher $dispatcher
    ) {
        $this->conn = $conn;
        $this->dispatcher = $dispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'pre', Role::class) => 'preCreate',
            Crud::getEventName('create', 'post', Role::class) => 'postCreate',
            Crud::getEventName('patch', 'post', Role::class) => 'postPatch',
        ];
    }

    public function preCreate(CreateEvent $event)
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

    public function postCreate(CreateEvent $event)
    {
        /** @var Role $role */
        $role = $event->getObject();

        if (Role::WS_ROLE === $role->getType() && $role->getWorkspace()) {
            // give open access to all the workspace resource
            $this->conn
                ->prepare("
                    INSERT INTO claro_resource_rights (role_id, mask, resourceNode_id)
                    SELECT {$role->getId()}, 1, resource.id FROM claro_resource_node resource
                    WHERE resource.workspace_id = {$role->getWorkspace()->getId()}
                ")
                ->execute();

            // init access rights for the workspace tools
            $this->conn
                ->prepare("
                    INSERT INTO claro_tool_rights (role_id, mask, ordered_tool_id)
                    SELECT {$role->getId()}, 0, ot.id 
                    FROM claro_ordered_tool AS ot
                    WHERE ot.workspace_id = {$role->getWorkspace()->getId()}
                ")
                ->execute();
        } elseif (Role::PLATFORM_ROLE === $role->getType()) {
            // init access rights for the desktop tools
            $this->conn
                ->prepare("
                    INSERT INTO claro_tool_rights (role_id, mask, ordered_tool_id)
                    SELECT {$role->getId()}, 0, ot.id 
                    FROM claro_ordered_tool AS ot
                    WHERE ot.workspace_id IS NULL AND user_id IS NULL
                ")
                ->execute();
        }
    }

    public function postPatch(PatchEvent $event)
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
                    $this->dispatcher->dispatch(SecurityEvents::ADD_ROLE, AddRoleEvent::class, [$users, $role]);
                } elseif ('remove' === $event->getAction()) {
                    $this->dispatcher->dispatch(SecurityEvents::REMOVE_ROLE, RemoveRoleEvent::class, [$users, $role]);
                }
            }
        }
    }
}
