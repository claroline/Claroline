<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\LdapBundle\Library;

use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;

/**
 * @Service()
 */
class LdapManager
{
    private $host;
    private $port;
    private $dn;
    private $connect;

    /**
     * @InjectParams({"ch" = @Inject("claroline.config.platform_config_handler")})
     */
    public function __construct(PlatformConfigurationHandler $ch)
    {
        $this->host = $ch->getParameter('ldap_host');
        $this->port = $ch->getParameter('ldap_port');
        $this->dn = $ch->getParameter('ldap_root_dn');
    }

    public function connect()
    {
        $this->connect = ldap_connect($this->host, $this->port);

        if ($this->connect) {
            return @ldap_bind($this->connect);
        }
    }

    public function close()
    {
        ldap_close($this->connect);
    }

    public function search($filter, $attributes = array())
    {
        return ldap_search($this->connect, $this->dn, $filter, $attributes);
    }

    public function getEntries($search)
    {
        return ldap_get_entries($this->connect, $search);
    }
}
