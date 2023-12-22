<?php

namespace Claroline\AppBundle\Component\Rule;

use Claroline\AppBundle\Component\ComponentInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

interface RuleInterface extends EventSubscriberInterface, ComponentInterface
{
    public function check(): bool;

    public function configure(): ?array;
}
