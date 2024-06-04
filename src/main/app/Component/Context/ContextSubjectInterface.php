<?php

namespace Claroline\AppBundle\Component\Context;

use Claroline\AppBundle\Entity\IdentifiableInterface;

interface ContextSubjectInterface extends IdentifiableInterface
{
    /**
     * @deprecated use IdentifiableInterface::getUuid()
     */
    public function getContextIdentifier(): string;
}
