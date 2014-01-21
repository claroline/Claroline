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

    public function valid(\DOMNodeList $node)
    {
        $expectedKeys = array('name','code','owner','visible','selfregistration');
        $errors = array();
        $child = $node->item(0)->childNodes;
        foreach ($child as $value)
        {
            if (!in_array($value->nodeName,$expectedKeys)) {
                $errors[] = $value->nodeName;
            }
        }
    }

    public function import($path)
    {
        $workspace = new SimpleWorkspace();
        $workspaceAttributes = array();
        $user = new User();
        $userAttributes = array();
        $doc = new \DOMDocument();
        $doc->load($path);
        $properties = $doc->getElementsByTagName('properties');
        $child = $properties->item(0);

        foreach ($child as $value)
        {
           $workspaceAttributes = $value->nodeValue;
        }
        $workspace->setName($workspaceAttributes[0]);
        $workspace->setCode($workspaceAttributes[1]);
        $workspace->set($workspaceAttributes[2]);
        $workspace->setCode($workspaceAttributes[3]);
        $u = $child->getElementsByTagName('owner')->item(0); // get user creator
        foreach ($u as $node )
        {
            $userAttributes[] = $node->nodeValue;
        }
        $user->setFirstName($userAttributes[0]);
        $user->setLastName($userAttributes[1]);
        $user->setUsername($userAttributes[2]);
        $user->setMail($userAttributes[3]);
        $user->setPassword($userAttributes[4]);
        $user->setlocale($userAttributes[5]);
        $newUser = $this->userManager->createUser($user);
        $workspace->setCreator($newUser);
    }
} 