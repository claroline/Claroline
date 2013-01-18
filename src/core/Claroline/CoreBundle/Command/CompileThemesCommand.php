<?php

namespace Claroline\CoreBundle\Command;

use \DirectoryIterator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * This command compiles the bootstrap theme(s) located in "Resources/less/themes".
 *
 * If no specific theme is passed as argument, all the themes are compiled.
 *
 * For each theme, compilation follows these steps :
 *
 * - The ".less.pre" files from "Resources/bootstrap" are preprocessed (true less
 *   files are generated in the cache, resolving all the @import directives as well
 *   as the special directives related to the current theme).
 * - The generated less files are compiled into css files whithin a dedicated theme
 *   folder in the public css directory.
 * - The "img" directory of the theme, if any, is mirrored in the public css theme
 *   directory.
 */
class CompileThemesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:themes:compile')
            ->setDescription('Compiles the core less/bootstrap themes.');
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

        foreach ($themesToCompile as $theme) {
            $output->writeln("Compiling theme {$theme}...");

            // Each theme must include the following files
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
            $output->writeln('Done');
        }
    }

    private function compileTheme($theme)
    {
        // All the relative paths below are relative to the cache
        // directory, where the valid less files are generated
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
                // Makes all the @import urls point to the bootstrap package (FrontEndBundle)
                if (preg_match('#\s*@import\s+"(.+)"\s*;#', $line, $matches)) {
                    $frontRelativePath = "../../vendor/claroline/front-end-bundle/Claroline/Bundle/FrontEndBundle";
                    $line = str_replace(
                        $matches[1],
                        "{$frontRelativePath}/Resources/public/twitter-bootstrap/less/{$matches[1]}",
                        $line
                    );
                }

                // Imports the theme variables
                if (preg_match('#^\s*@variables\s*?#', $line, $matches)) {
                    $line = "@import \"{$themeRelativePath}/variables.less\";\n";
                }

                // Imports the common layout
                if (preg_match('#^\s*@layout\s*?#', $line, $matches)) {
                    $line = '@import "../../src/core/Claroline/CoreBundle/Resources/less/layout.less";';
                }

                // Imports the theme main file
                if (preg_match('#^\s*@theme\s*?#', $line, $matches)) {
                    $line = "@import \"{$themeRelativePath}/theme.less\";\n";
                }

                $preProcessedContent .= $line;
            }

            fclose($fp);
            // Generates a less file in the cache, compiles it, then removes it
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
    }
}