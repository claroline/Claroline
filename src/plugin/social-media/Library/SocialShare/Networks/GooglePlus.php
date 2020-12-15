<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 4/23/15
 */

namespace Icap\SocialmediaBundle\Library\SocialShare\Networks;

class GooglePlus implements NetworkInterface
{
    const NAME = 'google';
    const SHARE_URL = 'https://plus.google.com/share?url=%s';
    const API_URL = 'https://clients6.google.com/rpc';
    const COLOR = '#dd4b39';
    const ICON = 'google-plus';

    /**
     * Gets networks's name.
     *
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Gets the share link for provided URL.
     *
     * @param $url
     * @param array $options
     *
     * @return string
     */
    public function getShareLink($url, array $options = array())
    {
        return sprintf(self::SHARE_URL, urlencode($url));
    }

    /**
     * Gets the number of shares of the URL.
     *
     * @param $url
     *
     * @return int
     */
    public function countShares($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, self::API_URL);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS,
            '[{"method":"pos.plusones.get","id":"p",
                "params":{"nolog":true,"id":"'.$url.'","source":"widget","userId":"@viewer","groupId":"@self"},
                "jsonrpc":"2.0","key":"p","apiVersion":"v1"}]'
        );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        $curl_results = curl_exec($curl);
        curl_close($curl);
        $data = json_decode($curl_results, true);

        if (isset($data[0]['result']['metadata']['globalCounts']['count'])) {
            return intval($data[0]['result']['metadata']['globalCounts']['count']);
        } else {
            return 0;
        }
    }

    /**
     * Gets networks's bg color.
     *
     * @return string
     */
    public function getColor()
    {
        return self::COLOR;
    }

    /**
     * Gets network's icon class.
     *
     * @return string
     */
    public function getIconClass()
    {
        return self::ICON;
    }
}
