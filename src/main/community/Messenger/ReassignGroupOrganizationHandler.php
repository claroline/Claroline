<?php

namespace Claroline\CommunityBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Messenger\Message\ReassignOrganization;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Assign groups with no organization to the default one.
 */
#[AsMessageHandler]
class ReassignGroupOrganizationHandler
{
    public function __construct(
        private readonly ObjectManager $om
    ) {
    }

    public function __invoke(ReassignOrganization $reassignOrganization): void
    {
        $organization = $this->om->getRepository(Organization::class)->find($reassignOrganization->getOrganizationId());
        $groups = $this->om->getRepository(Group::class)->findBy(['organization' => null]);

        foreach ($groups as $group) {
            $group->setOrganization($organization);
            $this->om->persist($group);
        }

        $this->om->flush();
    }
}
