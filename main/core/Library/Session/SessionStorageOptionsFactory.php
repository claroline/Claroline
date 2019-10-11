<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Session;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;

class SessionStorageOptionsFactory
{
    private $configHandler;
    private $defaultOptions;

    public function __construct(PlatformConfigurationHandler $configHandler, array $defaultOptions)
    {
        $this->configHandler = $configHandler;
        $this->defaultOptions = $defaultOptions;
    }

    public function getOptions()
    {
        return array_merge(
            $this->defaultOptions,
            ['cookie_lifetime' => $this->configHandler->getParameter('cookie_lifetime')]
        );
    }
}
