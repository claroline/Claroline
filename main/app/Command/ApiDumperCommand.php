<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class ApiDumperCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:api:dump')->setDescription('Dump the api doc as json');
        $this->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'The required format (json|yml)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $format = $input->getOption('format');
        $data = [];
        $classes = $this->getContainer()->get('claroline.api.routing.finder')->getHandledClasses();

        foreach ($classes as $class) {
            $data[$class] = $this->getContainer()->get('claroline.api.routing.documentator')->documentClass($class);
        }

        switch ($format) {
          case 'json': $string = json_encode($data); break;
          case 'yml': $string = Yaml::dump($data); break;
        }

        $output->writeln($string);
    }
}
