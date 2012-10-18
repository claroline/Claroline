<?php

namespace Claroline\CoreBundle\Tests\DataFixtures\Special;

use Claroline\CoreBundle\DataFixtures\LoggableFixture;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadEntitiesInWorkspace extends LoggableFixture implements ContainerAwareInterface
{
    private $nbUsers;
    private $class;
    private $username;

    public function __construct($nbUsers, $class, $username)
    {
        $this->nbUsers = $nbUsers;
        $this->class = strtolower($class);
        $this->username = $username;
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
        var_dump($this->username);
        $workspace = $em->getRepository('ClarolineCoreBundle:User')->findOneBy(array('username' => $this->username))->getPersonalWorkspace();
        $collaboratorRole = $workspace->getCollaboratorRole();

        if ($this->class == 'group') {
            $entities = $em->getRepository('ClarolineCoreBundle:Group')->findAll();
        } elseif ($this->class == 'user') {
            $entities = $em->getRepository('ClarolineCoreBundle:User')->findAll();
        } else {
            $this->log("cleaning...");
            $this->clean($collaboratorRole, $manager);
            $this->log("done");
            return;
        }

        $maxLoops = count($entities);

        if($maxLoops > $this->nbUsers){
           $maxLoops = $this->nbUsers;
        }

        while ($i < $maxLoops)
        {
            $this->addToWorkspace($entities, $collaboratorRole, $manager);
            $i++;
        }

        $em->flush();
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
            $this->log("entity whose id is {$entity->getId()} added");
            unset($entities[$offset]);
            $entities = array_values($entities);
        }
        $om->persist($entity);
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

