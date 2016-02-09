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
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GoogleResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\LinkedinResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\TwitterResourceOwner;
use JMS\DiExtraBundle\Annotation as DI;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\FacebookResourceOwner;
use Buzz\Client\Curl;
use HWI\Bundle\OAuthBundle\OAuth\RequestDataStorage\SessionStorage;

/**
 * @DI\Service("icap.oauth.hwi.resource_owner_factory")
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
        $owner = new FacebookResourceOwner(
            $this->createClientHttp(),
            $this->httpUtils,
            array(
                'client_id' => $this->configHandler->getParameter('facebook_client_id'),
                'client_secret' => $this->configHandler->getParameter('facebook_client_secret'),
                'infos_url' => "https://graph.facebook.com/me?fields=id,name,first_name,last_name,email",
                'scope' => 'email',
                'paths' => array(
                    'email' => 'email',
                    'firstname' => 'first_name',
                    'lastname' => 'last_name',
                    'nickname' => 'name'
                ),
                'options' => array()
            ),
            'facebook',
            new SessionStorage($this->session)
        );

        return $owner;
    }

    public function getTwitterResourceOwner()
    {
        $owner = new TwitterResourceOwner(
            $this->createClientHttp(),
            $this->httpUtils,
            array(
                'client_id' => $this->configHandler->getParameter('twitter_client_id'),
                'client_secret' => $this->configHandler->getParameter('twitter_client_secret'),
                'scope' => 'emailaddress',
                'paths' => array(),
                'options' => array()
            ),
            'twitter',
            new SessionStorage($this->session)
        );

        return $owner;
    }

    public function getGoogleResourceOwner()
    {
        $owner = new GoogleResourceOwner(
            $this->createClientHttp(),
            $this->httpUtils,
            array(
                'client_id' => $this->configHandler->getParameter('google_client_id'),
                'client_secret' => $this->configHandler->getParameter('google_client_secret'),
                'scope' => 'email profile',
                'paths' => array(
                    'firstname' => 'given_name',
                    'lastname'  => 'family_name'
                ),
                'options' => array()
            ),
            'google',
            new SessionStorage($this->session)
        );

        return $owner;
    }

    public function getLinkedinResourceOwner()
    {
        $owner = new LinkedinResourceOwner(
            $this->createClientHttp(),
            $this->httpUtils,
            array(
                'client_id' => $this->configHandler->getParameter('linkedin_client_id'),
                'client_secret' => $this->configHandler->getParameter('linkedin_client_secret'),
                'scope' => 'r_emailaddress r_basicprofile',
                'paths' => array(
                    'firstname' => 'firstName',
                    'lastname'  => 'lastName'
                ),
                'options' => array(
                    'infos_url' => 'https://api.linkedin.com/v1/people/~:(id,first-name,last-name,formatted-name,email-address,picture-url)?format=json'
                )
            ),
            'linkedin',
            new SessionStorage($this->session)
        );

        return $owner;
    }

    private function createClientHttp()
    {
        $httpClient = new Curl();
        $httpClient->setVerifyPeer(true);
        $httpClient->setTimeout(10);
        $httpClient->setMaxRedirects(5);
        $httpClient->setIgnoreErrors(true);

        return $httpClient;
    }
}
