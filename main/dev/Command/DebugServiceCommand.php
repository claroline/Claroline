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

use Claroline\CoreBundle\Library\Logger\ConsoleLogger;
use Claroline\CoreBundle\Listener\DoctrineDebug;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Debug a manager.
 */
class DebugServiceCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:debug:service')
            ->setDescription('Get the logs of a service (mainly for debugging doctrine)');
        $this->setDefinition(
            [
                new InputArgument('owner', InputArgument::REQUIRED, 'The user doing the action'),
                new InputArgument('service_name', InputArgument::REQUIRED, 'The service name'),
                new InputArgument('method_name', InputArgument::REQUIRED, 'The method name'),
                new InputArgument('parameters', InputArgument::IS_ARRAY, 'The method parameters'),
            ]
        );
        $this->addOption(
            'debug_doctrine_all',
            'a',
            InputOption::VALUE_NONE,
            'When set to true, shows the doctrine logs'
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $params = [
            'owner' => 'The user doing the action: ',
            'service_name' => 'The service name: ',
            'method_name' => 'The method name: ',
        ];

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
            $output,
            $argumentName,
            function ($argument) {
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
        $consoleLogger = ConsoleLogger::get($output);
        $manager = $this->getContainer()->get($input->getArgument('service_name'));
        if (method_exists($manager, 'setLogger')) {
            $manager->setLogger($consoleLogger);
        }
        $method = $input->getArgument('method_name');
        $om = $this->getContainer()->get('claroline.persistence.object_manager');

        if ($input->getOption('debug_doctrine_all')) {
            $om->setLogger($consoleLogger);
            $om->activateLog();
            $om->showFlushLevel();
            $this->getContainer()->get('claroline.doctrine.debug')->setLogger($consoleLogger)->activateLog()->setDebugLevel(DoctrineDebug::DEBUG_ALL)->setVendor('Claroline');
        }

        $this->getContainer()->get('claroline.authenticator')->authenticate($input->getArgument('owner'), null, false);
        $variables = $input->getArgument('parameters');
        $args = [];
        $class = get_class($manager);

        for ($i = 0; $i < count($variables); ++$i) {
            $param = new \ReflectionParameter([$class, $method], $i);
            $pclass = $param->getClass();
            $args[] = $pclass ?
                $om->getRepository($pclass->name)->find($variables[$i]) : $variables[$i];
        }

        call_user_func_array([$manager, $method], $args);
    }
}
