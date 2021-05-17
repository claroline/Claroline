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

use Claroline\AppBundle\Log\LoggableTrait;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Yaml\Yaml;

class TranslationManager implements LoggerAwareInterface
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
            $translations = [];
        }

        //this should be recursive
        foreach (array_keys($mainTranslations) as $requiredKey) {
            if (!array_key_exists($requiredKey, $translations)) {
                $translations[$requiredKey] = [];
                $translations[$requiredKey] = is_array($mainTranslations[$requiredKey]) ?
                    $this->recursiveFill($mainTranslations[$requiredKey], $translations[$requiredKey]) :
                    $requiredKey;
            }
        }

        return $translations;
    }

    private function recursiveRemove($mainTranslations, $translations)
    {
        foreach (array_keys($translations) as $key) {
            if (!array_key_exists($key, $mainTranslations)) {
                unset($translations[$key]);
            }
        }

        return $translations;
    }
}
