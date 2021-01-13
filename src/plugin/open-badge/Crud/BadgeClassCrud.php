<?php

namespace Claroline\OpenBadgeBundle\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BadgeClassCrud
{
    /** @var ParametersSerializer */
    private $parametersSerializer;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ParametersSerializer $parametersSerializer
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->parametersSerializer = $parametersSerializer;
    }

    public function preCreate(CreateEvent $event)
    {
        /** @var BadgeClass $badge */
        $badge = $event->getObject();
        if (empty($badge->getIssuer())) {
            $organization = null;
            if ($badge->getWorkspace()) {
                if (count($badge->getWorkspace()->getOrganizations()) > 1) {
                    $organization = $badge->getWorkspace()->getOrganizations()[0];
                }
            } else if ($this->tokenStorage->getToken()->getUser() instanceof User) {
                $organization = $this->tokenStorage->getToken()->getUser()->getMainOrganization();
            }

            if (!empty($organization)) {
                $badge->setIssuer($organization);
            }
        }

        if ($badge->getWorkspace()) {
            $badge->setEnabled($this->parametersSerializer->serialize()['badges']['enable_default']);
        }
    }
}
