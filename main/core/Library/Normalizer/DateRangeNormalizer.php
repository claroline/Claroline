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
     *
     * @param \DateTime|null $startDate
     * @param \DateTime|null $endDate
     *
     * @return array
     */
    public static function normalize(\DateTime $startDate = null, \DateTime $endDate = null)
    {
        if (!empty($startDate) && !empty($endDate)) {
            return [
                DateNormalizer::normalize($startDate),
                DateNormalizer::normalize($endDate),
            ];
        }

        return [];
    }

    /**
     * Denormalizes an array of date strings into DateTime objects.
     *
     * @param array $dateRange
     *
     * @return array
     */
    public static function denormalize($dateRange = [])
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
