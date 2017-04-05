<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 3/10/17
 */

namespace Claroline\CasBundle\Library\Configuration;

use Claroline\CasBundle\Library\Sso\CasFactory;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class CasServerConfigurationFactory.
 *
 * @DI\Service("claroline.factory.cas_configuration")
 */
class CasServerConfigurationFactory
{
    /** @var PlatformConfigurationHandler */
    private $platformConfigHandler;
    /** @var CasServerConfiguration */
    private $casServerConfiguration;
    /** @var CasFactory */
    private $casFactory;

    /**
     * CasServerConfigurationFactory constructor.
     *
     * @DI\InjectParams({
     *     "platformConfigHandler"  = @DI\Inject("claroline.config.platform_config_handler"),
     *     "casFactory"             = @DI\Inject("be_simple.sso_auth.factory")
     * })
     *
     * @param PlatformConfigurationHandler $platformConfigHandler
     * @param CasFactory                   $casFactory
     */
    public function __construct(
        PlatformConfigurationHandler $platformConfigHandler,
        CasFactory $casFactory
    ) {
        $this->platformConfigHandler = $platformConfigHandler;
        $this->platformConfigHandler->addDefaultParameters(new CasServerConfiguration());
        $this->casServerConfiguration = $this->readCasServerConfiguration();
        $this->casFactory = $casFactory;
    }

    /**
     * @return CasServerConfiguration
     */
    public function getCasConfiguration()
    {
        return $this->casServerConfiguration;
    }

    /**
     * @param CasServerConfiguration $casServerConfiguration
     *
     * @return CasServerConfiguration
     */
    public function setCasConfiguration(CasServerConfiguration $casServerConfiguration)
    {
        $this->casServerConfiguration = $casServerConfiguration;
        $this->platformConfigHandler->setParameters($casServerConfiguration->getParameters());

        return $this->casServerConfiguration;
    }

    /**
     * @return bool
     */
    public function isCasActive()
    {
        return $this->casServerConfiguration->isActive();
    }

    /**
     * @return bool
     */
    public function isCasOverrideLogin()
    {
        return $this->casServerConfiguration->isOverrideLogin();
    }

    public function getCasConfigurationArray()
    {
        return $this->casServerConfiguration->getConfigurationArray();
    }

    public function getCasConfigurationForServer()
    {
        return $this->casServerConfiguration->getConfigurationArray()['server'];
    }

    public function updateCasServerConfiguration()
    {
        $this->casFactory->updateServerConfig('cas_sso', $this->getCasConfigurationForServer());
    }

    /**
     * @return CasServerConfiguration
     */
    private function readCasServerConfiguration()
    {
        return new CasServerConfiguration(
            $this->platformConfigHandler->getParameter('cas_server_login_active'),
            $this->platformConfigHandler->getParameter('cas_server_login_url'),
            $this->platformConfigHandler->getParameter('cas_server_logout_url'),
            $this->platformConfigHandler->getParameter('cas_server_validation_url'),
            $this->platformConfigHandler->getParameter('cas_server_login_option'),
            $this->platformConfigHandler->getParameter('cas_server_login_name'),
            $this->platformConfigHandler->getParameter('login_target_route')
        );
    }
}
