<?php

namespace Claroline\DevBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Finder\Finder;

class ToolCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:tool')
            ->setDescription('Launches various development tools')
            ->setDefinition([
                new InputArgument('tool', InputArgument::REQUIRED, 'The tool to launch'),
                new InputArgument('bundle', InputArgument::REQUIRED, 'The target bundle'),
            ]);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $toolIds = array_keys($this->getAvailableTools());
        $toolQuestion = new ChoiceQuestion('Tool to launch: ', $toolIds);
        $bundleQuestion = new Question('Target bundle: ');

        while (null === $tool = $input->getArgument('tool')) {
            $tool = $helper->ask($input, $output, $toolQuestion);
            $input->setArgument('tool', $tool);
        }

        while (null === $bundle = $input->getArgument('bundle')) {
            $bundle = $helper->ask($input, $output, $bundleQuestion);
            $input->setArgument('bundle', $bundle);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $toolName = $input->getArgument('tool');
        $bundleName = $input->getArgument('bundle');
        $tools = $this->getAvailableTools();

        if (!in_array($toolName, array_keys($tools))) {
            throw new \Exception("Unknown tool '{$toolName}'");
        }

        $bundle = $this->getContainer()->get('kernel')->getBundle($bundleName);
        $output->writeln("Launching tool '{$toolName}'...");
        $argv = [$tools[$toolName], $bundle->getPath()];

        require $tools[$toolName];
    }

    private function getAvailableTools()
    {
        static $tools;

        if (!$tools) {
            $tools = [];
            $finder = new Finder();
            $scriptDir = realpath(__DIR__.'/../Resources/scripts/tools');
            $finder->depth(0)->files()->in($scriptDir);

            foreach ($finder as $file) {
                $tools[$file->getBasename('.php')] = $file->getPathname();
            }

            if (count($tools) === 0) {
                throw new \Exception(
                    "No tool available (no script found in {$scriptDir})"
                );
            }
        }

        return $tools;
    }
}
