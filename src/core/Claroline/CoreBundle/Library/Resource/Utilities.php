<?php

namespace Claroline\CoreBundle\Library\Resource;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.resource.utilities")
 */
class Utilities
{
    /**
     * Gets a unique name for a resource in a folder.
     * If the name of the resource already exists here, ~*indice* will be happened
     * to its name
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $parent
     *
     * @return string
     */
    public function getUniqueName(AbstractResource $resource, AbstractResource $parent)
    {
        $children = $parent->getChildren();
        $name = $resource->getName();
        $arName = explode('~', pathinfo($name, PATHINFO_FILENAME));
        $baseName = $arName[0];
        $nbName = 0;

        if (null != $children) {
            $nbName = 0;
            foreach ($children as $child) {
                $arChildName = explode('~', pathinfo($child->getName(), PATHINFO_FILENAME));
                if ($baseName == $arChildName[0]) {
                    $nbName++;
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