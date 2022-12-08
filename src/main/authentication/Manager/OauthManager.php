<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AuthenticationBundle\Manager;

use Claroline\AppBundle\Event\Platform\RefreshCacheEvent;
use Claroline\AppBundle\Manager\CacheManager;
use Claroline\AuthenticationBundle\Configuration\OauthConfiguration;
use Claroline\AuthenticationBundle\Entity\OauthUser;
use Claroline\AuthenticationBundle\Repository\OauthUserRepository;
use Claroline\AuthenticationBundle\Security\Authentication\Authenticator;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class OauthManager
{
    /** @var EntityManager */
    private $em;

    /** @var CacheManager */
    private $cacheManager;

    /** @var PlatformConfigurationHandler */
    private $platformConfigHandler;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var Authenticator */
    private $authenticator;

    /** @var OauthUserRepository */
    private $oauthUserRepository;

    public function __construct(
        EntityManager $entityManager,
        CacheManager $cacheManager,
        PlatformConfigurationHandler $platformConfigHandler,
        TokenStorageInterface $tokenStorage,
        Authenticator $authenticator
    ) {
        $this->em = $entityManager;
        $this->cacheManager = $cacheManager;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->tokenStorage = $tokenStorage;
        $this->authenticator = $authenticator;
        $this->oauthUserRepository = $entityManager->getRepository(OauthUser::class);
    }

    public function refreshCache(RefreshCacheEvent $event)
    {
        foreach (OauthConfiguration::resourceOwners() as $resourceOwner) {
            $service = str_replace(' ', '_', strtolower($resourceOwner));
            $errors = $this->validateService(
                $service,
                $this->platformConfigHandler->getParameter('external_authentication.'.$service.'.client_id'),
                $this->platformConfigHandler->getParameter('external_authentication.'.$service.'.client_secret')
            );
            $event->addCacheParameter(
                "is_{$service}_available",
                (0 === count($errors) && $this->isActive($service))
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
            case 'generic':
                return [];
        }

        return null;
    }

    public function getConfiguration($service)
    {
        $clientId = $this->platformConfigHandler->getParameter('external_authentication.'.$service.'.client_id');
        $clientSecret = $this->platformConfigHandler->getParameter('external_authentication.'.$service.'.client_secret');
        $clientTenantDomain = $this->platformConfigHandler->getParameter('external_authentication.'.$service.'.client_domain');

        $config = new OauthConfiguration(
            $clientId,
            $clientSecret,
            $this->isServiceAvailable($service),
            $this->platformConfigHandler->getParameter('external_authentication.'.$service.'.client_force_reauthenticate'),
            $clientTenantDomain
        );

        if ('generic' === $service) {
            $config->setAuthorizationUrl($this->platformConfigHandler->getParameter('external_authentication.'.$service.'.authorization_url'));
            $config->setAccessTokenUrl($this->platformConfigHandler->getParameter('external_authentication.'.$service.'.access_token_url'));
            $config->setInfosUrl($this->platformConfigHandler->getParameter('external_authentication.'.$service.'.infos_url'));
            $config->setScope($this->platformConfigHandler->getParameter('external_authentication.'.$service.'.scope'));
            $config->setPathsLogin($this->platformConfigHandler->getParameter('external_authentication.'.$service.'.paths_login'));
            $config->setPathsEmail($this->platformConfigHandler->getParameter('external_authentication.'.$service.'.paths_email'));
            $config->setDisplayName($this->platformConfigHandler->getParameter('external_authentication.'.$service.'.display_name'));
        }

        return $config;
    }

    public function getActiveServices()
    {
        $services = [];
        foreach (OauthConfiguration::resourceOwners() as $resourceOwner) {
            $service = str_replace(' ', '_', strtolower($resourceOwner));
            if ($this->isServiceAvailable($service)) {
                $services[] = array_merge([
                    'service' => $service,
                ], $this->platformConfigHandler->getParameter('external_authentication.'.$service));
            }
        }

        return $services;
    }

    public function linkAccount(Request $request, $service, $username = null)
    {
        $verifyPassword = false;
        $password = null;
        if (null === $username) {
            $verifyPassword = true;
            $username = $request->get('_username');
            $password = $request->get('_password');
        }

        $authenticatedUser = $this->authenticator->authenticate($username, $password, $verifyPassword);
        if ($authenticatedUser) {
            $oauthUser = new OauthUser($service['name'], $service['id'], $authenticatedUser);
            $this->em->persist($oauthUser);
            $this->em->flush();
            $request->getSession()->remove('claroline.oauth.resource_owner');

            return $this->authenticator->login($authenticatedUser, $request);
        } else {
            return new JsonResponse(['error' => 'login_error'], 400);
        }
    }

    public function unlinkAccount($userId)
    {
        $this->oauthUserRepository->unlinkOAuthUser($userId);
    }

    private function isActive($service)
    {
        return $this->platformConfigHandler->getParameter('external_authentication.'.$service.'.client_active');
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

        if (!$json || isset($data->error)) {
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
        if (200 !== $respInfo['http_code']) {
            return ['error' => 'twitter_application_validation_error'];
        }

        return [];
    }
}
