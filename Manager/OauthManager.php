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
use Claroline\CoreBundle\Form\BaseProfileType;
use Claroline\CoreBundle\Library\Security\Authenticator;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\TermsOfServiceManager;
use Claroline\CoreBundle\Manager\UserManager;
use Doctrine\ORM\EntityManager;
use Icap\OAuthBundle\Entity\OauthUser;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Event\RefreshCacheEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\CacheManager;
use Icap\OAuthBundle\Model\Configuration;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

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
     * @var LocaleManager
     */
    private $localeManager;

    /**
     * @var TermsOfServiceManager
     */
    private $termsManager;

    /**
     * @var FacetManager
     */
    private $facetManager;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var UserManager
     */
    private $userManager;


    private $authenticationHandler;

    private $authenticator;

    /**
     * @DI\InjectParams({
     *      "entityManager"         = @DI\Inject("doctrine.orm.entity_manager"),
     *      "cacheManager"          = @DI\Inject("claroline.manager.cache_manager"),
     *      "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *      "localeManager"         = @DI\Inject("claroline.common.locale_manager"),
     *      "termsManager"          = @DI\Inject("claroline.common.terms_of_service_manager"),
     *      "facetManager"          = @DI\Inject("claroline.manager.facet_manager"),
     *      "formFactory"           = @DI\Inject("form.factory"),
     *      "tokenStorage"          = @DI\Inject("security.token_storage"),
     *      "userManager"           = @DI\Inject("claroline.manager.user_manager"),
     *      "authenticationHandler" = @DI\Inject("claroline.authentication_handler"),
     *      "authenticator"         = @DI\Inject("claroline.authenticator")
     * })
     *
     * @param EntityManager $entityManager
     * @param CacheManager $cacheManager
     * @param PlatformConfigurationHandler $platformConfigHandler
     * @param LocaleManager $localeManager
     * @param TermsOfServiceManager $termsManager
     * @param FacetManager $facetManager
     * @param FormFactory $formFactory
     * @param TokenStorage $tokenStorage
     * @param UserManager $userManager
     * @param $authenticationHandler
     * @param Authenticator $authenticator
     */
    public function __construct(
        EntityManager $entityManager,
        CacheManager $cacheManager,
        PlatformConfigurationHandler $platformConfigHandler,
        LocaleManager $localeManager,
        TermsOfServiceManager $termsManager,
        FacetManager $facetManager,
        FormFactory $formFactory,
        TokenStorage $tokenStorage,
        UserManager $userManager,
        $authenticationHandler,
        Authenticator $authenticator
    )
    {
        $this->em                       = $entityManager;
        $this->cacheManager             = $cacheManager;
        $this->platformConfigHandler    = $platformConfigHandler;
        $this->localeManager            = $localeManager;
        $this->termsManager             = $termsManager;
        $this->facetManager             = $facetManager;
        $this->formFactory              = $formFactory;
        $this->tokenStorage             = $tokenStorage;
        $this->userManager              = $userManager;
        $this->authenticationHandler    = $authenticationHandler;
        $this->authenticator            = $authenticator;
    }

    /**
     * @DI\Observe("refresh_cache")
     * @param RefreshCacheEvent $event
     */
    public function refreshCache(RefreshCacheEvent $event)
    {
        foreach (Configuration::resourceOwners() as $resourceOwner) {
            $service = strtolower($resourceOwner);
            $errors = $this->validateService(
                $service,
                $this->platformConfigHandler->getParameter($service.'_client_id'),
                $this->platformConfigHandler->getParameter($service.'_client_secret')
            );
            $event->addCacheParameter(
                "is_{$service}_available",
                (count($errors) === 0 and $this->platformConfigHandler->getParameter($service.'_client_active'))
            );
        }
    }

    public function isServiceAvailable($service)
    {
        return $this->cacheManager->getParameter("is_{$service}_available");
    }

    public function validateService($service, $appId, $secret)
    {
        if (!$appId || !$secret) {
            return array('error' => $service.'_application_validation_error');
        }

        return call_user_func(array($this, "validate".ucfirst($service)), $appId, $secret);
    }

    public function getConfiguration($service)
    {
        return new Configuration(
            $this->platformConfigHandler->getParameter($service.'_client_id'),
            $this->platformConfigHandler->getParameter($service.'_client_secret'),
            $this->platformConfigHandler->getParameter($service.'_client_active')
        );
    }

    public function isActive($service)
    {
        return $this->platformConfigHandler->getParameter($service.'_client_active');
    }

    public function getActiveServices()
    {
        $services = array();
        foreach (Configuration::resourceOwners() as $resourceOwner) {
            $service = strtolower($resourceOwner);
            if ($this->isActive($service)) {
                $services[] = $service;
            }
        }

        return $services;
    }

    public function getRegistrationForm($user, $translator)
    {
        $facets = $this->facetManager->findForcedRegistrationFacet();
        $form = $this->formFactory->create(
            new BaseProfileType($this->localeManager, $this->termsManager, $translator, $facets),
            $user
        );

        return $form;
    }

    public function createNewAccount(Request $request, $translator, $service)
    {
        $user = new User();
        $form = $this->getRegistrationForm($user, $translator);
        $form->handleRequest($request);
        $session = $request->getSession();
        if ($form->isValid()) {
            $user = $this->userManager->createUser($user);

            $oauthUser = new OauthUser($service['name'], $service['id'], $user);
            $this->em->persist($oauthUser);
            $this->em->flush();
            $session->remove('icap.oauth.resource_owner');

            $facets = $this->facetManager->findForcedRegistrationFacet();
            //then we adds the differents value for facets.
            foreach ($facets as $facet) {
                foreach ($facet->getPanelFacets() as $panel) {
                    foreach ($panel->getFieldsFacet() as $field) {
                        $this->facetManager->setFieldValue(
                            $user,
                            $field,
                            $form->get($field->getName())->getData(),
                            true
                        );
                    }
                }
            }

            $msg = $translator->trans('account_created', array(), 'platform');
            $session->getFlashBag()->add('success', $msg);

            if ($this->platformConfigHandler->getParameter('registration_mail_validation')) {
                $msg = $translator->trans('please_validate_your_account', array(), 'platform');
                $session->getFlashBag()->add('success', $msg);
            }

            return $this->loginUser($user, $request);
        }

        return array('form' => $form->createView());
    }

    public function linkAccount(Request $request, $service)
    {
        $username = $request->get("_username");
        $password = $request->get("_password");
        $isAuthenticated = $this->authenticator->authenticate($username, $password);
        if ($isAuthenticated) {
            $user = $this->userManager->getUserByUsername($username);
            $oauthUser = new OauthUser($service['name'], $service['id'], $user);
            $this->em->persist($oauthUser);
            $this->em->flush();
            $request->getSession()->remove('icap.oauth.resource_owner');

            return $this->loginUser($user, $request);
        } else {
            return array('error' => 'login_error');
        }
    }

    private function loginUser($user, $request)
    {
        //this is bad but I don't know any other way (yet)
        $providerKey = 'main';
        $token = new UsernamePasswordToken($user, $user->getPassword(), $providerKey, $user->getRoles());
        $this->tokenStorage->setToken($token);
        //a bit hacky I know ~
        return $this->authenticationHandler->onAuthenticationSuccess($request, $token);
    }

    private function validateFacebook($appId, $secret)
    {
        if (!function_exists('curl_version')) {
            return array('error' => 'curl_facebook_application_validation_error');
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
            return array('error' => 'facebook_application_validation_error');
        }

        return array();
    }

    private function validateTwitter($appId, $secret)
    {
        if (!function_exists('curl_version')) {
            return array('error' => 'curl_twitter_application_validation_error');
        }

        $encoded_consumer_key = urlencode($appId);
        $encoded_consumer_secret = urlencode($secret);
        // step 1.2 - concatinate encoded consumer, a colon character and the encoded consumer secret
        $bearer_token = $encoded_consumer_key.':'.$encoded_consumer_secret;
        // step 1.3 - base64-encode bearer token
        $base64_encoded_bearer_token = base64_encode($bearer_token);
        // step 2
        $secretUrl = "https://api.twitter.com/oauth2/token"; // url to send data to for authentication
        $headers = array(
            "POST /oauth2/token HTTP/1.1",
            "Host: api.twitter.com",
            "User-Agent: ClarolineConnect Twitter Application-only OAuth App v.1",
            "Authorization: Basic ".$base64_encoded_bearer_token,
            "Content-Type: application/x-www-form-urlencoded;charset=UTF-8"
        );
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $secretUrl);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $headers); // set custom headers
        curl_setopt($curlHandle, CURLOPT_POST, 1); // send as post
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true); // return output
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        curl_setopt($curlHandle, CURLOPT_HEADER, 1); // send custom headers
        $retrievedhtml = curl_exec ($curlHandle); // execute the curl
        $respInfo = curl_getinfo($curlHandle);
        curl_close($curlHandle);
        if ($respInfo['http_code'] !== 200) {
            return array('error' => 'twitter_application_validation_error');
        }

        return array();
    }

    private function validateGoogle($appId, $secret)
    {
        return array();
    }

    private function validateLinkedin($appId, $secret)
    {
        return array();
    }
}
