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

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Event\RefreshCacheEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\CacheManager;
use Icap\OAuthBundle\Model\Configuration;

/**
 * @DI\Service("icap.oauth.manager.facebook")
 */
class FacebookManager
{
    private $cacheManager;
    private $platformConfigHandler;

    /**
     * @DI\InjectParams({
     *     "cacheManager"          = @DI\Inject("claroline.manager.cache_manager"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(
        CacheManager $cacheManager,
        PlatformConfigurationHandler $platformConfigHandler
    )
    {
        $this->cacheManager          = $cacheManager;
        $this->platformConfigHandler = $platformConfigHandler;
    }

    /**
     * @DI\Observe("refresh_cache")
     */
    public function refreshCache(RefreshCacheEvent $event)
    {
        $errors = $this->validateFacebook(
            $this->platformConfigHandler->getParameter('facebook_client_id'),
            $this->platformConfigHandler->getParameter('facebook_client_secret')
        );
        $event->addCacheParameter(
            'is_facebook_available',
            (count($errors) === 0 and $this->platformConfigHandler->getParameter('facebook_client_active'))
        );
    }

    public function isFacebookAvailable()
    {
        return $this->cacheManager->getParameter('is_facebook_available');
    }

    public function validateFacebook($appId, $secret)
    {
        if (!function_exists('curl_version')) {
            return array('error' => 'curl_facebook_application_validation_error');
        }

        if (!$appId || !$secret) {
            return array('error' => 'facebook_application_validation_error');
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

    public function getConfiguration()
    {
        return new Configuration(
            $this->platformConfigHandler->getParameter('facebook_client_id'),
            $this->platformConfigHandler->getParameter('facebook_client_secret'),
            $this->platformConfigHandler->getParameter('facebook_client_active')
        );
    }

    public function isActive()
    {
        return $this->platformConfigHandler->getParameter('facebook_client_active');
    }
}
