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
    private $rootdn;

    /**
     * @DI\InjectParams({"ch" = @DI\Inject("claroline.config.platform_config_handler")})
     */
    public function __construct(PlatformConfigurationHandler $ch)
    {
        $this->host = $ch->getParameter('ldap_host');
        $this->port = $ch->getParameter('ldap_port');
        $this->rootdn = $ch->getParameter('ldap_root_dn');
    }
    public function connect()
    {
        $conn = ldap_connect($this->host, $this->port);

        if ($conn) {
            $bind = ldap_bind($conn);
            ldap_close($conn);
        }
    }

    public function close()
    {

    }
} 