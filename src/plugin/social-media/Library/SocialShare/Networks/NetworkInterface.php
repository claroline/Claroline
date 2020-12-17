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

interface NetworkInterface
{
    /**
     * Gets networks's name.
     *
     * @return string
     */
    public function getName();

    /**
     * Gets networks's bg color.
     *
     * @return string
     */
    public function getColor();

    /**
     * Gets network's icon class.
     *
     * @return string
     */
    public function getIconClass();

    /**
     * Gets the share link for provided URL.
     *
     * @param $url
     * @param array $options
     *
     * @return string
     */
    public function getShareLink($url, array $options = []);

    /**
     * Gets the number of shares of the URL.
     *
     * @param $url
     *
     * @return int
     */
    public function countShares($url);
}
