<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Transfert;

use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\RoleManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\SimpleXMLElement;

class UsersImporter implements ImporterInterface
{
    private $om;
    public $types = array('xml');
    private $roleManager;
    private $userManager;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *  "om"                     = @DI\Inject("claroline.persistence.object_manager"),
     *  "roleManager"     = @DI\Inject("claroline.manager.role_manager"),
     *  "userManager"        = @DI\Inject("claroline.manager.user_manager"),
     * })
     */

    public function __construct(
        ObjectManager $om,
        RoleManager $roleManager,
        UserManager $userManager
    )
    {
        $this->om = $om;
        $this->roleManager = $roleManager;
        $this->userManager = $userManager;
    }

    /**
     * @param $type xml,json
     * @return boolean
     */
    public function supports($type)
    {
        if (in_array($type,$this->types)) {
            return true;
        }
    }

    public function valid($array)
    {

    }

    public function import($users)
    {
        foreach ($users as $user) {
            $this->createUser($user);
        }
    }

    public function execute($path)
    {
        $manifest = new SimpleXMLElement($path);
        if ($this->supports($manifest->user->type)) {
            $this->valid();
        } else {
            throwException("format not supported");
        }
    }
} 