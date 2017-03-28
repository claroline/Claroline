<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icap\OAuthBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\RefreshCacheEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Security\Authenticator;
use Claroline\CoreBundle\Manager\CacheManager;
use Claroline\CoreBundle\Manager\RegistrationManager;
use Claroline\CoreBundle\Manager\UserManager;
use Doctrine\ORM\EntityManager;
use Icap\OAuthBundle\Entity\OauthUser;
use Icap\OAuthBundle\Model\Configuration;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * @DI\Service("icap.oauth.manager")
 */
class OauthManager
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var PlatformConfigurationHandler
     */
    private $platformConfigHandler;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var RegistrationManager
     */
    private $registrationManager;

    private $authenticationHandler;

    private $authenticator;

    /**
     * @DI\InjectParams({
     *      "entityManager"         = @DI\Inject("doctrine.orm.entity_manager"),
     *      "cacheManager"          = @DI\Inject("claroline.manager.cache_manager"),
     *      "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *      "tokenStorage"          = @DI\Inject("security.token_storage"),
     *      "userManager"           = @DI\Inject("claroline.manager.user_manager"),
     *      "registrationManager"   = @DI\Inject("claroline.manager.registration_manager"),
     *      "authenticationHandler" = @DI\Inject("claroline.authentication_handler"),
     *      "authenticator"         = @DI\Inject("claroline.authenticator")
     * })
     *
     * @param EntityManager                $entityManager
     * @param CacheManager                 $cacheManager
     * @param PlatformConfigurationHandler $platformConfigHandler
     * @param TokenStorage                 $tokenStorage
     * @param UserManager                  $userManager
     * @param RegistrationManager          $registrationManager
     * @param $authenticationHandler
     * @param Authenticator $authenticator
     */
    public function __construct(
        EntityManager $entityManager,
        CacheManager $cacheManager,
        PlatformConfigurationHandler $platformConfigHandler,
        TokenStorage $tokenStorage,
        UserManager $userManager,
        registrationManager $registrationManager,
        $authenticationHandler,
        Authenticator $authenticator
    ) {
        $this->em = $entityManager;
        $this->cacheManager = $cacheManager;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->tokenStorage = $tokenStorage;
        $this->userManager = $userManager;
        $this->registrationManager = $registrationManager;
        $this->authenticationHandler = $authenticationHandler;
        $this->authenticator = $authenticator;
    }

    /**
     * @DI\Observe("refresh_cache")
     *
     * @param RefreshCacheEvent $event
     */
    public function refreshCache(RefreshCacheEvent $event)
    {
        foreach (Configuration::resourceOwners() as $resourceOwner) {
            $service = str_replace(' ', '_', strtolower($resourceOwner));
            $errors = $this->validateService(
                $service,
                $this->platformConfigHandler->getParameter($service.'_client_id'),
                $this->platformConfigHandler->getParameter($service.'_client_secret')
            );
            $event->addCacheParameter(
                "is_{$service}_available",
                (count($errors) === 0 && $this->isActive($service))
            );
        }
    }

    public function isServiceAvailable($service)
    {
        $isAvailable = $this->cacheManager->getParameter("is_{$service}_available");

        return is_null($isAvailable) ? $this->isActive($service) : !empty($isAvailable);
    }

    public function validateService($service, $appId, $secret)
    {
        if (!$appId || !$secret) {
            return ['error' => $service.'_application_validation_error'];
        }

        switch ($service) {
            case 'facebook':
                return $this->validateFacebook($appId, $secret);
            case 'twitter':
                return $this->validateTwitter($appId, $secret);
            case 'google':
            case 'linkedin':
            case 'windows_live':
            case 'office_365':
                return [];
        }
    }

    public function getConfiguration($service)
    {
        $clientId = $this->platformConfigHandler->getParameter($service.'_client_id');
        $clientSecret = $this->platformConfigHandler->getParameter($service.'_client_secret');
        $clientTenantDomain = $this->platformConfigHandler->getParameter($service.'_client_domain');
        // Compatibility with tool FormaLibreOfficeConnect
        if ($service === 'office_365') {
            if ($clientId === null) {
                $clientId = $this->platformConfigHandler->getParameter('o365_client_id');
            }
            if ($clientSecret === null) {
                $clientSecret = $this->platformConfigHandler->getParameter('o365_pw');
            }
            if ($clientTenantDomain === null) {
                $clientTenantDomain = $this->platformConfigHandler->getParameter('o365_domain');
            }
        }

        return new Configuration(
            $clientId,
            $clientSecret,
            $this->isServiceAvailable($service),
            $this->platformConfigHandler->getParameter($service.'_client_force_reauthenticate'),
            $clientTenantDomain
        );
    }

    public function getActiveServices()
    {
        $services = [];
        foreach (Configuration::resourceOwners() as $resourceOwner) {
            $service = str_replace(' ', '_', strtolower($resourceOwner));
            if ($this->isServiceAvailable($service)) {
                $services[] = $service;
            }
        }

        return $services;
    }

    public function getRegistrationForm($user)
    {
        return $this->registrationManager->getRegistrationForm($user);
    }

    public function createNewAccount(Request $request, $translator, $service)
    {
        $user = new User();
        $form = $this->registrationManager->getRegistrationForm($user);
        $form->handleRequest($request);
        $session = $request->getSession();
        if ($form->isValid()) {
            $this->registrationManager->registerNewUser($user, $form);

            $oauthUser = new OauthUser($service['name'], $service['id'], $user);
            $this->em->persist($oauthUser);
            $this->em->flush();
            $session->remove('icap.oauth.resource_owner');

            $msg = $translator->trans('account_created', [], 'platform');
            $session->getFlashBag()->add('success', $msg);

            if ($this->platformConfigHandler->getParameter('registration_mail_validation')) {
                $msg = $translator->trans('please_validate_your_account', [], 'platform');
                $session->getFlashBag()->add('success', $msg);
            }

            return $this->registrationManager->loginUser($user, $request);
        }

        return ['form' => $form->createView()];
    }

    public function linkAccount(Request $request, $service, $username = null)
    {
        $verifyPassword = false;
        $password = null;
        if ($username === null) {
            $verifyPassword = true;
            $username = $request->get('_username');
            $password = $request->get('_password');
        }
        $isAuthenticated = $this->authenticator->authenticate($username, $password, $verifyPassword);
        if ($isAuthenticated) {
            $user = $this->userManager->getUserByUsername($username);
            $oauthUser = new OauthUser($service['name'], $service['id'], $user);
            $this->em->persist($oauthUser);
            $this->em->flush();
            $request->getSession()->remove('icap.oauth.resource_owner');

            return $this->registrationManager->loginUser($user, $request);
        } else {
            return ['error' => 'login_error'];
        }
    }

    public function unlinkAccount($userId)
    {
        $this->em->getRepository("Icap\OAuthBundle\Entity\OauthUser")->unlinkOAuthUser($userId);
    }

    private function isActive($service)
    {
        $isActive = $this->platformConfigHandler->getParameter($service.'_client_active');
        // Compatibility with tool FormaLibreOfficeConnect
        if ($service === 'office_365' && $isActive === null) {
            $isActive = $this->platformConfigHandler->getParameter('o365_active');
        }

        return $isActive;
    }

    private function validateFacebook($appId, $secret)
    {
        if (!function_exists('curl_version')) {
            return ['error' => 'curl_facebook_application_validation_error'];
        }

        $secretUrl = "https://graph.facebook.com/{$appId}?fields=roles&access_token={$appId}|{$secret}";
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $secretUrl);
        curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_USERAGENT, 'ClarolineConnect');
        $json = curl_exec($curlHandle);
        curl_close($curlHandle);
        $data = json_decode($json);

        if (!$json || array_key_exists('error', $data)) {
            return ['error' => 'facebook_application_validation_error'];
        }

        return [];
    }

    private function validateTwitter($appId, $secret)
    {
        if (!function_exists('curl_version')) {
            return ['error' => 'curl_twitter_application_validation_error'];
        }

        $encoded_consumer_key = urlencode($appId);
        $encoded_consumer_secret = urlencode($secret);
        // step 1.2 - concatinate encoded consumer, a colon character and the encoded consumer secret
        $bearer_token = $encoded_consumer_key.':'.$encoded_consumer_secret;
        // step 1.3 - base64-encode bearer token
        $base64_encoded_bearer_token = base64_encode($bearer_token);
        // step 2
        $secretUrl = 'https://api.twitter.com/oauth2/token'; // url to send data to for authentication
        $headers = [
            'POST /oauth2/token HTTP/1.1',
            'Host: api.twitter.com',
            'User-Agent: ClarolineConnect Twitter Application-only OAuth App v.1',
            'Authorization: Basic '.$base64_encoded_bearer_token,
            'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
        ];
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $secretUrl);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $headers); // set custom headers
        curl_setopt($curlHandle, CURLOPT_POST, 1); // send as post
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true); // return output
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
        curl_setopt($curlHandle, CURLOPT_HEADER, 1); // send custom headers
        curl_exec($curlHandle); // execute the curl
        $respInfo = curl_getinfo($curlHandle);
        curl_close($curlHandle);
        if ($respInfo['http_code'] !== 200) {
            return ['error' => 'twitter_application_validation_error'];
        }

        return [];
    }
}
