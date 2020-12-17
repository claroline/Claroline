<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AuthenticationBundle\Security\Oauth\Hwi;

use Claroline\AuthenticationBundle\Manager\OauthManager;
use Http\Client\Common\HttpMethodsClient;
use HWI\Bundle\OAuthBundle\OAuth\RequestDataStorage\SessionStorage;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GoogleResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\LinkedinResourceOwner;
use Psr\Http\Client\ClientInterface;

class ResourceOwnerFactory
{
    private $httpClient;
    private $httpUtils;
    private $oauthManager;
    private $session;

    /**
     * @param $httpUtils
     * @param $session
     * @param ClientInterface $httpClient
     */
    public function __construct(OauthManager $oauthManager, $httpUtils, $session, HttpMethodsClient $httpClient)
    {
        $this->oauthManager = $oauthManager;
        $this->httpUtils = $httpUtils;
        $this->session = $session;
        $this->httpClient = $httpClient;
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
            $this->httpClient,
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
            $this->httpClient,
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
            $this->httpClient,
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
            $this->httpClient,
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
            $this->httpClient,
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
            $this->httpClient,
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

    public function getGenericResourceOwner()
    {
        $config = $this->oauthManager->getConfiguration('generic');
        $options = [];

        $paths = [
            'email' => $config->getPathsEmail(),
            'nickname' => $config->getPathsLogin(),
        ];

        $owner = new GenericResourceOwner(
            $this->httpClient,
            $this->httpUtils,
            [
                'client_id' => $config->getClientId(),
                'client_secret' => $config->getClientSecret(),
                'authorization_url' => $config->getAuthorizationUrl(),
                'access_token_url' => $config->getAccessTokenUrl(),
                'infos_url' => $config->getInfosUrl(),
                'scope' => $config->getScope(),
                'paths' => $paths,
                'options' => $options,
            ],
            'generic',
            new SessionStorage($this->session)
        );

        return $owner;
    }
}
