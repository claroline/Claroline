<?php
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
     * @param  Array|null $dateArray
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
        } else {

            return null;
        }

        $format = $this->translator->trans('date_range.format', array(), 'platform');
        $outputValue = date($format, $startDate).' - '.date($format, $endDate);
        if ($startDate == $endDate) {
            $outputValue = date($format, $startDate);
        }

        return $outputValue;
    }

    /**
     * Transforms a string to an array of 2 dates.
     *
     * @param  string $string
     * @return array of strings (names for tags)
     */
    public function reverseTransform($string)
    {
        $startDate = time();
        $endDate = time();

        if ($string != null) {
            $array = explode(' - ', $string);

            if (array_key_exists(0, $array)) {
                $startDate = $endDate = strtotime($array[0]);
            }
            if (array_key_exists(1, $array)) {
                $endDate = strtotime($array[1]);
            }
        } else {

            return null;
        }

        return array($startDate, $endDate);
    }
}