<?php

namespace Claroline\CoreBundle\Library\Home;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.common.home_service")
 */
class HomeService
{
    /**
     * Verify if a twig template exists, If the template does not exists a default path will be return;
     *
     * @param \String $path The path of the twig template separated by : just as the path for $this->render(...)
     * @return Return \String
     */
    public function defaultTemplate($path)
    {
        $dir = explode(":", $path);

        $controller = preg_split('/(?=[A-Z])/', $dir[0]);
        $controller = array_slice($controller, (count($controller) - 2));
        $controller = implode("", $controller);

        $base = __DIR__."/../../../".$controller."/Resources/views/";

        if ($dir[1] == "") {
            $dir[0] = $dir[0].":";
            $tmp = array_slice($dir, 2);
        } else {
            $tmp = array_slice($dir, 1);

            if (!file_exists($base.$tmp[0])) {
                $tmp[0] = "Default";
            }
        }

        if (file_exists($base.implode("/", $tmp))) {
            return $dir[0].":".implode(":", $tmp);
        } else {
            $file = explode(".", $tmp[count($tmp) - 1]);

            $file[0] = "default";

            $tmp[count($tmp) - 1] = implode(".", $file);

            if (file_exists($base.implode("/", $tmp))) {
                return $dir[0].":".implode(":", $tmp);
            }
        }

        return $path;
    }
}
