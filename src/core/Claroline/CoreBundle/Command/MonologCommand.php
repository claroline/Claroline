<?php

namespace Claroline\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

class MonologCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('claroline:logs:monolog')
            ->setDescription('Enable monolog in yml config file for prod, dev, test, etc')
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'path of yml config file'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);

        $path = $input->getArgument('path');

        if ($path and file_exists($path)) {

            if ($value = $this->editYml($this->getYml($path))) {
                $output->writeln('<info>Writing file...</info>');
                $this->writeYml($value, $path);
            } else {
                $output->writeln('<info>There is a problem parsing the file or monolog block is not defined</info>');
            }

        } else {
            $output->writeln('<info>File path not defined or file does not exist</info>');
        }

        $output->writeln('<comment>'.(microtime(true) - $start).' seconds</comment>');
    }

    private function getYml($path)
    {
        $yaml = new Parser();

        try {
            return $yaml->parse(file_get_contents($path));
        } catch (ParseException $e) {
            return null;
        }
    }

    private function editYml($array)
    {
        if (isset($array['monolog'])) {

            $array['monolog'] = array(
                'handlers' => array(
                    'main' => array(
                        'type' => 'stream',
                        'path' => '%kernel.logs_dir%/%kernel.environment%.log',
                        'level' => 'debug',
                    ),
                    'firephp' => array(
                        'type' => 'firephp',
                        'level' => 'info'
                    )
                )
            );

            return $array;

        }

        return null;
    }

    private function writeYml($array, $path)
    {
        $dumper = new Dumper();

        $yaml = $dumper->dump($array, 4);

        //var_dump($yaml);

        file_put_contents($path, $yaml);
    }
}
