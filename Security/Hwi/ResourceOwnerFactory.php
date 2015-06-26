<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icap\OAuthBundle\Security\Hwi;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\FacebookResourceOwner;
use Buzz\Client\Curl;
use HWI\Bundle\OAuthBundle\OAuth\RequestDataStorage\SessionStorage;

/**
 * @DI\Service("icap.oauth.hwi.facebook_owner_factory")
 */
class ResourceOwnerFactory
{
    private $httpUtils;
    private $configHandler;
    private $session;

    /**
     * @DI\InjectParams({
     *     "configHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "httpUtils"     = @DI\Inject("security.http_utils"),
     *     "session"       = @DI\Inject("session")
     * })
     */
    public function __construct(PlatformConfigurationHandler $configHandler, $httpUtils, $session)
    {
        $this->configHandler = $configHandler;
        $this->httpUtils     = $httpUtils;
        $this->session       = $session;
    }

    public function getFacebookResourceOwner()
    {
        $httpClient = new Curl();
        $httpClient->setVerifyPeer(true);
        $httpClient->setTimeout(10);
        $httpClient->setMaxRedirects(5);
        $httpClient->setIgnoreErrors(true);

        $owner = new FacebookResourceOwner(
            $httpClient,
            $this->httpUtils,
            array(
                'client_id' => $this->configHandler->getParameter('facebook_client_id'),
                'client_secret' => $this->configHandler->getParameter('facebook_client_secret'),
                'scope' => 'email',
                'paths' => array(),
                'options' => array()
            ),
            'facebook',
            new SessionStorage($this->session)
        );

        return $owner;

    }
}
