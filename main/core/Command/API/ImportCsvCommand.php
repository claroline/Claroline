<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\API;

use Claroline\AppBundle\Command\BaseCommandTrait;
use Claroline\AppBundle\Logger\JsonLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Creates an user, optionaly with a specific role (default to simple user).
 */
class ImportCsvCommand extends ContainerAwareCommand
{
    use BaseCommandTrait;
    private $params = ['id' => 'The file id: ', 'action' => 'The action to execute: '];

    protected function configure()
    {
        $this->setName('claroline:api:load')
            ->setDescription('Load from csv for the api');
        $this->setDefinition(
            [
              new InputArgument('id', InputArgument::REQUIRED, 'The file id.'),
              new InputArgument('action', InputArgument::REQUIRED, 'The action to execute.'),
              new InputArgument('log', InputArgument::OPTIONAL, 'The action to execute.'),
            ]
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logFile = $input->getArgument('log') ? $input->getArgument('log') : $this->generateRandomString(5);
        //big try catch in case something goes wrong, we can log it
        $path = $this->getContainer()->getParameter('claroline.param.import_log_dir').'/'.$logFile.'.json';
        $jsonLogger = new JsonLogger($path);
        $jsonLogger->set('total', 0);
        $jsonLogger->set('processed', 0);
        $jsonLogger->set('error', 0);
        $jsonLogger->set('success', 0);
        $jsonLogger->set('data.error', []);
        $jsonLogger->set('data.success', []);

        try {
            $user = $this->getContainer()->get('claroline.manager.user_manager')->getDefaultUser();
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->getContainer()->get('security.context')->setToken($token);

            $id = $input->getArgument('id');
            $action = $input->getArgument('action');
            $publicFile = $this->getContainer()->get('claroline.api.serializer')->deserialize(
              'Claroline\CoreBundle\Entity\File\PublicFile',
              ['id' => $id]
          );

            $this->getContainer()->get('claroline.manager.api_manager')->import(
              $publicFile,
              $action,
              $logFile
          );

            $output->writeLn('Done, your log name is '.$logFile.'.');
        } catch (\Exception $e) {
            $jsonLogger->increment('error');
            $jsonLogger->push('data.error', [
              'line' => 'unkown',
              'value' => $e->getFile().':'.$e->getLine()."\n".$e->getMessage(),
            ]);
        }
    }

    public function generateRandomString($length = 10)
    {
        return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
    }
}
