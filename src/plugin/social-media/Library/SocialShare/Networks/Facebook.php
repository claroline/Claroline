<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * Date: 4/23/15
 */
namespace Icap\SocialmediaBundle\Library\SocialShare\Networks;

class Facebook implements NetworkInterface
{
    const NAME = 'facebook';
    const SHARE_URL = 'https://www.facebook.com/sharer/sharer.php?u=%s';
    const API_URL = 'http://api.facebook.com/restserver.php?method=links.getStats&urls=%s&format=json';
    const COLOR = '#3b5998';
    const ICON = 'facebook';

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
    public function getShareLink($url, array $options = [])
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
        $api_url = sprintf(self::API_URL, urlencode($url));
        $data = null;
        try {
            $data = json_decode(file_get_contents($api_url), true);
        } catch (\Exception $e) {
            $data = null;
        }

        return ($data !== null && isset($data[0]->share_count)) ? intval($data[0]->share_count) : 0;
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
