<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UJM\ExoBundle\Command;

use Claroline\CoreBundle\Manager\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Manager\ExerciseManager;

class ExportExoResultsCommand extends Command
{
    private $userManager;
    private $exerciseManager;
    private $em;
    private $tokenStorage;

    public function __construct(UserManager $userManager, ExerciseManager $exerciseManager, EntityManagerInterface $em, TokenStorageInterface $tokenStorage)
    {
        $this->userManager = $userManager;
        $this->exerciseManager = $exerciseManager;
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('export exercice results into csv');
        $this->setDefinition(
      [
        new InputArgument('exercice_id', InputArgument::REQUIRED, 'ID of the exercise to export, or workspace ID if option set'),
        new InputArgument('username', InputArgument::REQUIRED, 'Username'),
        new InputArgument('export_directory', InputArgument::OPTIONAL, 'Path to export'),
      ]
    );
        $this->addOption(
      'workspace',
      null,
      InputOption::VALUE_NONE,
      'When set, search for exercices in workspace ID'
    );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getArgument('exercice_id');
        $outputOption = $input->getArgument('export_directory');
        $username = $input->getArgument('username');
        $workspaceMode = $input->getOption('workspace');
        //credentials
        $user = $this->userManager->getUserByUsername($username);
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);

        $exoRepo = $this->em->getRepository(Exercise::class);
        $exercises = [];
        if (!$workspaceMode) {
            $exercises[] = $exoRepo->findOneBy(['resourceNode' => $id]);
        } else {
            $exercises = $exoRepo->findByWorkspace($id);
        }
        if (is_array($exercises) && !empty($exercises)) {
            foreach ($exercises as $exo) {
                $exercise = $exoRepo->findOneBy(['resourceNode' => $exo->getResourceNode()->getId()]);
                $path = null;
                if (null !== $outputOption) {
                    $path = $outputOption.'/'.preg_replace('/[^A-Za-z0-9_\-]/', '_', $exercise->getResourceNode()->getName()).'['.$exercise->getResourceNode()->getId().'].csv';
                }
                try {
                    $output->writeln('<comment>Debut export resultats exercice ID </comment>'.$exo->getResourceNode()->getId());
                    $this->exerciseManager->exportResultsToCsv($exercise, $path);
                    $output->writeln('<comment>Fin export resultats exercice ID </comment>'.$exo->getResourceNode()->getId().': '.$path);
                } catch (\Exception  $e) {
                    $output->writeln('<error>!!!!Erreur export resultats exercice ID '.$exo->getResourceNode()->getId().'</error>');
                }
                $output->writeln('------------------------------------------------------------------------------');
            }
        }

        return 0;
    }
}
