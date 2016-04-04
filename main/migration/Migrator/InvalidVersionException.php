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

class InvalidVersionException extends \Exception
{
    private $usageMessage;

    public function __construct($version)
    {
        $message = 'Version must be either a numeric string or a Migrator::VERSION_* class constant';
        $this->usageMessage = sprintf(
            'Invalid target version "%s": version must be either "%s", "%s" or an explicit version number',
            $version,
            Migrator::VERSION_NEAREST,
            Migrator::VERSION_FARTHEST
        );
        parent::__construct($message);
    }

    public function getUsageMessage()
    {
        return $this->usageMessage;
    }
}