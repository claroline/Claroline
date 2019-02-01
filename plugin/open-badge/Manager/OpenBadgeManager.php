<?php

namespace Claroline\OpenBadgeBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @DI\Service("claroline.manager.open_badge_manager")
 */
class OpenBadgeManager
{
    /**
     * Crud constructor.
     *
     * @DI\InjectParams({
     *     "om"               = @DI\Inject("claroline.persistence.object_manager"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om, WorkspaceManager $workspaceManager)
    {
        $this->om = $om;
        $this->workspaceManager = $workspaceManager;
    }

    public function addAssertion(BadgeClass $badge, User $user)
    {
        $assertion = $this->om->getRepository(Assertion::class)->findOneBy(['badge' => $badge, 'recipient' => $user]);

        if (!$assertion) {
            $assertion = new Assertion();
            $assertion->setBadge($badge);
            $assertion->setRecipient($user);
            $assertion->setImage($badge->getImage());
        }

        $assertion->setRevoked(false);

        $this->om->persist($assertion);
        $this->om->flush();
    }

    public function revokeAssertion(Assertion $assertion)
    {
        $assertion->setRevoked(true);
        $this->om->persist($assertion);
        $this->om->flush();
    }

    public function isAllowedBadgeManagement(TokenInterface $token, BadgeClass $badge)
    {
        $issuingMode = $badge->getIssuingMode();
        $user = $token->getUser();

        foreach ($issuingMode as $mode) {
            switch ($mode) {
              case BadgeClass::ISSUING_MODE_ORGANIZATION:
                $organization = $badge->getIssuer();

                foreach ($user->getAdministratedOrganizations() as $orga) {
                    if ($orga->getId() === $organization->getId()) {
                        return true;
                    }
                }
                break;
              case BadgeClass::ISSUING_MODE_USER:
                foreach ($badge->getAllowedIssuers() as $issuer) {
                    if ($issuer->getId() === $user->getId()) {
                        return true;
                    }
                }
                break;
              case BadgeClass::ISSUING_MODE_GROUP:
                foreach ($badge->getAllowedIssuersGroups() as $issuer) {
                    foreach ($user->getGroups() as $group) {
                        if ($issuer->getId() === $group->getId()) {
                            return true;
                        }
                    }
                }
                break;
              case BadgeClass::ISSUING_MODE_WORKSPACE:
                $workspace = $badge->getWorkspace();
                if ($workspace) {
                    $managers = $this->workspaceManager->getManagers($workspace);
                    foreach ($managers as $manager) {
                        if ($manager->getId() === $user->getId() || $user->getId() === $workspace->getCreator()->getId()) {
                            return true;
                        }
                    }
                }
                break;
              case BadgeClass::ISSUING_MODE_AUTO:
                break;
            }
        }

        return false;
    }
}
