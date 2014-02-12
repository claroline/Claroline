<?php
/**
 * Created by PhpStorm.
 * User: jorge
 * Date: 12/02/14
 * Time: 13:50
 */

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.hwi_manager")
 */
class HwiManager
{
    public function __construct()
    {

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