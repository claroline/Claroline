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

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;

/**
 * @Service("claroline.manager.ip_white_list_manager")
 */
class IPWhiteListManager
{
    private $ipFile;

    /**
     * @InjectParams({
     *      "ipFile" = @Inject("%claroline.ip_white_list_file%"),
     * })
     */
    public function __construct($ipFile)
    {
        $this->ipFile = $ipFile;
    }

    public function addIP($ip)
    {
        $ips = Yaml::parse($this->ipFile);
        if (!in_array($ip, $ips)) $ips[] = $ip;
        $yaml = Yaml::dump($ips);
        file_put_contents($this->ipFile, $yaml);
    }

    public function IPExists($ip)
    {
        return in_array($ip, Yaml::parse($this->ipFile));
    }
}
