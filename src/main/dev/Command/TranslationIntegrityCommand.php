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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TranslationIntegrityCommand extends Command
{
    const BASE_LANG = 'fr';

    protected function configure()
    {
        $this->setName('claroline:debug:translations')
            ->setDescription('Show translations integrity informations. This command ignore arrays');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $translationFiles = $this->getTranslationFiles();
        $output->writeln('<comment> Analysing '.self::BASE_LANG.' translations... </comment>');
        $frenchLations = $this->getLangFiles($translationFiles, self::BASE_LANG);
        $duplicates = $this->getDuplicates($frenchLations);
        $this->displayDuplicateErrors($duplicates, $output);

        return 0;
    }

    private function getTranslationFiles()
    {
        $pluginManager = $this->getContainer()->get('claroline.manager.plugin_manager');
        $bundles = $pluginManager->getInstalledBundles();
        $translationFiles = [];

        foreach ($bundles as $bundle) {
            $parts = explode('\\', get_class($bundle));
            $shortName = end($parts);

            if ($pluginManager->isLoaded($shortName)) {
                $translationFiles = array_unique(array_merge($this->parseDirectoryTranslationFiles($shortName), $translationFiles));
            }
        }

        //then we need to add the corebundle
        $translationFiles = array_unique(array_merge($this->parseDirectoryTranslationFiles('ClarolineCoreBundle'), $translationFiles));

        return $translationFiles;
    }

    private function getDuplicates($translationFiles)
    {
        $duplicates = [];

        foreach ($translationFiles as $translationFile) {
            $translations = json_decode(file_get_contents($translationFile), true);
            $line = 0;

            if (!$translations) {
                $duplicates['empty'][] = $translationFile;
            } else {
                foreach ($translations as $key => $value) {
                    ++$line;

                    $duplicates['key'][$key][] = [
                        $line,
                        $this->getDomainFromFileName($translationFile),
                        $this->getBundleFromFileName($translationFile),
                        $key,
                    ];

                    if (is_string($value)) {
                        $duplicates['value'][$value][] = [
                            $line,
                            $this->getDomainFromFileName($translationFile),
                            $this->getBundleFromFileName($translationFile),
                            $value,
                        ];
                    } else {
                        $duplicates['array'][] = [
                            $line,
                            $this->getDomainFromFileName($translationFile),
                            $this->getBundleFromFileName($translationFile),
                            $key,
                        ];
                    }
                }
            }
        }

        return $duplicates;
    }

    private function getLangFiles($translationFiles, $lang)
    {
        $langFiles = [];

        foreach ($translationFiles as $translationFile) {
            $parts = explode('/', $translationFile);
            $end = end($parts);
            if ($lang === explode('.', $end)[1]) {
                $langFiles[] = $translationFile;
            }
        }

        return $langFiles;
    }

    private function getDomainFromFileName($file)
    {
        $parts = explode('/', $file);
        $end = end($parts);

        return explode('.', $end)[0];
    }

    private function getBundleFromFileName($file)
    {
        $startsAt = strpos($file, '/distribution/plugin/') + strlen('/distribution/plugin/');
        $endsAt = strpos($file, '/Resources', $startsAt);
        $result = substr($file, $startsAt, $endsAt - $startsAt);

        if (strpos($result, '/')) {
            $result = 'core';
        }

        return $result;
    }

    private function parseDirectoryTranslationFiles($shortName)
    {
        $translationFiles = [];
        $translationDir = $this->getContainer()->get('kernel')->locateResource('@'.$shortName.'/Resources/translations');
        $iterator = new \DirectoryIterator($translationDir);

        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile()) {
                $translationFiles[] = realpath($fileinfo->getPathname());
            }
        }

        return $translationFiles;
    }

    private function displayDuplicateErrors($duplicates, OutputInterface $output)
    {
        $output->writeln('<comment> Displaying duplicate keys result: </comment>');
        $totalDuplicates = 0;
        $totalLines = 0;

        foreach ($duplicates['key'] as $key => $values) {
            if (count($values) > 1) {
                $output->writeln("<error>Key \"{$key}\" as duplicatas:</error>");
                ++$totalDuplicates;
                foreach ($values as $value) {
                    ++$totalLines;
                    $output->writeln("  <comment>{$value[2]}/translations/{$value[1]}.fr.yml line {$value[0]}</comment>");
                }
            }
        }

        $output->writeln('<comment> Displaying duplicate translations result: </comment>');

        foreach ($duplicates['value'] as $key => $values) {
            if (count($values) > 1) {
                $output->writeln("<error>Translations \"{$key}\" as duplicatas:</error>");
                ++$totalDuplicates;
                foreach ($values as $value) {
                    ++$totalLines;
                    $output->writeln("  <comment>{$value[2]}:{$value[1]}.fr.yml line {$value[0]}</comment>");
                }
            }
        }

        $output->writeln('<comment> Displaying array translations result: </comment>');

        foreach ($duplicates['array'] as $key => $value) {
            $output->writeln("  <error>Array found at {$value[2]}:{$value[1]}.fr.yml line {$value[0]}</error>");
        }

        $output->writeln(' ');
        $output->writeln("{$totalDuplicates} duplicates");
        $output->writeln("{$totalLines} lines to fix");
        $output->writeln('The lines indications are not accurate at all. Use ctrl+f to find what you search.');
    }
}
