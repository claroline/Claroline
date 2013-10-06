<?php

namespace Claroline\WebInstaller;

use Claroline\CoreBundle\Library\Installation\Settings\DatabaseSettings;

class ParameterBag
{
    private $installationLanguage = 'en';
    private $databaseSettings = null;
    private $databaseValidationErrors = array();
    private $databaseGlobalError = null;
    private $platformLanguage = null;

    public function setInstallationLanguage($language)
    {
        $this->installationLanguage = $language;
    }

    public function getInstallationLanguage()
    {
        return $this->installationLanguage;
    }

    public function getDatabaseSettings()
    {
        if (!$this->databaseSettings) {
            $this->databaseSettings = new DatabaseSettings();
        }

        return $this->databaseSettings;
    }

    public function setDatabaseValidationErrors(array $errors)
    {
        $this->databaseValidationErrors = $errors;

        if (count($errors) > 0) {
            $this->databaseGlobalError = null;
        }
    }

    public function getDatabaseValidationErrors()
    {
        return $this->databaseValidationErrors;
    }

    public function setDatabaseGlobalError($error)
    {
        $this->databaseGlobalError = $error;
    }

    public function getDatabaseGlobalError()
    {
        return $this->databaseGlobalError;
    }

    public function setPlatformLanguage($language)
    {
        $this->platformLanguage = $language;
    }

    public function getPlatformLanguage()
    {
        return $this->platformLanguage;
    }
}
