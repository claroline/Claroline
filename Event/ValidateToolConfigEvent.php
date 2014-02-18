<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Event\DataConveyorEventInterface;

class ValidateToolConfigEvent extends Event implements DataConveyorEventInterface
{
    private $isPopulated = false;
    private $configurationBuilder;

    public function getConfigurationBuilder()
    {
        return $this->configurationBuilder;
    }

    public function setConfigurationBuilder($configurationBuilder)
    {
        $this->isPopulated = true;
        $this->configurationBuilder = $configurationBuilder;
    }

    public function isPopulated()
    {
        return $this->isPopulated;
    }
} 