<?php

namespace Claroline\CoreBundle\Command\Dev;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

/**
 * Creates an user, optionaly with a specific role (default to simple user).
 */
class CreateUsersCommand extends ContainerAwareCommand
{
    public function __construct()
    {
        parent::__construct();

        $this->firstNames = array(
            "Mary",
            "Amanda",
            "James",
            "Patricia",
            "Michael",
            "Sarah",
            "Patrick",
            "Homer",
            "Bart",
            "Marge",
            "Lisa",
            "John",
            "Stan",
            "Stephane",
            "Emmanuel",
            "Nicolas",
            "Frederic",
            "Luke",
            "Luc",
            "Kenneth",
            "Stanley",
            "Kyle",
            "Leopold",
            "Eric",
            "Matthieu",
            "Aurelie",
            "Elisabeth",
            "Louis",
            "Jerome",
            "Ned",
            "Ralph",
            "Charles Montgomery",
            "Waylon",
            "Carl",
            "Timothy",
            "Kirk",
            "Milhouse",
            "Todd",
            "Maude",
            "Benjamen",
            "ObiWan",
            "George",
            "Barack",
            "Alfred",
            "Paul",
            "Gabriel",
            "Anne",
            "Theophile",
            "Bill",
            "Claudia",
            "Silva",
            "Ford",
            "Rodney",
            "Greg",
            "Bob",
            "Robert",
            "Jean-Kevin",
            "Charles-Henry",
            "Douglas",
            "Arthur",
            "Marvin",
            "Bruce",
            "William",
            "Jason",
            "Melanie",
            "Sophie",
            "Dominique",
            "Coralie",
            "Camille",
            "Claudia",
            "Margareth",
            "Antonio"
            );

         $this->lastNames = array(
             "Johnson",
             "Miller",
             "Brown",
             "Williams",
             "Davis",
             "Simpson",
             "Smith",
             "Doe",
             "Klein",
             "Godfraind",
             "Gervy",
             "Fervaille",
             "Minne",
             "Skywalker",
             "Marsh",
             "Broflovski",
             "Cartman",
             "Stotch",
             "McCormick",
             "McLane",
             "Bourne",
             "Yates",
             "McElroy",
             "Flanders",
             "Wiggum",
             "Burns",
             "Smithers",
             "Carlson",
             "LoveJoy",
             "Van Houten",
             "Gates",
             "Kenobi",
             "Lucas",
             "Clooney",
             "Harisson",
             "Obama",
             "Bush",
             "Black",
             "Hogan",
             "Anderson",
             "McKay",
             "Fields",
             "Bruel",
             "Kottick",
             "Dupond",
             "Leloux",
             "Miller",
             "Adams",
             "Dent",
             "Accroc",
             "Prefect",
             "Escort",
             "Sheridan",
             "William",
             "Willis",
             "Lee",
             "Devos",
             "Tatcher",
             "Gilbert",
             "Casilli"
        );

        $this->maxFirstNameOffset = count($this->firstNames);
        $this->maxFirstNameOffset--;
        $this->maxLastNameOffset = count($this->lastNames);
        $this->maxLastNameOffset--;
    }

    protected function configure()
    {
        $this->setName('claroline:users:create')
            ->setDescription('Creates a lot of users.');
        $this->setDefinition(array(
            new InputArgument('amount', InputArgument::REQUIRED, 'The number of users created'),
        ));
        $this->addOption(
            'ws_creator', 'wsc', InputOption::VALUE_NONE, "When set to true, created users will have the workspace creator role"
        );
        $this->addOption(
            'admin', 'a', InputOption::VALUE_NONE, "When set to true, created users will have the admin role"
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $params = array(
            'amount' => 'the number of users'
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $number = $input->getArgument('amount');

        for ($i=0; $i<$number; $i++) {
            $user = new User();
            $user->setFirstName($this->firstNames[rand(0, $this->maxFirstNameOffset)]);
            $user->setLastName($this->lastNames[rand(0, $this->maxLastNameOffset)]);
            $user->setUsername($user->getFirstName() . $user->getLastName() . rand(0, 1000));
            $user->setPlainPassword('123');
            $em = $this->getContainer()->get('doctrine.orm.entity_manager');
            $roleRepo = $em->getRepository('Claroline\CoreBundle\Entity\Role');

            if ($input->getOption('admin')) {
                $role = $roleRepo->findOneByName(PlatformRoles::ADMIN);
            } elseif ($input->getOption('ws_creator')) {
                $role = $roleRepo->findOneByName(PlatformRoles::WS_CREATOR);
            } else {
                $role = $roleRepo->findOneByName(PlatformRoles::USER);
            }
            $user->addRole($role);

            $em->persist($user);
            $config = new Configuration();
            $config->setWorkspaceType(Configuration::TYPE_SIMPLE);
            $config->setWorkspaceName($user->getUsername());
            $config->setWorkspaceCode('PERSO');
            $wsCreator = $this->getContainer()->get('claroline.workspace.creator');
            $workspace = $wsCreator->createWorkspace($config, $user);
            $workspace->setType(AbstractWorkspace::USER_REPOSITORY);
            $user->addRole($workspace->getManagerRole());
            $user->setPersonnalWorkspace($workspace);
            $em->persist($workspace);
            $em->flush();

            echo("--- user {$i} created \n");
        }
    }
}