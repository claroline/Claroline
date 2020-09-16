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
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\ApiManager;
use Claroline\CoreBundle\Manager\UserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Creates an user, optionally with a specific role (default to simple user).
 */
class ImportCsvCommand extends Command
{
    use BaseCommandTrait;

    private $params = ['id' => 'The file id: ', 'action' => 'The action to execute: '];
    private $importLogDir;
    private $userManager;
    private $tokenStorage;
    private $objectManager;
    private $apiManager;

    public function __construct(string $importLogDir, UserManager $userManager, TokenStorageInterface $tokenStorage, ObjectManager $objectManager, ApiManager $apiManager)
    {
        $this->importLogDir = $importLogDir;
        $this->userManager = $userManager;
        $this->tokenStorage = $tokenStorage;
        $this->apiManager = $apiManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Load from csv for the api');
        $this->setDefinition(
            [
              new InputArgument('id', InputArgument::REQUIRED, 'The file id.'),
              new InputArgument('action', InputArgument::REQUIRED, 'The action to execute.'),
              new InputArgument('log', InputArgument::OPTIONAL, 'The action to execute.'),
            ]
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logFile = $input->getArgument('log') ? $input->getArgument('log') : $this->generateRandomString(5);
        //big try catch in case something goes wrong, we can log it
        $path = $this->importLogDir.'/'.$logFile.'.json';
        $jsonLogger = new JsonLogger($path);
        $jsonLogger->set('total', 0);
        $jsonLogger->set('processed', 0);
        $jsonLogger->set('error', 0);
        $jsonLogger->set('success', 0);
        $jsonLogger->set('data.error', []);
        $jsonLogger->set('data.success', []);

        try {
            $user = $this->userManager->getDefaultClarolineAdmin();
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->tokenStorage->setToken($token);

            $id = $input->getArgument('id');
            $action = $input->getArgument('action');
            $publicFile = $this->objectManager->getObject(
                ['id' => $id],
                'Claroline\CoreBundle\Entity\File\PublicFile'
            );

            $this->apiManager->import(
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

        return 0;
    }

    public function generateRandomString($length = 10)
    {
        return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
    }
}
