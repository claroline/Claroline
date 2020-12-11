<?php

namespace Claroline\CoreBundle\Library\Normalizer;

class DateRangeNormalizer
{
    /**
     * The transfer date format.
     * It must be a PHP valid format.
     *
     * @var string
     */
    const DATE_FORMAT = 'Y-m-d\TH:i:s';

    /**
     * Normalizes two DateTimes to an array of date strings.
     */
    public static function normalize(\DateTimeInterface $startDate = null, \DateTimeInterface $endDate = null): array
    {
        if (!empty($startDate) || !empty($endDate)) {
            return [
                DateNormalizer::normalize($startDate),
                DateNormalizer::normalize($endDate),
            ];
        }

        return [];
    }

    /**
     * Denormalizes an array of date strings into DateTime objects.
     */
    public static function denormalize(array $dateRange = []): array
    {
        if (!empty($dateRange)) {
            return [
                DateNormalizer::denormalize($dateRange[0]),
                DateNormalizer::denormalize($dateRange[1]),
            ];
        }

        return [null, null];
    }
}
