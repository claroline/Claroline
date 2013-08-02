<?php

namespace Claroline\MigrationBundle\Migrator;

class InvalidDirectionException extends \Exception
{
    public function __construct($direction)
    {
        $action = $direction === Migrator::DIRECTION_UP ? 'upgrade' : 'downgrade';
        $position = $direction === Migrator::DIRECTION_UP ? 'below' : 'above';
        parent::__construct("Cannot {$action} to a version {$position} the current one");
    }
}
