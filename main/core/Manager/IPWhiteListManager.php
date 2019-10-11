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

use Symfony\Component\Yaml\Yaml;

class IPWhiteListManager
{
    private $ipFile;
    private $rangeFile;

    public function __construct($ipFile, $rangeFile)
    {
        $this->ipFile = $ipFile;
        $this->rangeFile = $rangeFile;
    }

    public function addIP($ip)
    {
        $ips = [];
        if (file_exists($this->ipFile)) {
            $ips = Yaml::parseFile($this->ipFile);
        }

        if (is_array($ips)) {
            if (!in_array($ip, $ips)) {
                $ips[] = $ip;
            }
        } else {
            $ips = [$ip];
        }

        $yaml = Yaml::dump($ips);
        file_put_contents($this->ipFile, $yaml);
    }

    public function removeIP($ip)
    {
        $ips = Yaml::parseFile($this->ipFile);

        if (is_array($ips)) {
            $key = array_search($ip, $ips);
            if (null !== $key) {
                unset($ips[$key]);
                $yaml = Yaml::dump($ips);
                file_put_contents($this->ipFile, $yaml);
            }
        }
    }

    public function IPExists($ip)
    {
        return in_array($ip, Yaml::parseFile($this->ipFile));
    }

    public function isWhiteListed()
    {
        if (file_exists($this->ipFile)) {
            $ips = Yaml::parseFile($this->ipFile);
            if (is_array($ips)) {
                foreach ($ips as $ip) {
                    if (isset($_SERVER['REMOTE_ADDR']) && $ip === $_SERVER['REMOTE_ADDR']) {
                        return true;
                    }
                }
            }
        }

        if (file_exists($this->rangeFile)) {
            $ranges = Yaml::parseFile($this->rangeFile);

            if (is_array($ranges)) {
                foreach ($ranges as $range) {
                    if ($this->validateRange($range['lower_bound'], $range['higher_bound'])) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function validateRange($lowerBound, $higherBound)
    {
        $ip = $_SERVER['REMOTE_ADDR'];

        return ip2long($ip) <= ip2long($higherBound) && ip2long($lowerBound) <= ip2long($ip);
    }

    public function cleanWhiteList()
    {
        file_put_contents($this->ipFile, Yaml::dump([]));
    }
}
