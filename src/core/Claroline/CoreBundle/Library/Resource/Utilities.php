<?php

namespace Claroline\CoreBundle\Library\Resource;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;

class Utilities
{
    public function getUniqueName (AbstractResource $resource, AbstractResource $parent)
    {
        $children = $parent->getChildren();
        $name = $resource->getName();
        $arName = explode('~', pathinfo($name, PATHINFO_FILENAME));
        $baseName = $arName[0];
        $nbName = 0;

        if (null != $children) {
            foreach ($children as $child) {
                $childArName = explode('~', pathinfo($child->getName(), PATHINFO_FILENAME));
                $childBaseName = $childArName[0];
                if($childBaseName == $baseName && pathinfo($child->getName(),
                    PATHINFO_EXTENSION) == pathinfo($resource->getName(), PATHINFO_EXTENSION)) {
                    if(array_key_exists(1, $childArName)) {
                        $ind = $childArName[1];
                        if ($ind >= $nbName) {
                            $nbName = $ind;
                            $nbName++;
                        }
                    } else {
                        $nbName = 1;
                    }
                }
            }
        }
        if (0 != $nbName) {
            $newName = $baseName.'~'.$nbName.'.'.pathinfo($resource->getName(), PATHINFO_EXTENSION);
        } else {
            $newName = $resource->getName();
        }

        return $newName;
    }

    public function normalizeEventName($prefix, $resourceType)
    {
        return $prefix . '_' . strtolower(str_replace(' ', '_', $resourceType));
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
}