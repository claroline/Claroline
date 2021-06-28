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

use Claroline\AppBundle\Routing\Documentator;
use Claroline\AppBundle\Routing\Finder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class ApiDumpCommand extends Command
{
    private $finder;
    private $documentator;

    public function __construct(Finder $finder, Documentator $documentator)
    {
        $this->finder = $finder;
        $this->documentator = $documentator;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Dump the api doc as json');
        $this->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'The required format (json|yml)');
        $this->addOption('debug', 'd', InputOption::VALUE_NONE, 'debug mode (no output)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $format = $input->getOption('format') ?? 'json';
        $data = [];

        foreach ($this->finder->getHandledClasses() as $class) {
            $data[$class] = $this->documentator->documentClass($class);
        }

        switch ($format) {
          case 'json': $string = json_encode($data, JSON_PRETTY_PRINT); break;
          case 'yml': $string = Yaml::dump($data); break;
        }

        if (!$input->getOption('debug')) {
            $output->writeln($string);
        }

        return 0;
    }
}
