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
        if ($this->username !== null){
            $user = $em->getRepository('ClarolineCoreBundle:User')->findOneBy(array('username' => $this->username));
            if ($user == null) {
                throw new \Exception("Cannot load entities in a non existing user personal workspace: user {$username} does not exists");
            }
            $workspace = $user->getPersonalWorkspace();
        } else {
            if ($this->ws !== null) {
                $workspace = $this->ws;
            } else {
                throw new \Exception("Cannot load entities in an non existing workspace");
            }
        }
        $collaboratorRole = $workspace->getCollaboratorRole();
        $this->log('role '.$collaboratorRole->getRole());

        if ($this->class == 'group') {
            $entities = $em->getRepository('ClarolineCoreBundle:Group')->findAll();
        } elseif ($this->class == 'user') {
            $entities = $em->getRepository('ClarolineCoreBundle:User')->findAll();
        } elseif ($this->class == null){
            $this->log("cleaning...");
            $this->clean($collaboratorRole, $manager);
            $this->log("done");
            $entities = null;
        }

        $this->log("entities :". count($entities));

        $maxLoops = count($entities);

        if($maxLoops > $this->nbUsers){
           $maxLoops = $this->nbUsers;
        }

        $this->log("max loops: $maxLoops");

        while ($i < $maxLoops)
        {
            $this->addToWorkspace($entities, $collaboratorRole, $manager);
            $i++;
        }

        $manager->flush();

//        $this->log(count($user->getPersonalWorkspace()->getCollaboratorRole()->getUsers())." collaborators added to user {$user->getUsername()}");
    }

    //may cause infinite loop due to the lack of optimization.
    private function addToWorkspace($entities, $collaboratorRole, $om)
    {
        $maxOffset = count($entities);
        $maxOffset--;
        $offset = rand(0, $maxOffset);
        $entity = $entities[$offset];

        if($entity->hasRole($collaboratorRole->getRole())){
            $this->log("I strongly recommand to ctrl+c if you see this a lot");
            $this->addToWorkspace($entities, $collaboratorRole, $om);
        } else {
            $entity->addRole($collaboratorRole);
            $this->log("entity whose class is ".get_class($entity)." and id is {$entity->getId()} added");
            $om->persist($entity);
//            unset($entities[$offset]);
//            $entities = array_values($entities);
//            $this->log(count($collaboratorRole->getUsers())." collaborators added ");
        }
    }

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
}

