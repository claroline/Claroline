<?php

namespace Claroline\LogBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncMessageInterface;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

class CreateFunctionalLog implements AsyncMessageInterface
{
    /** @var \DateTimeInterface */
    private $date;
    /** @var string */
    private $action;
    /** @var string */
    private $details;
    /** @var User */
    private $doer;
    /** @var Workspace|null */
    private $workspace;
    /** @var ResourceNode|null */
    private $resourceNode;

    public function __construct(
        \DateTimeInterface $date,
        string $action,
        string $details,
        User $doer,
        ?Workspace $workspace = null,
        ?ResourceNode $resourceNode = null
    ) {
        $this->date = $date;
        $this->action = $action;
        $this->details = $details;
        $this->doer = $doer;
        $this->workspace = $workspace;
        $this->resourceNode = $resourceNode;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getDetails(): string
    {
        return $this->details;
    }

    public function getDoer(): User
    {
        return $this->doer;
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function getResourceNode(): ?ResourceNode
    {
        return $this->resourceNode;
    }
}
