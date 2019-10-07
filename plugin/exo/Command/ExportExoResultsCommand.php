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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ExportExoResultsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:exo:export_results')->setDescription('export exercice results into csv');
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('exercice_id');
        $outputOption = $input->getArgument('export_directory');
        $username = $input->getArgument('username');
        $workspaceMode = $input->getOption('workspace');
        //credentials
        $user = $this->getContainer()->get('claroline.manager.user_manager')->getUserByUsername($username);
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->getContainer()->get('security.context')->setToken($token);
        $exoManager = $this->getContainer()->get('UJM\ExoBundle\Manager\ExerciseManager');

        $exoRepo = $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository('UJMExoBundle:Exercise');
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
                    $exoManager->exportResultsToCsv($exercise, $path);
                    $output->writeln('<comment>Fin export resultats exercice ID </comment>'.$exo->getResourceNode()->getId().': '.$path);
                } catch (ContextErrorException  $e) {
                    $output->writeln('<error>!!!!Erreur export resultats exercice ID '.$exo->getResourceNode()->getId().'</error>');
                }
                $output->writeln('------------------------------------------------------------------------------');
            }
        }
    }
}
