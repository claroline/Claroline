<?php

namespace Claroline\AppBundle\Entity;

/**
 * CrudEntityInterface is the base interface to implement in all the entities managed by the Crud component.
 */
interface CrudEntityInterface extends IdentifiableInterface
{
    public function getName(): string;
}
