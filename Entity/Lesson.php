<?php
/**
 * Created by JetBrains PhpStorm.
 * User: gaetan
 * Date: 26/06/13
 * Time: 15:52
 * To change this template use File | Settings | File Templates.
 */
namespace ICAP\LessonBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity
* @ORM\Table(name="icap__lesson")
*/
class Lesson extends AbstractResource
{
    public function getPathArray()
    {
        $path = $this->getPath();
        $pathItems = explode("`", $path);
        $pathArray = array();
        foreach ($pathItems as $item) {
            preg_match("/-([0-9]+)$/", $item, $matches);
            if (count($matches) > 0) {
                $id = substr($matches[0], 1);
                $name = preg_replace("/-([0-9]+)$/", "", $item);
                $pathArray[] = array('id' => $id, 'name' => $name);
            }
        }

        return $pathArray;
    }
}