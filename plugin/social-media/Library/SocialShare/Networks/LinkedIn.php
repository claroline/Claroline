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

class LinkedIn implements NetworkInterface
{
    const NAME = 'linkedin';
    const SHARE_URL = 'http://www.linkedin.com/shareArticle?%s';
    const API_URL = 'http://www.linkedin.com/countserv/count/share?url=%s&format=json';
    const COLOR = '#007bb6';
    const ICON = 'linkedin';

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
        $options['mini'] = 'true';
        $options['url'] = $url;

        return sprintf(self::SHARE_URL, http_build_query($options));
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
        $api_url = sprintf(self::API_URL, urlencode($url));
        $data = null;
        try {
            $data = json_decode(file_get_contents($api_url));
        } catch (\Exception $e) {
            $data = null;
        }

        return ($data !== null && isset($data->count)) ? intval($data->count) : 0;
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
