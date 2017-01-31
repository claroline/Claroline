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

use Buzz\Client\Curl;
use HWI\Bundle\OAuthBundle\OAuth\RequestDataStorage\SessionStorage;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GoogleResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\LinkedinResourceOwner;
use Icap\OAuthBundle\Manager\OauthManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap.oauth.hwi.resource_owner_factory")
 */
class ResourceOwnerFactory
{
    private $httpUtils;
    private $oauthManager;
    private $session;

    /**
     * @DI\InjectParams({
     *     "oauthManager" = @DI\Inject("icap.oauth.manager"),
     *     "httpUtils"     = @DI\Inject("security.http_utils"),
     *     "session"       = @DI\Inject("session")
     * })
     *
     * @param OauthManager $oauthManager
     * @param $httpUtils
     * @param $session
     */
    public function __construct(OauthManager $oauthManager, $httpUtils, $session)
    {
        $this->oauthManager = $oauthManager;
        $this->httpUtils = $httpUtils;
        $this->session = $session;
    }

    public function getFacebookResourceOwner()
    {
        $config = $this->oauthManager->getConfiguration('facebook');
        $options = ['revoke_token_url' => null];
        // Force reauthentication
        if ($config->isClientForceReauthenticate()) {
            $options['auth_type'] = 'reauthenticate';
        }
        $owner = new FacebookResourceOwner(
            $this->createClientHttp(),
            $this->httpUtils,
            [
                'client_id' => $config->getClientId(),
                'client_secret' => $config->getClientSecret(),
                'infos_url' => 'https://graph.facebook.com/me?fields=id,name,first_name,last_name,email',
                'scope' => 'email',
                'options' => $options,
            ],
            'facebook',
            new SessionStorage($this->session)
        );

        return $owner;
    }

    public function getTwitterResourceOwner()
    {
        $config = $this->oauthManager->getConfiguration('twitter');
        $owner = new TwitterResourceOwner(
            $this->createClientHttp(),
            $this->httpUtils,
            [
                'client_id' => $config->getClientId(),
                'client_secret' => $config->getClientSecret(),
                'scope' => 'emailaddress',
                'options' => [
                    'force_login' => $config->isClientForceReauthenticate(),
                    'include_email' => true,
                ],
            ],
            'twitter',
            new SessionStorage($this->session)
        );

        return $owner;
    }

    public function getGoogleResourceOwner()
    {
        $config = $this->oauthManager->getConfiguration('google');
        $options = [];
        // Force re-select account
        if ($config->isClientForceReauthenticate()) {
            $options['prompt'] = 'select_account';
        }
        $owner = new GoogleResourceOwner(
            $this->createClientHttp(),
            $this->httpUtils,
            [
                'client_id' => $config->getClientId(),
                'client_secret' => $config->getClientSecret(),
                'scope' => 'email profile',
                'options' => $options,
            ],
            'google',
            new SessionStorage($this->session)
        );

        return $owner;
    }

    public function getLinkedinResourceOwner()
    {
        $config = $this->oauthManager->getConfiguration('linkedin');
        $owner = new LinkedinResourceOwner(
            $this->createClientHttp(),
            $this->httpUtils,
            [
                'client_id' => $config->getClientId(),
                'client_secret' => $config->getClientSecret(),
                'scope' => 'r_emailaddress r_basicprofile',
                'paths' => [
                    'firstname' => 'firstName',
                    'lastname' => 'lastName',
                ],
                'options' => [
                    'infos_url' => 'https://api.linkedin.com/v1/people/~:(id,first-name,last-name,formatted-name,email-address,picture-url)?format=json',
                ],
            ],
            'linkedin',
            new SessionStorage($this->session)
        );

        return $owner;
    }

    public function getWindowsLiveResourceOwner()
    {
        $config = $this->oauthManager->getConfiguration('windows_live');
        $owner = new WindowsLiveResourceOwner(
            $this->createClientHttp(),
            $this->httpUtils,
            [
                'client_id' => $config->getClientId(),
                'client_secret' => $config->getClientSecret(),
                'scope' => 'wl.basic wl.emails',
                'paths' => [
                    'firstname' => 'first_name',
                    'lastname' => 'last_name',
                ],
                'options' => [
                    'force_login' => $config->isClientForceReauthenticate(),
                ],
            ],
            'windows_live',
            new SessionStorage($this->session)
        );

        return $owner;
    }

    public function getOffice365ResourceOwner()
    {
        $config = $this->oauthManager->getConfiguration('office_365');
        $owner = new Office365ResourceOwner(
            $this->createClientHttp(),
            $this->httpUtils,
            [
                'client_id' => $config->getClientId(),
                'client_secret' => $config->getClientSecret(),
                'scope' => '',
                'options' => [
                    'force_login' => $config->isClientForceReauthenticate(),
                ],
            ],
            'office_365',
            new SessionStorage($this->session),
            $config->getClientTenantDomain(),
            $config->getClientVersion()
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
