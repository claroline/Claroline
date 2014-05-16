<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Ldap;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;

/**
 * @DI\Service("claroline.library.ldap")
 */
class Ldap
{
    private $host;
    private $port;
    private $dn;
    private $connect;

    /**
     * @DI\InjectParams({"ch" = @DI\Inject("claroline.config.platform_config_handler")})
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
