<?php

namespace Claroline\CoreBundle\Library\Resource;

class Searcher
{
    public function createSearchArray($criterias)
    {
        $compiledArray = array();

        //parameter => isArray
        $possibleKeys = array(
            'types' => true,
            'roots' => true,
            'mimeTypes' => true,
            'dateTo' => false,
            'dateFrom' => false,
            'name' => false,
        );

        //Get the criterias list as an array.
        //if the parameter is an array (eg 'types') the url will be type1='a'&type2='b'
        //this function concat all these types into 1 array instead of different variables.

        foreach ($possibleKeys as $type => $isArray) {
            $arr = array();
            if ($isArray) {
                foreach ($criterias as $key => $item) {
                    if (substr_count($key, $type) > 0) {
                        $arr[] = $item;
                    }
                }

                if (count($arr) > 0) {
                    $compiledArray[$type] = $arr;
                }
            } else {
                if (array_key_exists($type, $criterias)) {
                    $compiledArray[$type] = $criterias[$type];
                }
            }
        }

        return $compiledArray;
    }
}
