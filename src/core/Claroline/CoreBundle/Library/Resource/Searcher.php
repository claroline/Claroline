<?php

namespace Claroline\CoreBundle\Library\Resource;

class Searcher
{
    public function createSearchArray($criterias)
    {
        $compiledArray = array();
        $types = array();
        $roots = array();
        //get the criterias list as an array
        foreach ($criterias as $key => $item) {

            if (substr_count($key, 'types') > 0) {
                $types[] = $item;
            }
            if (substr_count($key, 'roots') > 0) {
                $roots[] = $item;
            }
        }

        if (count($types) != 0) {
            $compiledArray['types'] = $types;
        }

        if (count($roots) != 0) {
            $compiledArray['roots'] = $roots;
        }

        if (array_key_exists('dateTo', $criterias)) {
            $compiledArray['dateTo'] = $criterias['dateTo'];
        }

        if (array_key_exists('dateFrom', $criterias)) {
            $compiledArray['dateFrom'] = $criterias['dateFrom'];
        };

        return $compiledArray;
    }
}
