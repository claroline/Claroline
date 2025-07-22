<?php

namespace Claroline\CoreBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncLowMessageInterface;
use Claroline\CoreBundle\Entity\Organization\Organization;

class ReassignOrganization implements AsyncLowMessageInterface
{
    public function __construct(
        private readonly int $organizationId
    ) {
    }

    /**
     * Return the id of the organization to use as a replacement.
     */
    public function getOrganizationId(): int
    {
        return $this->organizationId;
    }
}
