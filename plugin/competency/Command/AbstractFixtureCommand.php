<?php

namespace HeVinci\CompetencyBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractFixtureCommand extends Command
{
    protected function getFixture($fixtureFile, OutputInterface $output)
    {
        $file = realpath($fixtureFile);

        if (!$file) {
            if (false === $file = realpath(getcwd().'/'.$fixture)) {
                throw new \Exception("Cannot found fixture '{$fixture}'");
            }
        }

        require_once $file;

        $classes = get_declared_classes();
        $fixtureClass = end($classes); // should check type with reflection

        $container = $this->getApplication()->getKernel()->getContainer();

        return new $fixtureClass($container, $output);
    }
}
