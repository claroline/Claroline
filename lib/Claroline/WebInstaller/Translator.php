<?php

namespace Claroline\WebInstaller;

class Translator
{
    private $language;
    private $fallbackLanguage;
    private $translationDirectory;
    private $catalogue;

    public function __construct($translationDirectory, $language, $fallbackLanguage)
    {
        $this->language = $language;
        $this->fallbackLanguage = $fallbackLanguage;
        $this->translationDirectory = $translationDirectory;
    }

    public function setLanguage($language)
    {
        if ($language !== $this->language) {
            $this->language = $language;
            $this->catalogue = $this->buildCatalogue();
        }
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function translate($key, array $parameters = array())
    {
        if (!$this->catalogue) {
            $this->catalogue = $this->buildCatalogue();
        }

        if (isset($this->catalogue[$key])) {
            return $this->catalogue[$key];
        }

        return $key;
    }

    public function toClosure()
    {
        $translator = $this;

        return function ($key, array $parameters = array()) use ($translator) {
            return $translator->translate($key, $parameters);
        };
    }

    private function buildCatalogue()
    {
        if (!file_exists($fallbackFile = $this->translationDirectory . '/' . $this->fallbackLanguage . '.php')) {
            throw new \Exception("Fallback language '{$this->fallbackLanguage}' is not available");
        }

        $translations = require $fallbackFile;

        if (file_exists($languageFile = $this->translationDirectory . '/' . $this->language . '.php')) {
            $translations = array_merge($translations, require $languageFile);
        }

        return $translations;
    }
}
