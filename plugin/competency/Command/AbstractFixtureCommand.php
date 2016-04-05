<?php

namespace HeVinci\CompetencyBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractFixtureCommand extends ContainerAwareCommand
{
    protected function getFixture($fixtureFile, OutputInterface $output)
    {
        $file = realpath($fixtureFile);

        if (!$file) {
            if (false === $file = realpath(getcwd() . '/' . $fixture)) {
                throw new \Exception("Cannot found fixture '{$fixture}'");
            }
        }

        require_once($file);

        $classes = get_declared_classes();
        $fixtureClass = end($classes); // should check type with reflection

        $container = $this->getContainer();

        return new $fixtureClass($container, $output);
    }
}
