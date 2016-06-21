<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\WebInstaller;

use Claroline\CoreBundle\Library\Installation\Settings\DatabaseSettings;
use Claroline\CoreBundle\Library\Installation\Settings\FirstAdminSettings;
use Claroline\CoreBundle\Library\Installation\Settings\MailingSettings;
use Claroline\CoreBundle\Library\Installation\Settings\PlatformSettings;

class ParameterBag
{
    private $installationLanguage = 'en';
    private $databaseSettings = null;
    private $country = null;
    private $databaseValidationErrors = array();
    private $databaseGlobalError = null;
    private $platformSettings = null;
    private $platformValidationErrors = array();
    private $firstAdminSettings = null;
    private $firstAdminValidationErrors = array();
    private $mailingSettings = null;
    private $mailingValidationErrors = array();
    private $mailingGlobalError = null;
    private $hasConfirmedSendDatas = false;
    private $token = null;

    /**
     *
     */
    public function __construct()
    {
        $this->setCountry($this->ipInfo('Visitor', 'Country'));
    }

    public function setInstallationLanguage($language)
    {
        $this->installationLanguage = $language;
    }

    public function getInstallationLanguage()
    {
        return $this->installationLanguage;
    }

    public function setCountry($country)
    {
        $this->country = $country;
    }

    public function getCountry()
    {
        return $this->country;
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

    public function getPlatformSettings()
    {
        if (!$this->platformSettings) {
            $this->platformSettings = new PlatformSettings();
        }

        return $this->platformSettings;
    }

    public function setPlatformValidationErrors(array $errors)
    {
        $this->platformValidationErrors = $errors;
    }

    public function getPlatformValidationErrors()
    {
        return $this->platformValidationErrors;
    }

    public function getFirstAdminSettings()
    {
        if (!$this->firstAdminSettings) {
            $this->firstAdminSettings = new FirstAdminSettings();
        }

        return $this->firstAdminSettings;
    }

    public function setFirstAdminValidationErrors(array $errors)
    {
        $this->firstAdminValidationErrors = $errors;
    }

    public function getFirstAdminValidationErrors()
    {
        return $this->firstAdminValidationErrors;
    }

    public function getMailingSettings()
    {
        if (!$this->mailingSettings) {
            $this->mailingSettings = new MailingSettings();
        }

        return $this->mailingSettings;
    }

    public function reinitializeMailingSettings()
    {
        $this->mailingSettings = new MailingSettings();
    }

    public function setMailingValidationErrors(array $errors)
    {
        $this->mailingValidationErrors = $errors;
    }

    public function getMailingValidationErrors()
    {
        return $this->mailingValidationErrors;
    }

    public function setMailingGlobalError($error)
    {
        $this->mailingGlobalError = $error;
    }

    public function getMailingGlobalError()
    {
        return $this->mailingGlobalError;
    }
    public function getHasConfirmedSendDatas()
    {
        return $this->hasConfirmedSendDatas;
    }

    public function setHasConfirmedSendDatas($hasConfirmedSendDatas)
    {
        $this->hasConfirmedSendDatas = $hasConfirmedSendDatas;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function ipInfo($ip = null, $purpose = 'location', $deepDetect = true)
    {
        $output = null;
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            $ip = $_SERVER['REMOTE_ADDR'];
            if ($deepDetect) {
                if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                }
                if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
                }
            }
        }
        $purpose = str_replace(array('name', "\n", "\t", ' ', '-', '_'), null, strtolower(trim($purpose)));
        $support = array('country', 'countrycode', 'state', 'region', 'city', 'location', 'address');
        $continents = array(
            'AF' => 'Africa',
            'AN' => 'Antarctica',
            'AS' => 'Asia',
            'EU' => 'Europe',
            'OC' => 'Australia (Oceania)',
            'NA' => 'North America',
            'SA' => 'South America',
        );
        if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
            $ipdat = @json_decode(file_get_contents('http://www.geoplugin.net/json.gp?ip='.$ip));
            if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
                switch ($purpose) {
                    case 'location':
                        $output = array(
                            'city' => @$ipdat->geoplugin_city,
                            'state' => @$ipdat->geoplugin_regionName,
                            'country' => @$ipdat->geoplugin_countryName,
                            'country_code' => @$ipdat->geoplugin_countryCode,
                            'continent' => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                            'continent_code' => @$ipdat->geoplugin_continentCode,
                        );
                        break;
                    case 'address':
                        $address = array($ipdat->geoplugin_countryName);
                        if (@strlen($ipdat->geoplugin_regionName) >= 1) {
                            $address[] = $ipdat->geoplugin_regionName;
                        }
                        if (@strlen($ipdat->geoplugin_city) >= 1) {
                            $address[] = $ipdat->geoplugin_city;
                        }
                        $output = implode(', ', array_reverse($address));
                        break;
                    case 'city':
                        $output = @$ipdat->geoplugin_city;
                        break;
                    case 'state':
                        $output = @$ipdat->geoplugin_regionName;
                        break;
                    case 'region':
                        $output = @$ipdat->geoplugin_regionName;
                        break;
                    case 'country':
                        $output = @$ipdat->geoplugin_countryName;
                        break;
                    case 'countrycode':
                        $output = @$ipdat->geoplugin_countryCode;
                        break;
                }
            }
        }

        return $output;
    }
}
