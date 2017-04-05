<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 3/30/17
 */

namespace Claroline\LdapBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class LdapUser.
 *
 * @ORM\Table(name="claro_ldap_user")
 * @ORM\Entity(repositoryClass="Claroline\LdapBundle\Repository\LdapUserRepository")
 */
class LdapUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $ldapId;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $serverName;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;

    public function __construct($serverName, $ldapId, User $user)
    {
        $this->serverName = $serverName;
        $this->ldapId = $ldapId;
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
