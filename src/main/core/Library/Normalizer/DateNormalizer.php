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
     */
    public static function normalize(\DateTimeInterface $date = null): ?string
    {
        if (!empty($date)) {
            return $date->format(static::DATE_FORMAT);
        }

        return null;
    }

    /**
     * Denormalizes a string into a DateTime object.
     */
    public static function denormalize(string $dateString = null): ?\DateTimeInterface
    {
        if (!empty($dateString)) {
            try {
                $dateObject = \DateTime::createFromFormat(static::DATE_FORMAT, trim($dateString));
                if (false !== $dateObject) {
                    return $dateObject;
                }
            } catch (\Exception $e) {
            }
        }

        return null;
    }
}
