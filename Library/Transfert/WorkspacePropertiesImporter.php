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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;

class WorkspacePropertiesImporter implements ImporterInterface{

    private $userManager;
    public $types = array('xml');

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *  "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *  "userManager"        = @DI\Inject("claroline.manager.user_manager"),
     * })
     */
     public function __construct(
         UserManager $userManager
     )
     {
         $this->userManager = $userManager;
     }
    public function supports($type)
    {
        return true;
    }

    public function valid($array)
    {
        $expectedKeys = array('name','code','owner','visible','selfregistration');
        $errors = array();

        foreach ($array as $i => $value)
        {
            if (!array_key_exists($value[$i],$expectedKeys[$i])) {
                $errors[$i] = $expectedKeys[$i];
            }
        }
    }

    public function import($array)
    {
        $workspace = new SimpleWorkspace();
        $workspaceAttributes = array();
        $user = new User();
        $user->setFirstName($array['first_name']);
        $user->setLastName($array['last_name']);
        $user->setAdministrativeCode($array['administrative_code']);
        $user->setMail($array['mail']);

        $workspace->setName($workspaceAttributes[0]);
        $workspace->setCode($workspaceAttributes[1]);
        $workspace->set($workspaceAttributes[2]);
        $workspace->setCode($workspaceAttributes[3]);

        $newUser = $this->userManager->createUser($user);
        $workspace->setCreator($newUser);
    }
} 