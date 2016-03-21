<?php
/**
 * Created by PhpStorm.
 * User: ptsavdar
 * Date: 16/03/16
 * Time: 16:39
 */

namespace Icap\WebsiteBundle\Testing;


use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Icap\WebsiteBundle\Entity\Website;

class Persister
{
    /**
     * @var ObjectManager
     */
    private $om;
    /**
     * @var Role
     */
    private $userRole;

    private $websiteType;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * @param $username
     * @return User
     */
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

    /**
     * @param Workspace $workspace
     * @param User $user
     * @return User
     */
    public function workspaceUser(Workspace $workspace, User $user)
    {
        $role = new Role();
        $role->setName("ROLE_WS_{$workspace->getName()}_{$user->getUsername()}");
        $role->setTranslationKey($role->getName());
        $role->setWorkspace($workspace);
        $user->addRole($role);
        $this->om->persist($role);
        $this->om->persist($user);

        return $user;
    }

    /**
     * @param $title
     * @param User $creator
     * @return Website
     */
    public function website($title, User $creator)
    {
        $website = new Website();
        if (!$this->websiteType) {
            $this->websiteType = new ResourceType();
            $this->websiteType->setName('icap_website');
            $this->om->persist($this->websiteType);
        }

        $node = new ResourceNode();
        $node->setName($title);
        $node->setCreator($creator);
        $node->setResourceType($this->websiteType);
        $node->setWorkspace($creator->getPersonalWorkspace());
        $node->setClass('Icap\WebsiteBundle\Entity\Website');
        $node->setGuid(time());

        $website->setResourceNode($node);

        $this->om->persist($website);
        $this->om->persist($node);

        return $website;
    }
}