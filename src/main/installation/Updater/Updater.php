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

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

abstract class Updater implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function preUpdate(): void
    {
    }

    public function postUpdate(): void
    {
    }
}
