<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DevBundle\Command;

use Claroline\AppBundle\Command\BaseCommandTrait;
use Claroline\AppBundle\Logger\ConsoleLogger;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Security\Authentication\Authenticator;
use Claroline\CoreBundle\Command\AdminCliCommand;
use Claroline\CoreBundle\Listener\Doctrine\DebugListener;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Debug a manager.
 */
class DebugServiceCommand extends Command implements AdminCliCommand
{
    use BaseCommandTrait;

    private $params = [
        'owner' => 'The user doing the action: ',
        'service_name' => 'The service name: ',
        'method_name' => 'The method name: ',
    ];

    private $om;
    private $debugListener;
    private $authenticator;

    public function __construct(ObjectManager $om, DebugListener $debugListener, Authenticator $authenticator)
    {
        $this->om = $om;
        $this->debugListener = $debugListener;
        $this->authenticator = $authenticator;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('claroline:debug:service')
            ->setDescription('Get the logs of a service (mainly for debugging doctrine)')
            ->setDefinition([
                new InputArgument('owner', InputArgument::REQUIRED, 'The user doing the action'),
                new InputArgument('service_name', InputArgument::REQUIRED, 'The service name'),
                new InputArgument('method_name', InputArgument::REQUIRED, 'The method name'),
                new InputArgument('parameters', InputArgument::IS_ARRAY, 'The method parameters'),
            ])
            ->addOption(
                'debug_doctrine_all', 'a', InputOption::VALUE_NONE, 'When set to true, shows the doctrine logs'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $consoleLogger = ConsoleLogger::get($output);
        $manager = $this->getApplication()->getKernel()->get($input->getArgument('service_name'));
        if (method_exists($manager, 'setLogger')) {
            $manager->setLogger($consoleLogger);
        }
        $method = $input->getArgument('method_name');

        if ($input->getOption('debug_doctrine_all')) {
            $this->om->setLogger($consoleLogger);
            $this->om->activateLog();
            $this->om->showFlushLevel();
            $this->debugListener->setLogger($consoleLogger)->activateLog()->setDebugLevel(DebugListener::DEBUG_ALL)->setVendor('Claroline');
        }

        $this->authenticator->authenticate($input->getArgument('owner'), null, false);
        $variables = $input->getArgument('parameters');
        $args = [];
        $class = get_class($manager);

        for ($i = 0; $i < count($variables); ++$i) {
            $param = new \ReflectionParameter([$class, $method], $i);
            $pclass = $param->getClass();
            $args[] = $pclass ?
                $this->om->getRepository($pclass->name)->find($variables[$i]) : $variables[$i];
        }

        call_user_func_array([$manager, $method], $args);

        return 0;
    }
}
