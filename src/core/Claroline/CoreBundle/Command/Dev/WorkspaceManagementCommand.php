<?php

namespace Claroline\CoreBundle\Command\Dev;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class WorkspaceManagementCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:workspace:management')
            ->setDescription('Will register users to the personal workspace of the specified user');

        $this->setDefinition(array(
            new InputArgument('username', InputArgument::REQUIRED, 'the username'),
            new InputArgument('count', InputArgument::OPTIONAL, 'the maximum amount of entities added'),
        ));
        $this->addOption(
            'group', 'g', InputOption::VALUE_NONE, "When set to true, the command will register groups"
        );
        $this->addOption(
            'user', 'u', InputOption::VALUE_NONE, "When set to true, the command will register groups"
        );
        $this->addOption(
            'clean', 'c', InputOption::VALUE_NONE, "When set to true, the command will register groups"
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $params = array(
            'username' => 'username',
            'count' => 'count',
        );

        foreach ($params as $argument => $argumentName) {
            if (!$input->getArgument($argument)) {
                $input->setArgument(
                    $argument, $this->askArgument($output, $argumentName)
                );
            }
        }
    }

    protected function askArgument(OutputInterface $output, $argumentName)
    {
        $argument = $this->getHelper('dialog')->askAndValidate(
            $output, "Enter the {$argumentName}: ", function($argument) {
                if (empty($argument)) {
                    throw new \Exception('This argument is required');
                }

                return $argument;
            }
        );

        return $argument;
    }

    //this is not optimized
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $i = 0;
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:User')->findOneBy(array('username' => $input->getArgument('username')))->getPersonalWorkspace();
        $collaboratorRole = $workspace->getCollaboratorRole();

        if ($input->getOption('group')) {
            $entities = $em->getRepository('ClarolineCoreBundle:Group')->findAll();
        } elseif ($input->getOption('user')) {
            $entities = $em->getRepository('ClarolineCoreBundle:User')->findAll();
        } else {
            echo "cleaning...\n";
            $this->clean($collaboratorRole);
            echo "done\n";
            return;
        }

        $maxLoops = count($entities);

        if($maxLoops > $input->getArgument('count')){
           $maxLoops = $input->getArgument('count');
        }

        while ($i < $maxLoops)
        {
            $this->addToWorkspace($entities, $collaboratorRole);
            $i++;
        }

        $em->flush();
    }

    //may cause infinite loop due to the lack of optimization.
    private function addToWorkspace($entities, $collaboratorRole)
    {
        $maxOffset = count($entities);
        $maxOffset--;
        $offset = rand(0, $maxOffset);
        $entity = $entities[$offset];

        if($entity->hasRole($collaboratorRole->getRole())){
            echo "I strongly recommand to ctrl+c if you see this a lot\n";
            $this->addToWorkspace($entities, $collaboratorRole);
        } else {
            $entity->addRole($collaboratorRole);
            echo "entity whose id is {$entity->getId()} added\n";
            unset($entities[$offset]);
            $entities = array_values($entities);
        }
        $this->getContainer()->get('doctrine.orm.entity_manager')->persist($entity);
    }

    private function clean($collaboratorRole)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $users = $em->getRepository('ClarolineCoreBundle:User')->findAll();

        foreach($users as $user){
            $user->removeRole($collaboratorRole);
            $em->persist($user);
        }

        $em->flush();
        $groups = $em->getRepository('ClarolineCoreBundle:Group')->findAll();

        foreach($groups as $group){
            $group->removeRole($collaboratorRole);
            $em->persist($group);
        }

        $em->flush();
    }
}