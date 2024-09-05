<?php

namespace Claroline\AppBundle\Entity;

/**
 * CrudEntityInterface is the base interface to implement in all the entities managed by the Crud component.
 */
interface CrudEntityInterface extends IdentifiableInterface
{
    // public function getMimeType(): string;

    /**
     * The list of identifiers which can be used to retrieve the entity in the API.
     * NB. No need to declare the UUID prop which is the only required id.
     */
    public static function getIdentifiers(): array;
}
