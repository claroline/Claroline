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

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Manager\File\ArchiveManager;
use Claroline\CoreBundle\Manager\PluginManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class TranslationExportCommand extends Command
{
    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly PluginManager $pluginManager,
        private readonly ArchiveManager $archiveManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Creates a zip archive which contains all the translation files of the platform in a simplified directory structure.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $translationFiles = new FileBag();
        $this->getTranslationFiles($output, $translationFiles);

        $this->archiveManager->create($this->kernel->getProjectDir().DIRECTORY_SEPARATOR.'translations.zip', $translationFiles);

        return 0;
    }

    private function getTranslationFiles(OutputInterface $output, FileBag $fileBag): void
    {
        $bundles = $this->pluginManager->getInstalledBundles();

        foreach ($bundles as $bundle) {
            if ($this->pluginManager->isLoaded($bundle->getName())) {
                $this->getBundleTranslationFiles($output, $bundle, $fileBag);
            }
        }
    }

    private function getBundleTranslationFiles(OutputInterface $output, BundleInterface $bundle, FileBag $fileBag): void
    {
        try {
            $path = str_replace($this->kernel->getProjectDir(), '', $bundle->getPath());
            $path = str_replace('src', '', $path);
            $path = str_replace('vendor', '', $path);
            $path = trim($path, DIRECTORY_SEPARATOR);

            $output->writeln('<comment> Add translations from bundle '.$bundle->getName().' ('.$path.')</comment>', OutputInterface::VERBOSITY_VERBOSE);

            $translationDir = $this->kernel->locateResource('@'.$bundle->getName().'/Resources/translations');
            $iterator = new \DirectoryIterator($translationDir);

            foreach ($iterator as $fileInfo) {
                if ($fileInfo->isFile()) {
                    $output->writeln('<info>    - Add file '.$fileInfo->getFilename().'</info>', OutputInterface::VERBOSITY_VERY_VERBOSE);
                    $fileBag->add(
                        $path.DIRECTORY_SEPARATOR.$fileInfo->getFilename(),
                        realpath($fileInfo->getPathname())
                    );
                }
            }
        } catch (\Exception $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');
        } // kernel will throw an exception if no translations is defined
    }
}
