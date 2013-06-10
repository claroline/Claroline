<?php

namespace Claroline\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ThemeCompileCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('claroline:theme:compile')
            ->setDescription('Compile themes')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Name or path of a theme (example: ClarolineCoreBundle:less:bootstrap-default/theme.html.twig)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);

        $themeService = $this->getContainer()->get('claroline.common.theme_service');
        $name = $input->getArgument('name');
        $themes = $themeService->getThemes();
        $list = $themeService->listThemes($themes);

        $output->writeln("<info>Themes to compile:</info>");

        if ($name and isset($list[$name])) {

            $output->writeln(str_pad($name, 50)." <comment>".$list[$name]."</comment>");
            $name = $list[$name];

        } elseif ($name and array_search($name, $list)) {

            $output->writeln(str_pad(array_search($name, $list), 50)." <comment>$name</comment>");

        } else {

            $name = $themes;

            foreach ($themes as $theme) {
                $output->writeln(str_pad($theme->getName(), 50)." <comment>".$theme->getPath()."</comment>");
            }
        }

        $output->writeln("<info>Compiling...</info>");

        $themeService->compileTheme($name, "./web/");

        $output->writeln("<comment>".(microtime(true) - $start)." seconds</comment>");
    }
}
