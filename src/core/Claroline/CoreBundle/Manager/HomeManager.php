<?php

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\HttpFoundation\Response;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Service("claroline.manager.home_manager")
 */
class HomeManager
{
    private $graph;
    private $templating;
    private $contentType;

    /**
     * @InjectParams({
     *     "graph" = @Inject("claroline.common.graph_service"),
     *     "templating"  = @Inject("templating"),
     *     "manager"  = @Inject("doctrine")
     * })
     */
    public function __construct($graph, $templating, $manager)
    {
        $this->graph = $graph;
        $this->templating = $templating;
        $this->contentType = $manager->getRepository("ClarolineCoreBundle:Home\Content2Type");
    }

    /**
     * Alias
     */
    public function render($template, $array, $default = false)
    {
        if ($default) {
            $template = $this->defaultTemplate($template);
        }

        return new Response($this->templating->render($template, $array));
    }

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

        $base = __DIR__."/../../".$controller."/Resources/views/";

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

    public function getContent($content, $type = null, $subContent = null)
    {
        $array = array(
            "type" => $type,
            "size" => "span12",
            "content" => $content
        );

        if ($subContent) {
            $array["father"] = $subContent->getfather()->getId();
            $array["size"] = $subContent->getSize();

        } else if ($type) {

            $array["size"] = $type->getSize();
        }

        $array["menu"] = $this->getMenu($content, $type, $subContent)->getContent();

        return $array;
    }

    /**
     * Render the HTML of the menu in a content.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getMenu($content, $type, $subContent)
    {
        return $this->render(
            "ClarolineCoreBundle:Home:menu.html.twig",
            array("content" => $content, "type" => $type, "subContent" => $subContent)
        );
    }

    /**
     * Render the layout of contents by his type.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function contentLayout($type, $subContent = null, $region = null)
    {
        $content = $this->getContentByType($type, $subContent, $region);

        $variables = array();

        if ($content) {

            $variables['content'] = $content;
            $variables['creator'] = $this->getCreator($type, null, null, $subContent);
            $variables['subcontent'] = $subContent;
            $variables['region'] = $region;

            return $this->render('ClarolineCoreBundle:Home:layout.html.twig', $variables);
        }

        return $this->render('ClarolineCoreBundle:Home:error.html.twig', array('path' => $type));
    }

    /**
     * Get Content by type.
     * This method return an array with the content on success or null if the type does not exist.
     *
     * @return \Array
     */
    public function getContentByType($type, $subContent = null, $region = null)
    {
        if ($type) {
            if ($subContent) {
                $first = $subContent;
            } else {
                $first = $type;
            }

            if ($first) {

                $content = " ";

                for ($i = 0; $i < $type->getMaxContentPage() and $first != null; $i++) {
                    $variables = array();

                    $variables["content"] = $first->getContent();
                    $variables["size"] = $first->getSize();
                    $variables["type"] = $type->getName();
                    $variables["menu"] = $this->getMenu(
                        $first->getContent(),
                        $first->getSize(),
                        $type->getName(),
                        $father
                    )->getContent();

                    $variables = $this->isDefinedPush($variables, "father", $father, "getId");
                    $variables = $this->isDefinedPush($variables, "region", $region);

                    $content .= $this->render(
                        $this->container->get('claroline.common.home_service')->defaultTemplate(
                            "ClarolineCoreBundle:Home/types:".$type->getName().".html.twig"
                        ),
                        $variables
                    )->getContent();

                    $first = $first->getNext();
                }

                return $content;
            }

            return " "; // Not yet content
        }

        return null;
    }

    public function getRegions($regions)
    {
        $tmp = array();

        echo count($regions);

        foreach ($regions as $first) {

            var_dump($first->getRegion());

            $content = "";

            while ($first != null) {

                $contentType = $this->contentType->findOneBy(
                    array('content' => $first->getContent())
                );

                if ($contentType) {
                    $type = $contentType->getType()->getName();
                } else {
                    $type = "default";
                }

                //@TODO Need content rights for admin users
                if (!(!$this->get('security.context')->isGranted('ROLE_ADMIN') and
                    $type == "menu" and
                    $first->getContent()->getTitle() == 'Administration')
                ) {
                    $content .= $this->render(
                        "ClarolineCoreBundle:Home/types:".$type.".html.twig",
                        array(
                            'content' => $first->getContent(),
                            'size' => $first->getSize(),
                            'menu' => "",
                            'type' => $type,
                            'region' => $region->getName()
                        ),
                        true
                    )->getContent();
                }

                $first = $first->getNext();
            }

            if ($content != "") {
                $tmp[$region->getName()] = $content;
            }
        }

        return $tmp;
    }
}
