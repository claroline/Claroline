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
use Symfony\Component\Console\Output\OutputInterface;

class DumpTranslationsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:translations:dump')->setDescription('Dump the translations in the web folder');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $translations = $this->getTranslationFiles();
        $builtTranslations = [];

        foreach ($translations as $translation) {
            $builtTranslations = $this->buildTranslations($translation, $builtTranslations);
        }

        $this->dump($builtTranslations);
    }

    //copied from the debug translation method
    private function getTranslationFiles()
    {
        $pluginManager = $this->getContainer()->get('claroline.manager.plugin_manager');
        $bundles = $pluginManager->getInstalledBundles();
        $translationFiles = [];

        foreach ($bundles as $bundle) {
            $parts = explode('\\', get_class($bundle['instance']));
            $shortName = end($parts);

            if ($pluginManager->isLoaded($shortName)) {
                $translationFiles = array_unique(array_merge($this->parseDirectoryTranslationFiles($shortName), $translationFiles));
            }
        }

        //then we need to add the corebundle
        $translationFiles = array_unique(array_merge($this->parseDirectoryTranslationFiles('ClarolineCoreBundle'), $translationFiles));

        return $translationFiles;
    }

    //copied from the debug translation method
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

    private function buildTranslations($translation, array $builtTranslations)
    {
        $file = pathinfo($translation, PATHINFO_BASENAME);
        //we need to concatenate translations here
        if (isset($builtTranslations[$file])) {
            $data = array_merge($builtTranslations[$file], json_decode(file_get_contents($translation), true));
        } else {
            $data = json_decode(file_get_contents($translation), true);
        }

        $builtTranslations[$file] = $data;

        return $builtTranslations;
    }

    private function dump($buildTranslations)
    {
        $translationDir = $this->getContainer()->getParameter('kernel.root_dir').'/../web/js/translations';
        if (!is_dir($translationDir)) {
            mkdir($translationDir);
        }

        foreach ($buildTranslations as $file => $data) {
            file_put_contents($translationDir.'/'.$file, json_encode($data));
        }
    }
}
