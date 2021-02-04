<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\InstallationBundle\Updater;

use Claroline\AppBundle\Log\LoggableTrait;
use Psr\Log\LoggerAwareInterface;

abstract class Updater implements LoggerAwareInterface
{
    use LoggableTrait;

    public function preUpdate()
    {
    }

    public function postUpdate()
    {
    }
}
