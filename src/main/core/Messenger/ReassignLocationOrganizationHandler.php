<?php

namespace Claroline\CoreBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Location;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Messenger\Message\ReassignOrganization;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Assign badges with no organization to the default one.
 */
#[AsMessageHandler]
class ReassignLocationOrganizationHandler
{
    public function __construct(
        private readonly ObjectManager $om
    ) {
    }

    public function __invoke(ReassignOrganization $reassignOrganization): void
    {
        $organization = $this->om->getRepository(Organization::class)->find($reassignOrganization->getOrganizationId());
        $locations = $this->om->getRepository(Location::class)->findWithNoOrganization();

        foreach ($locations as $location) {
            $location->addOrganization($organization);
            $this->om->persist($location);
        }

        $this->om->flush();
    }
}
