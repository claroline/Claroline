<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MigrationBundle\Migrator;

class InvalidDirectionException extends \Exception
{
    public function __construct($direction)
    {
        $action = Migrator::DIRECTION_UP === $direction ? 'upgrade' : 'downgrade';
        $position = Migrator::DIRECTION_UP === $direction ? 'below' : 'above';
        parent::__construct("Cannot {$action} to a version {$position} the current one");
    }
}
