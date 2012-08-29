<?php

namespace Claroline\CoreBundle\Command;

use \DirectoryIterator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;

class CompileLessCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:less:compile')
            ->setDescription('Compiles the less files.');
        $this->addOption(
            'theme', 'thm', InputOption::VALUE_OPTIONAL, 'Selects the theme that will be compiled'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $themesDir = realpath(__DIR__ . '/../Resources/less/themes');
        $themesToCompile = array();

        if (null !== $theme = $input->getOption('theme')) {
            $themesToCompile[] = $theme;
        } else {
            $themeDirs = new DirectoryIterator($themesDir);

            foreach ($themeDirs as $themeDir) {
                if ($themeDir->isDir() && !$themeDir->isDot()) {
                    $themesToCompile[] = $themeDir->getBasename();
                }
            }
        }

        foreach ($themesToCompile as $theme)
        {
            $expectedFiles = array(
                "{$themesDir}/{$theme}/variables.less",
                "{$themesDir}/{$theme}/theme.less"
            );

            foreach ($expectedFiles as $expectedFile) {
                if (!file_exists($expectedFile)) {
                    $msg = '<error>'
                        . "Cannot find '{$expectedFile}'...\n"
                        . "-> Theme '{$theme}' won't be compiled"
                        . '</error>';
                    $output->writeln($msg);
                    continue 2;
                }
            }

            $this->compileTheme($theme);
        }
    }

    private function compileTheme($theme)
    {
        $themeRelativePath = "../../src/core/Claroline/CoreBundle/Resources/less/themes/{$theme}";
        $preLessFiles = array(
            __DIR__ . '/../Resources/less/bootstrap/bootstrap.less.pre'
                => 'bootstrap.css',
            __DIR__ . '/../Resources/less/bootstrap/responsive.less.pre'
                => 'bootstrap-responsive.css'
        );


        foreach ($preLessFiles as $preLessFile => $targetCssFile) {
            $fp = fopen($preLessFile, 'r');
            $preProcessedContent = '';

            while ($line = fgets($fp)) {
                if (preg_match('#\s*@import\s+"(.+)"\s*;#', $line, $matches)) {
                    $line = str_replace(
                        $matches[1],
                        "../../vendor/claroline/front-end-bundle/Claroline/Bundle/FrontEndBundle/Resources/public/twitter-bootstrap/less/{$matches[1]}",
                        $line
                    );
                }

                if (preg_match('#^\s*@variables\s*?#', $line, $matches)) {
                    $line = "@import \"{$themeRelativePath}/variables.less\";\n";
                }

                if (preg_match('#^\s*@layout\s*?#', $line, $matches)) {
                    $line = '@import "../../src/core/Claroline/CoreBundle/Resources/less/layout.less";';
                }

                if (preg_match('#^\s*@theme\s*?#', $line, $matches)) {
                    $line = "@import \"{$themeRelativePath}/theme.less\";\n";
                }

                $preProcessedContent .= $line;
            }

            fclose($fp);
            $tmpLessFile = __DIR__ . '/../../../../../app/cache/' . uniqid();
            file_put_contents($tmpLessFile, $preProcessedContent);
            $this->doCompile($tmpLessFile, $targetCssFile, $theme);
            unlink($tmpLessFile);
        }
    }

    private function doCompile($lessFile, $cssFile, $theme)
    {
        $publicCssThemesDir = __DIR__ . '/../Resources/public/css/themes';
        $targetThemeDir = "$publicCssThemesDir/{$theme}";

        if (!is_dir($targetThemeDir)) {
            mkdir($targetThemeDir);
        }

        system("lessc {$lessFile} > {$targetThemeDir}/{$cssFile}");

        $themeImgDir = __DIR__ . "/../Resources/less/themes/{$theme}/img";

        if (is_dir($themeImgDir)) {
            $fileSystem = new FileSystem();
            $fileSystem->mirror($themeImgDir, "{$publicCssThemesDir}/{$theme}/img");
        }


        /*
        if (is_dir($themeImgDir)) {
            $copyItem = function ($source, $destination, $isDir = false) use ($copyItem) {
                if (!$isDir) {
                    if (!copy($source, $destination)) {
                        $output->writeln("<error>Cannot copy '{$source}' to '{$destination}'");
                    }
                } else {
                    mkdir($destination);
                    $sourceItems = new DirectoryIterator($source);

                    foreach ($sourceItems as $item) {
                        $copyItem();
                    }
                }
            };
        }*/
    }
}