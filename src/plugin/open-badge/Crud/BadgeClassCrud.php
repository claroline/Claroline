<?php

namespace Claroline\OpenBadgeBundle\Crud;

use Claroline\AppBundle\Event\Crud\CrudEvent;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\Organization\OrganizationManager;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BadgeClassCrud
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var OrganizationManager */
    private $organizationManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        OrganizationManager $organizationManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->organizationManager = $organizationManager;
    }

    /**
     * Auto link badge to the correct Organization if the user has not selected one.
     */
    public function checkOrganization(CrudEvent $event)
    {
        /** @var BadgeClass $badge */
        $badge = $event->getObject();

        if (empty($badge->getIssuer())) {
            $organization = null;
            if ($badge->getWorkspace()) {
                if (count($badge->getWorkspace()->getOrganizations()) > 1) {
                    $organization = $badge->getWorkspace()->getOrganizations()[0];
                }
            } elseif ($this->tokenStorage->getToken()->getUser() instanceof User) {
                $organization = $this->tokenStorage->getToken()->getUser()->getMainOrganization();
            }

            if (!empty($organization)) {
                $badge->setIssuer($organization);
            } else {
                $badge->setIssuer($this->organizationManager->getDefault(true));
            }
        }
    }
}
