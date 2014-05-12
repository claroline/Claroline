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

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Mockery as m;

class LdapTest extends MockeryTestCase
{

    private $ch;
    private $connexion;

    public function setUp()
    {
        $this->ch = $this->mock('Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler');
        $this->ch->shouldReceive('getParameter')->with('ldap_host')->andReturn('ldaps://tet.univ-st-etienne.fr');
        $this->ch->shouldReceive('getParameter')->with('ldap_port')->andReturn('636');
        $this->ch->shouldReceive('getParameter')->with('ldap_root_dn')->andReturn('dc=univ-st-etienne,dc=fr');
        $this->connexion = new Ldap($this->ch);
    }

    public function testConnect()
    {
        $this->connexion->connect();
    }
} 