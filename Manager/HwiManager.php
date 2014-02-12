<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Event\RefreshCacheEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;

/**
 * @DI\Service("claroline.manager.hwi_manager")
 */
class HwiManager
{
    private $cacheManager;
    private $ch;

    /**
     * @DI\InjectParams({
     *     "cacheManager"   = @DI\Inject("claroline.manager.cache_manager"),
     *     "ch"             = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(
        CacheManager $cacheManager,
        PlatformConfigurationHandler $ch
    )
    {
        $this->cacheManager = $cacheManager;
        $this->ch = $ch;
    }

    /**
     * @DI\Observe("refresh_cache")
     */
    public function refreshCache(RefreshCacheEvent $event)
    {
        $errors = $this->validateFacebook($this->ch->getParameter('facebook_client_id'), $this->ch->getParameter('facebook_client_secret'));
        $boolean = count($errors) === 0 ? true: false;
        $event->addCacheParameter('is_facebook_available', $boolean);
    }

    public function isFacebookAvailable()
    {
        return $this->cacheManager->getParameter('is_facebook_available');
    }

    public function validateFacebook($appId, $secret)
    {
        $secretUrl = "https://graph.facebook.com/{$appId}?fields=roles&access_token={$appId}|{$secret}";
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $secretUrl);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_USERAGENT, 'ClarolineConnect');
        $json = curl_exec($curl_handle);
        curl_close($curl_handle);
        $data = json_decode($json);

        if (array_key_exists('error', $data)) {
            return array('error' => 'facebook_application_validation_error');
        }

        return array();
    }


}