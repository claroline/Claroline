<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DevBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * @DI\Service("claroline.dev_manager.translation_manager")
 */
class TranslationManager
{
    use LoggableTrait;

    public function fill($mainFile, $filledFile)
    {
        if (!$filledFile) {
            touch($filledFile);
        }
        $this->log("Filling the translation file {$filledFile}");
        $mainTranslations = Yaml::parse($mainFile);
        $translations = Yaml::parse($filledFile);

        $translations = $this->recursiveFill($mainTranslations, $translations);
        $translations = $this->recursiveRemove($mainTranslations, $translations);

        ksort($translations);
        $yaml = Yaml::dump($translations);
        file_put_contents($filledFile, $yaml);
    }

    private function recursiveFill($mainTranslations, $translations)
    {
        if (!is_array($translations)) {
            $translations = array();
        }

        //this should be recursive
        foreach (array_keys($mainTranslations) as $requiredKey) {
            if (!array_key_exists($requiredKey, $translations)) {
                $translations[$requiredKey] = array();
                $translations[$requiredKey] = is_array($mainTranslations[$requiredKey]) ?
                    $this->recursiveFill($mainTranslations[$requiredKey], $translations[$requiredKey]) :
                    $requiredKey;
            }
        }

        return $translations;
    }

    private function recursiveRemove($mainTranslations, $translations)
    {
        foreach ($translations as $key => $value) {
            if (!array_key_exists($key, $mainTranslations)) {
                unset($translations[$key]);
            }
            //this won't work but it's not really important.
            //if (is_array($mainTranslation[$key])) $this->recursiveRemove($mainTranslations[$key], $translations[$key]);
        }

        return $translations;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
