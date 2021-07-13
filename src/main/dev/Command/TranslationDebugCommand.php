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

use Claroline\DevBundle\Manager\TranslationManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class TranslationDebugCommand extends Command
{
    private $translationManager;

    public function __construct(TranslationManager $translationManager)
    {
        $this->translationManager = $translationManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Search the translations and order them in their different config.yml files');
        $this->setDefinition(
            [
                new InputArgument('locale', InputArgument::REQUIRED, 'The locale to fill.'),
            ]
        );
        $this->addOption(
            'domain',
            null,
            InputOption::VALUE_REQUIRED,
            'Wich domain do you want to fill ?'
        );
        $this->addOption(
            'main_lang',
            null,
            InputOption::VALUE_REQUIRED,
            'Which language already contains every translation ?'
        );
        $this->addOption(
            'fqcn',
            null,
            InputOption::VALUE_REQUIRED,
            'What is the bundle fqcn ?'
        );
        $this->addOption(
            'fill',
            'f',
            InputOption::VALUE_NONE,
            'Override the translations file'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fqcn = $input->getOption('fqcn') ? $input->getOption('fqcn') : 'ClarolineCoreBundle';
        $domain = $input->getOption('domain') ? $input->getOption('domain') : 'platform';
        $locale = $input->getArgument('locale');
        $mainLang = $input->getOption('main_lang') ? $input->getOption('main_lang') : 'fr';
        $filledShortPath = '@'.$fqcn.'/Resources/translations/'.$domain.'.'.$locale.'.yml';
        $mainShortPath = '@'.$fqcn.'/Resources/translations/'.$domain.'.'.$mainLang.'.yml';
        $mainFile = $this->getApplication()->getKernel()->locateResource($mainShortPath);
        $filledFile = $this->getApplication()->getKernel()->locateResource($filledShortPath);
        if ($input->getOption('fill')) {
            $this->translationManager->fill($mainFile, $filledFile);
        }
        $this->showUntranslated($filledFile, $output, $locale);

        return 0;
    }

    private function showUntranslated($filledFile, OutputInterface $output, $locale)
    {
        $displayWarning = true;
        $line = 1;
        $translations = Yaml::parse($filledFile);
        $safe = $this->getSafeDubious();

        foreach ($translations as $key => $value) {
            if ($key === $value) {
                if (!in_array($key, $safe[$locale])) {
                    if ($displayWarning) {
                        $output->writeln('<comment> These lines may contain incorrect translations </comment>');
                        $displayWarning = false;
                    }
                    $output->writeln(sprintf('line %s - %s', $line, $key));
                }
            }

            ++$line;
        }
    }

    private function getSafeDubious()
    {
        return [
            'en' => [
                'by', 'dsn',
            ],
            'fr' => [
                'dsn',
            ],
            'es' => [
                'dsn',
            ],
            'nl' => [
            ],
            'de' => [
            ],
        ];
    }
}
