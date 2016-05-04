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

use Symfony\Component\HttpFoundation\Request;

class Container
{
    private $request;
    private $appDirectory;
    private $installerDirectory;
    private $cachedServices = array();

    public function __construct(Request $request, $appDirectory)
    {
        $this->request = $request;
        $this->appDirectory = $appDirectory;
        $this->installerDirectory = __DIR__.'/../../..';
    }

    /**
     * @return string
     */
    public function getAppDirectory()
    {
        return $this->appDirectory;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return ParameterBag
     */
    public function getParameterBag()
    {
        $session = $this->request->getSession();

        if (!$session->has('parameter_bag')) {
            $session->set('parameter_bag', new ParameterBag());
        }

        return $session->get('parameter_bag');
    }

    /**
     * @return Translator
     */
    public function getTranslator()
    {
        if (!isset($this->cachedServices['translator'])) {
            $this->cachedServices['translator'] = new Translator(
                $this->installerDirectory.'/translations', 'en', 'en'
            );
        }

        $this->cachedServices['translator']->setLanguage(
            $this->getParameterBag()->getInstallationLanguage()
        );

        return $this->cachedServices['translator'];
    }

    /**
     * @return TemplateEngine
     */
    public function getTemplateEngine()
    {
        if (!isset($this->cachedServices['templating'])) {
            $templating = new TemplateEngine($this->installerDirectory.'/templates');
            $baseUrl = $this->request->getBaseUrl();
            $templating->addHelpers(
                array(
                    'getLangs' => function () {
                        return $this->getLangs();
                    },
                    'getIp' => function () {
                        return $this->getClientIp();
                    },
                    'getURL' => function () {
                        return $this->getURL();
                    },
                    'getCountry' => function () {
                        return $this->getParameterBag()->getCountry();
                    },
                    'getLang' => function () {
                        return $this->getParameterBag()->getPlatformSettings()->getLanguage();
                    },
                    'getEmail' => function () {
                        return $this->getParameterBag()->getPlatformSettings()->getSupportEmail();
                    },
                    'getCountries' => function () {
                        return $this->getCountries();
                    },
                    'getVersion' => function () {
                        return $this->getVersion();
                    },
                    'trans' => $this->getTranslator()->toClosure(),
                    'path' => function ($path) use ($baseUrl) {
                        return rtrim($baseUrl.$path, '/');
                    },
                    'value' => function (array $search, $key) {
                        if (isset($search[$key])) {
                            return $search[$key];
                        }
                    },
                )
            );
            $this->cachedServices['templating'] = $templating;
        }

        return $this->cachedServices['templating'];
    }

    /**
     *
     */
    public function getVersion()
    {
        $packages = json_decode(file_get_contents($this->appDirectory.'/../vendor/composer/installed.json'));

        foreach ($packages as $package) {
            if ($package instanceof \stdClass && $package->name === 'claroline/core-bundle') {
                return $package->version;
            }
        }

        return '-';
    }

    /**
     * Function to get the client IP address.
     */
    public function getClientIp()
    {
        switch (true) {
            case $ip = getenv('HTTP_CLIENT_IP') : break;
            case $ip = getenv('HTTP_X_FORWARDED_FOR') : break;
            case $ip = getenv('HTTP_X_FORWARDED') : break;
            case $ip = getenv('HTTP_FORWARDED_FOR') : break;
            case $ip = getenv('HTTP_FORWARDED') : break;
            case $ip = getenv('REMOTE_ADDR') : break;
            default: $ip = '192.168.1.1';
        }

        return $ip;
    }

    /**
     * Get current URL.
     */
    public function getURL()
    {
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';

        if (isset($_SERVER['SERVER_NAME'])) {
            $url .= $_SERVER['SERVER_NAME'];
        }

        if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80') {
            return $url .= ':'.$_SERVER['SERVER_PORT'];
        }

        if (isset($_SERVER['REQUEST_URI'])) {
            $url .= $_SERVER['REQUEST_URI'];
        }

        return $url;
    }

    /**
     * Get list of contries.
     */
    public function getCountries()
    {
        return array(
            'AF' => 'Afghanistan',
            'AL' => 'Albania',
            'DZ' => 'Algeria',
            'AS' => 'American Samoa',
            'AD' => 'Andorra',
            'AO' => 'Angola',
            'AI' => 'Anguilla',
            'AQ' => 'Antarctica',
            'AG' => 'Antigua and Barbuda',
            'AR' => 'Argentina',
            'AM' => 'Armenia',
            'AW' => 'Aruba',
            'AU' => 'Australia',
            'AT' => 'Austria',
            'AZ' => 'Azerbaijan',
            'BS' => 'Bahamas',
            'BH' => 'Bahrain',
            'BD' => 'Bangladesh',
            'BB' => 'Barbados',
            'BY' => 'Belarus',
            'BE' => 'Belgium',
            'BZ' => 'Belize',
            'BJ' => 'Benin',
            'BM' => 'Bermuda',
            'BT' => 'Bhutan',
            'BO' => 'Bolivia',
            'BA' => 'Bosnia and Herzegovina',
            'BW' => 'Botswana',
            'BV' => 'Bouvet Island',
            'BR' => 'Brazil',
            'BQ' => 'British Antarctic Territory',
            'IO' => 'British Indian Ocean Territory',
            'VG' => 'British Virgin Islands',
            'BN' => 'Brunei',
            'BG' => 'Bulgaria',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi',
            'KH' => 'Cambodia',
            'CM' => 'Cameroon',
            'CA' => 'Canada',
            'CT' => 'Canton and Enderbury Islands',
            'CV' => 'Cape Verde',
            'KY' => 'Cayman Islands',
            'CF' => 'Central African Republic',
            'TD' => 'Chad',
            'CL' => 'Chile',
            'CN' => 'China',
            'CX' => 'Christmas Island',
            'CC' => 'Cocos [Keeling] Islands',
            'CO' => 'Colombia',
            'KM' => 'Comoros',
            'CG' => 'Congo - Brazzaville',
            'CD' => 'Congo - Kinshasa',
            'CK' => 'Cook Islands',
            'CR' => 'Costa Rica',
            'HR' => 'Croatia',
            'CU' => 'Cuba',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'CI' => 'Côte d’Ivoire',
            'DK' => 'Denmark',
            'DJ' => 'Djibouti',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'NQ' => 'Dronning Maud Land',
            'DD' => 'East Germany',
            'EC' => 'Ecuador',
            'EG' => 'Egypt',
            'SV' => 'El Salvador',
            'GQ' => 'Equatorial Guinea',
            'ER' => 'Eritrea',
            'EE' => 'Estonia',
            'ET' => 'Ethiopia',
            'FK' => 'Falkland Islands',
            'FO' => 'Faroe Islands',
            'FJ' => 'Fiji',
            'FI' => 'Finland',
            'FR' => 'France',
            'GF' => 'French Guiana',
            'PF' => 'French Polynesia',
            'TF' => 'French Southern Territories',
            'FQ' => 'French Southern and Antarctic Territories',
            'GA' => 'Gabon',
            'GM' => 'Gambia',
            'GE' => 'Georgia',
            'DE' => 'Germany',
            'GH' => 'Ghana',
            'GI' => 'Gibraltar',
            'GR' => 'Greece',
            'GL' => 'Greenland',
            'GD' => 'Grenada',
            'GP' => 'Guadeloupe',
            'GU' => 'Guam',
            'GT' => 'Guatemala',
            'GG' => 'Guernsey',
            'GN' => 'Guinea',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HT' => 'Haiti',
            'HM' => 'Heard Island and McDonald Islands',
            'HN' => 'Honduras',
            'HK' => 'Hong Kong SAR China',
            'HU' => 'Hungary',
            'IS' => 'Iceland',
            'IN' => 'India',
            'ID' => 'Indonesia',
            'IR' => 'Iran',
            'IQ' => 'Iraq',
            'IE' => 'Ireland',
            'IM' => 'Isle of Man',
            'IL' => 'Israel',
            'IT' => 'Italy',
            'JM' => 'Jamaica',
            'JP' => 'Japan',
            'JE' => 'Jersey',
            'JT' => 'Johnston Island',
            'JO' => 'Jordan',
            'KZ' => 'Kazakhstan',
            'KE' => 'Kenya',
            'KI' => 'Kiribati',
            'KW' => 'Kuwait',
            'KG' => 'Kyrgyzstan',
            'LA' => 'Laos',
            'LV' => 'Latvia',
            'LB' => 'Lebanon',
            'LS' => 'Lesotho',
            'LR' => 'Liberia',
            'LY' => 'Libya',
            'LI' => 'Liechtenstein',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'MO' => 'Macau SAR China',
            'MK' => 'Macedonia',
            'MG' => 'Madagascar',
            'MW' => 'Malawi',
            'MY' => 'Malaysia',
            'MV' => 'Maldives',
            'ML' => 'Mali',
            'MT' => 'Malta',
            'MH' => 'Marshall Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MU' => 'Mauritius',
            'YT' => 'Mayotte',
            'FX' => 'Metropolitan France',
            'MX' => 'Mexico',
            'FM' => 'Micronesia',
            'MI' => 'Midway Islands',
            'MD' => 'Moldova',
            'MC' => 'Monaco',
            'MN' => 'Mongolia',
            'ME' => 'Montenegro',
            'MS' => 'Montserrat',
            'MA' => 'Morocco',
            'MZ' => 'Mozambique',
            'MM' => 'Myanmar [Burma]',
            'NA' => 'Namibia',
            'NR' => 'Nauru',
            'NP' => 'Nepal',
            'NL' => 'Netherlands',
            'AN' => 'Netherlands Antilles',
            'NT' => 'Neutral Zone',
            'NC' => 'New Caledonia',
            'NZ' => 'New Zealand',
            'NI' => 'Nicaragua',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'NU' => 'Niue',
            'NF' => 'Norfolk Island',
            'KP' => 'North Korea',
            'VD' => 'North Vietnam',
            'MP' => 'Northern Mariana Islands',
            'NO' => 'Norway',
            'OM' => 'Oman',
            'PC' => 'Pacific Islands Trust Territory',
            'PK' => 'Pakistan',
            'PW' => 'Palau',
            'PS' => 'Palestinian Territories',
            'PA' => 'Panama',
            'PZ' => 'Panama Canal Zone',
            'PG' => 'Papua New Guinea',
            'PY' => 'Paraguay',
            'YD' => "People's Democratic Republic of Yemen",
            'PE' => 'Peru',
            'PH' => 'Philippines',
            'PN' => 'Pitcairn Islands',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'PR' => 'Puerto Rico',
            'QA' => 'Qatar',
            'RO' => 'Romania',
            'RU' => 'Russia',
            'RW' => 'Rwanda',
            'RE' => 'Réunion',
            'BL' => 'Saint Barthélemy',
            'SH' => 'Saint Helena',
            'KN' => 'Saint Kitts and Nevis',
            'LC' => 'Saint Lucia',
            'MF' => 'Saint Martin',
            'PM' => 'Saint Pierre and Miquelon',
            'VC' => 'Saint Vincent and the Grenadines',
            'WS' => 'Samoa',
            'SM' => 'San Marino',
            'SA' => 'Saudi Arabia',
            'SN' => 'Senegal',
            'RS' => 'Serbia',
            'CS' => 'Serbia and Montenegro',
            'SC' => 'Seychelles',
            'SL' => 'Sierra Leone',
            'SG' => 'Singapore',
            'SK' => 'Slovakia',
            'SI' => 'Slovenia',
            'SB' => 'Solomon Islands',
            'SO' => 'Somalia',
            'ZA' => 'South Africa',
            'GS' => 'South Georgia and the South Sandwich Islands',
            'KR' => 'South Korea',
            'ES' => 'Spain',
            'LK' => 'Sri Lanka',
            'SD' => 'Sudan',
            'SR' => 'Suriname',
            'SJ' => 'Svalbard and Jan Mayen',
            'SZ' => 'Swaziland',
            'SE' => 'Sweden',
            'CH' => 'Switzerland',
            'SY' => 'Syria',
            'ST' => 'São Tomé and Príncipe',
            'TW' => 'Taiwan',
            'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania',
            'TH' => 'Thailand',
            'TL' => 'Timor-Leste',
            'TG' => 'Togo',
            'TK' => 'Tokelau',
            'TO' => 'Tonga',
            'TT' => 'Trinidad and Tobago',
            'TN' => 'Tunisia',
            'TR' => 'Turkey',
            'TM' => 'Turkmenistan',
            'TC' => 'Turks and Caicos Islands',
            'TV' => 'Tuvalu',
            'UM' => 'U.S. Minor Outlying Islands',
            'PU' => 'U.S. Miscellaneous Pacific Islands',
            'VI' => 'U.S. Virgin Islands',
            'UG' => 'Uganda',
            'UA' => 'Ukraine',
            'SU' => 'Union of Soviet Socialist Republics',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom',
            'US' => 'United States',
            'ZZ' => 'Unknown or Invalid Region',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VU' => 'Vanuatu',
            'VA' => 'Vatican City',
            'VE' => 'Venezuela',
            'VN' => 'Vietnam',
            'WK' => 'Wake Island',
            'WF' => 'Wallis and Futuna',
            'EH' => 'Western Sahara',
            'YE' => 'Yemen',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
            'AX' => 'Åland Islands',
        );
    }

    /**
     * Get installation languages.
     */
    public function getLangs()
    {
        $langs = scandir($this->installerDirectory.'/translations/');

        foreach ($langs as $key => $lang) {
            if (!preg_match('/^.*\.php$/', $lang)) {
                unset($langs[$key]);
            } else {
                $langs[$key] = str_replace('.php', '', $langs[$key]);
            }
        }

        return $langs;
    }

    public function getWriter()
    {
        return new Writer(
            $this->appDirectory.'/config/parameters.yml.dist',
            $this->appDirectory.'/config/parameters.yml',
            $this->appDirectory.'/config/platform_options.yml',
            $this->appDirectory.'/config/is_installed.php'
        );
    }

    public function getInstaller()
    {
        return new Installer(
            $this->getParameterBag()->getFirstAdminSettings(),
            $this->getWriter(),
            $this->appDirectory.'/AppKernel.php',
            'AppKernel'
        );
    }
}
