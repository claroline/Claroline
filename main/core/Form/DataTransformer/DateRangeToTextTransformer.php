<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class DateRangeToTextTransformer implements DataTransformerInterface
{
    protected $translator;

    public function __construct($translator)
    {
        $this->translator = $translator;
    }

    /**
     * Transforms dateArray (2 dates) to a string.
     *
     * @param array|null $dateArray
     *
     * @return string
     */
    public function transform($dateArray)
    {
        $startDate = time();
        $endDate = time();
        if ($dateArray != null) {
            if (array_key_exists(0, $dateArray)) {
                $startDate = $dateArray[0];
            }
            if (array_key_exists(1, $dateArray)) {
                $endDate = $dateArray[1];
            }

            $format = $this->translator->trans('date_range.format', array(), 'platform');
            $separator = $this->translator->trans('date_range.separator', array(), 'platform');
            $outputValue = date($format, $startDate).' '.$separator.' '.date($format, $endDate);
            if ($startDate == $endDate) {
                $outputValue = date($format, $startDate);
            }

            return $outputValue;
        }
    }

    /**
     * Transforms a string to an array of 2 dates.
     *
     * @param string $string
     *
     * @return array of strings (names for tags)
     */
    public function reverseTransform($string)
    {
        $startDate = time();
        $endDate = time();
        $separator = $this->translator->trans('date_range.separator', array(), 'platform');

        if ($string != null) {
            $array = explode(' '.$separator.' ', $string);

            if (array_key_exists(0, $array)) {
                $dateFormat = $this->translator->trans('date_range.format', array(), 'platform');
                $startDate = $endDate = \DateTime::createFromFormat($dateFormat, $array[0])->getTimestamp();

                if (array_key_exists(1, $array)) {
                    $endDate = \DateTime::createFromFormat($dateFormat, $array[1])->getTimestamp();
                }
            }

            return array($startDate, $endDate);
        }
    }
}
