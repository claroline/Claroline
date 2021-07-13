<?php

namespace Claroline\LogBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncMessageInterface;
use Claroline\CoreBundle\Entity\User;

class CreateSecurityLog implements AsyncMessageInterface
{
    /** @var string */
    private $action;
    /** @var string */
    private $details;
    /** @var User */
    private $doer;
    /** @var User */
    private $target;
    /** @var string */
    private $doerIp;
    /** @var string|null */
    private $doerCountry;
    /** @var string|null */
    private $doerCity;

    public function __construct(
        string $action,
        string $details,
        User $doer,
        User $target,
        string $doerIp,
        ?string $doerCountry = null,
        ?string $doerCity = null
    ) {
        $this->action = $action;
        $this->details = $details;
        $this->doer = $doer;
        $this->target = $target;
        $this->doerIp = $doerIp;
        $this->doerCountry = $doerCountry;
        $this->doerCity = $doerCity;
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

    public function getTarget(): User
    {
        return $this->target;
    }

    public function getDoerIp(): string
    {
        return $this->doerIp;
    }

    public function getDoerCountry(): ?string
    {
        return $this->doerCountry;
    }

    public function getDoerCity(): ?string
    {
        return $this->doerCity;
    }
}
