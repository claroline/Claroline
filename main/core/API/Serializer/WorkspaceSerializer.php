<?php

namespace Claroline\CoreBundle\API\Serializer;

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @DI\Service("claroline.serializer.workspace")
 * @DI\Tag("claroline.serializer")
 */
class WorkspaceSerializer
{
    private $om;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorization;

    /**
     * @var StrictDispatcher
     */
    private $eventDispatcher;

    /**
     * ResourceNodeManager constructor.
     *
     * @DI\InjectParams({
     *     "om"                = @DI\Inject("claroline.persistence.object_manager"),
     *     "authorization"     = @DI\Inject("security.authorization_checker"),
     *     "eventDispatcher"   = @DI\Inject("claroline.event.event_dispatcher"),
     *     "userManager"       = @DI\Inject("claroline.manager.user_manager"),
     *     "roleManager"       = @DI\Inject("claroline.manager.role_manager")
     * })
     *
     * @param ObjectManager                 $om
     * @param AuthorizationCheckerInterface $authorization
     * @param StrictDispatcher              $eventDispatcher
     * @param UserManager                   $userManager
     * @param RoleManager                   $roleManager
     */
    public function __construct(
        ObjectManager $om,
        AuthorizationCheckerInterface $authorization,
        StrictDispatcher $eventDispatcher,
        UserManager $userManager,
        RoleManager $roleManager
    ) {
        $this->om = $om;
        $this->authorization = $authorization;
        $this->eventDispatcher = $eventDispatcher;
        $this->userManager = $userManager;
        $this->roleManager = $roleManager;
    }

    /**
     * Serializes a Workspace entity for the JSON api.
     *
     * @param Workspace $workspace - the workspace to serialize
     *
     * @return array - the serialized representation of the workspace
     */
    public function serialize(Workspace $workspace)
    {
        $roleManager = $this->roleManager->getManagerRole($workspace);
        $managers = $this->userManager->getUsersByRolesIncludingGroups([$roleManager]);
        $creator = $workspace->getCreator();

        $serializedWorkspace = [
          'id' => $workspace->getId(),
          'uuid' => $workspace->getGuid(),
          'name' => $workspace->getName(),
          'code' => $workspace->getCode(),
          //moment timestamp
          'dateCreation' => $workspace->getCreationDate()->format('Y-m-d\TH:i:s'),
          'creator' => [
            'id' => $creator ? $creator->getId() : 0,
            'uuid' => $creator ? $creator->getId() : 0,
            'username' => $creator ? $creator->getUsername() : 'undefined',
          ],
          'isModel' => $workspace->isModel(),
          'roles' => array_map(function (Role $role) {
              return [
                'id' => $role->getId(),
                'name' => $role->getName(),
              ];
          }, $workspace->getRoles()->toArray()),
          'managers' => array_map(function (User $manager) {
              return [
              'id' => $manager->getId(),
              'uuid' => $manager->getGuid(),
              'username' => $manager->getUsername(),
              'lastName' => $manager->getLastName(),
              'firstName' => $manager->getFirstName(),
            ];
          }, $managers),
        ];

        return $serializedWorkspace;
    }
}
