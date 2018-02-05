<?php

namespace Claroline\CoreBundle\Library\Normalizer;

class DateNormalizer
{
    /**
     * The transfer date format.
     * It must be a PHP valid format.
     *
     * @var string
     */
    const DATE_FORMAT = 'Y-m-d\TH:i:s';

    /**
     * Normalizes a DateTime to a string.
     *
     * @param \DateTime|null $date
     *
     * @return string|null
     */
    public static function normalize(\DateTime $date = null)
    {
        if (!empty($date)) {
            return $date->format(static::DATE_FORMAT);
        }

        return null;
    }

    /**
     * Denormalizes a string into a DateTime object.
     *
     * @param string $dateString
     *
     * @return \DateTime|null
     */
    public static function denormalize($dateString = null)
    {
        if (!empty($dateString)) {
            try {
                $dateTime = \DateTime::createFromFormat(static::DATE_FORMAT, $dateString);

                return $dateTime;
            } catch (\Exception $e) {
            }
        }

        return null;
    }
}
