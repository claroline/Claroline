<?php

namespace FormaLibre\PresenceBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\RoleManager;
use FormaLibre\PresenceBundle\Entity\PresenceRights;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 *@DI\Service("formalibre.manager.presence_manager")
 */
class PresenceManager
{
    private $om;
    private $rightsRepo;
    private $roleManager;
    private $authorization;
    private $presenceRepo;

    /**
     * @DI\InjectParams({
     *      "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *      "roleManager"        = @DI\Inject("claroline.manager.role_manager"),
     *      "authorization"        = @DI\Inject("security.authorization_checker")
     * })
     */
    public function __construct(ObjectManager $om, RoleManager $roleManager, AuthorizationCheckerInterface $authorization)
    {
        $this->om = $om;
        $this->rightsRepo = $om->getRepository('FormaLibrePresenceBundle:PresenceRights');
        $this->roleManager = $roleManager;
        $this->authorization = $authorization;
        $this->presenceRepo = $om->getRepository('FormaLibrePresenceBundle:Presence');
    }

    public function getAllPresenceRights()
    {
        $toflush = false;
        $allRights = $this->rightsRepo->findAll();
        $rolesPlateforme = $this->roleManager->getAllPlatformRoles();

        $existentRights = [];
        foreach ($allRights as $oneRight) {
            $role = $oneRight->getRole();
            $existentRights[$role->getId()] = $oneRight;
        }
        foreach ($rolesPlateforme as $oneRolePlateforme) {
            if (!isset($existentRights[$oneRolePlateforme->getId()])) {
                $toflush = true;

                $newRight = new PresenceRights();
                $newRight->setRole($oneRolePlateforme);
                $newRight->setMask(PresenceRights::PERSONAL_ARCHIVES);
                $this->om->persist($newRight);

                $existentRights[$oneRolePlateforme->getId()] = $newRight;
            }
        }
        if ($toflush) {
            $this->om->flush();
        }

        return $existentRights;
    }

    public function checkRights(User $user, $theRight)
    {
        if ($this->authorization->isGranted('ROLE_ADMIN')) {
            return true;
        }
        $roles = $user->getEntityRoles();
        $rights = $this->rightsRepo->findPresenceRightsByRolesAndValue($roles, $theRight);

        return count($rights) > 0;
    }

    public function getPresencesByUserAndSessions(User $user, array $sessions)
    {
        return (count($sessions) > 0) ?
            $this->presenceRepo->findPresencesByUserAndSession(
                $user,
                $sessions
            ) :
            [];
    }

    public function getPresencesByUserAndSessionAndStatusName(
        User $user,
        array $sessions,
        $statusName
    ) {
        return (count($sessions) > 0) ?
            $this->presenceRepo->findPresencesByUserAndSessionAndStatusName(
                $user,
                $sessions,
                $statusName
            ) :
            [];
    }
}
