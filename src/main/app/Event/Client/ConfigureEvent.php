<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Event\Client;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * An event dispatched when the application UI is rendered
 * giving the chance to plugins to inject some custom parameters which will be available in the javascript client.
 */
class ConfigureEvent extends Event
{
    public function __construct(
        private array $parameters
    ) {
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = array_merge_recursive($this->parameters, $parameters);
    }
}
