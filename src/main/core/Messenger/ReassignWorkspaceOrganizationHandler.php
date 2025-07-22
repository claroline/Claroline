<?php

namespace Claroline\CoreBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Messenger\Message\ReassignOrganization;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Assign workspaces with no organization to the default one.
 */
#[AsMessageHandler]
class ReassignWorkspaceOrganizationHandler
{
    public function __construct(
        private readonly ObjectManager $om
    ) {
    }

    public function __invoke(ReassignOrganization $reassignOrganization): void
    {
        $organization = $this->om->getRepository(Organization::class)->find($reassignOrganization->getOrganizationId());
        $workspaces = $this->om->getRepository(Workspace::class)->findWithNoOrganization();

        foreach ($workspaces as $workspace) {
            $workspace->addOrganization($organization);
            $this->om->persist($workspace);
        }

        $this->om->flush();
    }
}
