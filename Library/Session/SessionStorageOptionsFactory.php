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
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.session.storage_options_factory")
 */
class SessionStorageOptionsFactory
{
    private $configHandler;
    private $defaultOptions;

    /**
     * @DI\InjectParams({
     *     "configHandler"  = @DI\Inject("claroline.config.platform_config_handler"),
     *     "defaultOptions" = @DI\Inject("%session.storage.options%")
     * })
     */
    public function __construct(PlatformConfigurationHandler $configHandler, array $defaultOptions)
    {
        $this->configHandler = $configHandler;
        $this->defaultOptions = $defaultOptions;
    }

    public function getOptions()
    {
        return array_merge(
            $this->defaultOptions,
            array('cookie_lifetime' => $this->configHandler->getParameter('cookie_lifetime'))
        );
    }
}
