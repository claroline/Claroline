<?php

namespace Claroline\CoreBundle\Tests\DataFixtures\Special;

use Claroline\CoreBundle\Library\Fixtures\LoggableFixture;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadEntitiesInWorkspace extends LoggableFixture implements ContainerAwareInterface
{
    private $nbUsers;
    private $class;
    private $username;
    private $ws;

    public function __construct($nbUsers, $class, $username, $ws = null)
    {
        $this->nbUsers = $nbUsers;
        $this->class = strtolower($class);
        $this->username = $username;
        $this->ws = $ws;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function load(ObjectManager $manager)
    {
        $i = 0;
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        if ($this->username !== null) {
            $user = $em->getRepository('ClarolineCoreBundle:User')
                ->findOneBy(array('username' => $this->username));
            if ($user == null) {
                throw new \Exception(
                    "Cannot load entities in a non existing user personal workspace:"
                    . "user {$this->username} does not exists"
                );
            }
            $workspace = $user->getPersonalWorkspace();
        } else {
            if ($this->ws !== null) {
                $workspace = $this->ws;
            } else {
                throw new \Exception("Cannot load entities in an non existing workspace");
            }
        }

        if ($this->class === 'group') {
            $entities = $em->getRepository('ClarolineCoreBundle:Group')->findAll();
        } elseif ($this->class === 'user') {
            $entities = $em->getRepository('ClarolineCoreBundle:User')->findAll();
        } elseif ($this->class === null) {
            $this->log("cleaning...");
            $this->log("done");
            $entities = null;
        }

        $this->log("entities :". count($entities));

        $maxLoops = count($entities);

        if ($maxLoops > $this->nbUsers) {
            $maxLoops = $this->nbUsers;
        }

        $this->log("max loops: $maxLoops");

        while ($i < $maxLoops) {
            $this->addToWorkspace($entities, $workspace, $manager);
            $i++;
        }

        $manager->flush();

    }

    //may cause infinite loop due to the lack of optimization.
    private function addToWorkspace($entities, $workspace, $om)
    {

        $maxOffset = count($entities);
        $maxOffset--;
        $offset = rand(0, $maxOffset);
        $entity = $entities[$offset];

        $wsRoles = $om->getRepository('ClarolineCoreBundle:Role')->getWorkspaceRoles($workspace);
        $isRegistered = false;

        if (get_class($entity) === 'Claroline\CoreBundle\Entity\Group') {
            foreach ($wsRoles as $role) {
                if ($entity->hasRole($role->getName())) {
                    $isRegistered = true;
                }
            }
        } else {
            //it must be sure the user doens't already have this role.
            //If the user has the role through a group it's stikk OK.
            $userRoles = $entity->getRoles(false);
            foreach ($userRoles as $userRole) {
                foreach ($wsRoles as $role) {
                    if ($role->getName() == $userRole) {
                        $isRegistered = true;
                    }
                }
            }
        }

        if ($isRegistered) {
            $this->log("I strongly recommand to ctrl+c if you see this a lot");
            $this->addToWorkspace($entities, $workspace, $om);
        } else {
            $this->log("entity whose class is ".get_class($entity)." and id is {$entity->getId()} added");
            $entity->addRole($om->getRepository('ClarolineCoreBundle:Role')->getCollaboratorRole($workspace));
            $om->persist($entity);
            $om->flush();
            unset($entities[$offset]);
            $entities = array_values($entities);
            $this->log(count($om->getRepository('ClarolineCoreBundle:Role')
                ->getCollaboratorRole($workspace)->getUsers())." collaborators added "
            );
        }
    }
    /*
    private function clean($collaboratorRole, $om)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $users = $em->getRepository('ClarolineCoreBundle:User')->findAll();

        foreach($users as $user){
            $user->removeRole($collaboratorRole);
            $om->persist($user);
        }

        $om->flush();
        $groups = $em->getRepository('ClarolineCoreBundle:Group')->findAll();

        foreach($groups as $group){
            $group->removeRole($collaboratorRole);
            $om->persist($group);
        }

        $om->flush();
    }
    */
}

