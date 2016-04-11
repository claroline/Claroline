<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ResultBundle\Testing;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\ResultBundle\Entity\Mark;
use Claroline\ResultBundle\Entity\Result;

class Persister
{
    private $om;
    private $userRole;
    private $resultType;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function user($username)
    {
        $user = new User();
        $user->setFirstName($username);
        $user->setLastName($username);
        $user->setUsername($username);
        $user->setPassword($username);
        $user->setMail($username . '@mail.com');
        $user->setGuid($username);
        $this->om->persist($user);

        if (!$this->userRole) {
            $this->userRole = new Role();
            $this->userRole->setName('ROLE_USER');
            $this->userRole->setTranslationKey('user');
            $this->om->persist($this->userRole);
        }

        $user->addRole($this->userRole);
        $workspace = new Workspace();
        $workspace->setName($username);
        $workspace->setCreator($user);
        $workspace->setCode($username);
        $workspace->setGuid($username);
        $this->om->persist($workspace);

        $user->setPersonalWorkspace($workspace);

        return $user;
    }

    public function workspaceUser(Workspace $workspace, User $user)
    {
        $role = new Role();
        $role->setName("ROLE_WS_{$workspace->getName()}_{$user->getUsername()}");
        $role->setTranslationKey($role->getName());
        $role->setWorkspace($workspace);
        $user->addRole($role);
        $workspace->addRole($role);

        $this->om->persist($role);
        $this->om->persist($user);

        return $user;
    }

    public function result($title, User $creator, $total = 20)
    {
        $result = new Result();
        $result->setTotal($total);

        if (!$this->resultType) {
            $this->resultType = new ResourceType();
            $this->resultType->setName('claroline_result');
            $this->om->persist($this->resultType);
        }

        $node = new ResourceNode();
        $node->setName($title);
        $node->setCreator($creator);
        $node->setResourceType($this->resultType);
        $node->setWorkspace($creator->getPersonalWorkspace());
        $node->setClass('Claroline\ResultBundle\Entity\Result');
        $node->setGuid(time());

        $result->setResourceNode($node);

        $this->om->persist($result);
        $this->om->persist($node);

        return $result;
    }

    public function mark(Result $result, User $user, $value)
    {
        $mark = new Mark($result, $user, $value);

        $this->om->persist($mark);

        return $mark;
    }
}
