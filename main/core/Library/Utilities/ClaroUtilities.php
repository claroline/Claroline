<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Utilities;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("claroline.utilities.misc")
 */
class ClaroUtilities
{
    private $container;
    private $hasIntl;
    private $formatter;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->hasIntl = extension_loaded('intl');
    }

    /**
     * Fill the empty value on $fillable with $array and sort it.
     *
     * Ie:
     * $fillable[4] = value4
     * $fillable[1] = value1
     * $fillable[2] = value2
     *
     * $array[] = value3
     *
     * Once the function is fired the results is
     * $fillable[1] = value1
     * $fillable[2] = value2
     * $fillable[3] = value3
     * $fillable[4] = value4
     *
     * @param array $fillable
     * @param array $array
     *
     * @return array
     */
    public function arrayFill(array $fillable, array $array)
    {
        ksort($fillable);
        $saveKey = 1;
        $filledArray = array();

        foreach ($fillable as $key => $value) {
            if ($key - $saveKey != 0) {
                while ($key - $saveKey >= 1) {
                    $filledArray[$saveKey] = array_shift($array);
                    $saveKey++;
                }
                $filledArray[$key] = $value;
            } else {
                $filledArray[$key] = $value;
            }
            $saveKey++;
        }

        if (count($array) > 0) {
            foreach ($array as $item) {
                $filledArray[] = $item;
            }
        }

        return $filledArray;
    }

    /**
     * From http://php.net/manual/en/function.time.php
     *
     * @param integer $secs
     *
     * @return string
     */
    public function timeElapsed($secs)
    {
        if ($secs === 0) {
            return '0s';
        }

        $bit = array(
            'y' => $secs / 31556926 % 12,
            'w' => $secs / 604800 % 52,
            'd' => $secs / 86400 % 7,
            'h' => $secs / 3600 % 24,
            'm' => $secs / 60 % 60,
            's' => $secs % 60
            );

        foreach ($bit as $k => $v) {
            if ($v > 0) {
                $ret[] = $v . $k;
            }
        }

        return join(' ', $ret);
    }

    /**
     * Generates a globally unique identifier.
     *
     * @see http://php.net/manual/fr/function.com-create-guid.php
     *
     * @return string
     */
    public function generateGuid()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf(
            '%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(16384, 20479),
            mt_rand(32768, 49151),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535)
        );
    }

    public function getDefaultEncoding()
    {
        $headers = $this->container->get('request')->server->getHeaders();
        $userAgent = $headers['USER_AGENT'];

        if (strpos($userAgent, 'Linux') !== false) {
            return 'ISO-8859-1';
        }

        if (strpos($userAgent, 'Windows') !== false) {
            return 'CP437';
        }

        //default
        return 'ISO-8859-1';
    }

    /*
     * Format the date according to the locale.
     */
    public function intlDateFormat($date)
    {
        if (($formatter = $this->getFormatter()) instanceof \IntlDateFormatter) {
            return $formatter->format($date);
        } elseif ($date instanceof \DateTime) {
            return $date->format('d-m-Y');
        }

        return date('d-m-Y', $date);
    }

    private function getFormatter()
    {
        if (!$this->formatter && $this->hasIntl) {
            $request = $this->container->get('request_stack')->getMasterRequest();
            $this->formatter = new \IntlDateFormatter(
                $this->container->get('claroline.common.locale_manager')->getUserLocale($request),
                \IntlDateFormatter::SHORT,
                \IntlDateFormatter::SHORT,
                date_default_timezone_get(),
                \IntlDateFormatter::GREGORIAN
            );
        }

        return $this->formatter;
    }

    /**
     * Take a file size (B) and displays it in a more readable way.
     */
    public function formatFileSize($fileSize)
    {
        //don't format if it's already formatted.
        $validUnits = array('KB', 'MB', 'GB', 'TB');

        foreach ($validUnits as $unit) {
            if (strpos($unit, $fileSize)) {
                return $fileSize;
            }
        }

        if ($fileSize < 1024) {
            return $fileSize . ' B';
        } elseif ($fileSize < 1048576) {
            return round($fileSize / 1024, 2) . ' KB';
        } elseif ($fileSize < 1073741824) {
            return round($fileSize / 1048576, 2) . ' MB';
        } elseif ($fileSize < 1099511627776) {
            return round($fileSize / 1073741824, 2) . ' GB';
        }

        return round($fileSize / 1099511627776, 2) . ' TB';
    }

    /**
     * Take a formatted file size and returns the number of bytes
     */
    public function getRealFileSize($fileSize)
    {
        //B goes at the end because it's always matched otherwise
        $validUnits = array('KB', 'MB', 'GB', 'TB');
        $value = str_replace(' ', '', $fileSize);

        $replacements = array('');
        $pattern = '/(\d+)/';
        $data = preg_grep($pattern, array($value));

        foreach ($validUnits as $unit) {
            if (strpos($fileSize, $unit)) {
                switch($unit) {
                    case 'B':
                        return $data[0] * pow(1024, 0);
                    case 'KB':
                        return $data[0] * pow(1024, 1);
                    case 'MB':
                        return $data[0] * pow(1024, 2);
                    case 'GB':
                        return $data[0] * pow(1024, 3);
                    case 'TB':
                        return $data[0] * pow(1024, 4);
                }
            }
        }

        return $fileSize;
    }

    public function formatCsvOutput($data)
    {
        $data = str_replace("\r\n", PHP_EOL, $data);
        $data = str_replace("\r", PHP_EOL, $data);
        $data = str_replace("\n", PHP_EOL, $data);

        return $data;
    }
}
