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
    private $security;
    private $content;
    private $type;
    private $region;
    private $contentType;
    private $contentRegion;
    private $subContent;

    /**
     * @InjectParams({
     *     "graph" = @Inject("claroline.common.graph_service"),
     *     "templating"  = @Inject("templating"),
     *     "manager"  = @Inject("doctrine"),
     *     "security"  = @Inject("security.context")
     * })
     */
    public function __construct($graph, $templating, $manager, $security)
    {
        $this->graph = $graph;
        $this->templating = $templating;
        $this->security = $security;
        $this->content = $manager->getRepository("ClarolineCoreBundle:Home\Content");
        $this->type = $manager->getRepository("ClarolineCoreBundle:Home\Type");
        $this->region = $manager->getRepository("ClarolineCoreBundle:Home\Region");
        $this->contentType = $manager->getRepository("ClarolineCoreBundle:Home\Content2Type");
        $this->contentRegion = $manager->getRepository("ClarolineCoreBundle:Home\Content2Region");
        $this->subContent = $manager->getRepository("ClarolineCoreBundle:Home\SubContent");
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
     *getMenu
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

    public function getContent($content, $type = null, $father = null)
    {
        $variables = array(
            "type" => $type,
            "size" => "span12"
        );

        $type = $this->type->findOneBy(array('name' => $type));

        if ($father) {

            $variables["father"] = $father;

            $father = $this->content->find($father);

            $subContent = $this->subContent->findOneBy(
                array('child' => $content, 'father' => $father)
            );

            $variables["size"] = $subContent->getSize();

        } else {

            $contentType = $this->contentType->findOneBy(
                array('content' => $content, 'type' => $type)
            );

            $variables["size"] = $contentType->getSize();
        }

        $variables["menu"] = $this->menuAction(
            $content->getId(), $variables["size"], $variables["type"], $father
        )->getContent();

        $variables["content"] = $content;

        return $variables;
    }

    /**
     * Render the HTML of the menu in a content.
     *
     * @param \String $id The id of the content.
     * @param \String $size The size (span12) of the content.
     * @param \String $type The type of the content.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getMenu($id, $size, $type, $father = null)
    {
        $variables = array('id' => $id, 'size' => $size, 'type' => $type);

        $variables = $this->isDefinedPush($variables, "father", $father, "getId");

        return $this->render('ClarolineCoreBundle:Home:menu.html.twig', $variables);
    }

    /**
     * Render the layout of contents by his type.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function contentLayout($type, $father = null, $region = null)
    {
        $content = $this->getContentByType($type, $father, $region);

        $variables = array();

        if ($content) {

            $variables['content'] = $content;
            $variables['creator'] = $this->getCreator($type, null, null, $father);

            $variables = $this->isDefinedPush($variables, "father", $father);
            $variables = $this->isDefinedPush($variables, "region", $region);

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
    public function getContentByType($type, $father = null, $region = null)
    {
        $type = $this->type->findOneBy(array('name' => $type));

        if ($type) {
            if ($father) {

                $father = $this->content->find($father);

                $first = $this->subContent->findOneBy(
                    array('back' => null, 'father' => $father)
                );

            } else {
                $first = $this->contentType->findOneBy(
                    array('back' => null, 'type' => $type)
                );
            }

            if ($first) {
                $content = " ";

                for ($i = 0; $i < $type->getMaxContentPage() and $first != null; $i++) {
                    $variables = array();

                    $variables["content"] = $first->getContent();
                    $variables["size"] = $first->getSize();
                    $variables["type"] = $type->getName();
                    $variables["menu"] = $this->getMenu(
                        $first->getContent()->getId(),
                        $first->getSize(),
                        $type->getName(),
                        $father
                    )->getContent();

                    $variables = $this->isDefinedPush($variables, "father", $father, "getId");
                    $variables = $this->isDefinedPush($variables, "region", $region);

                    $content .= $this->render(
                        "ClarolineCoreBundle:Home/types:".$type->getName().".html.twig",
                        $variables,
                        true
                    )->getContent();

                    $first = $first->getNext();
                }

                return $content;
            }

            return " "; // Not yet content
        }

        return null;
    }

    public function getRegions()
    {
        $tmp = array();

        $regions = $this->region->findAll();

        foreach ($regions as $region) {

            $content = "";

            $first = $this->contentRegion->findOneBy(array('back' => null, 'region' => $region));

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
                if (!(!$this->security->isGranted('ROLE_ADMIN') and
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

    public function getCreator($type, $id = null, $content = null, $father = null)
    {
        //cant use @Secure(roles="ROLE_ADMIN") annotation beacause this method is called in anonymous mode

        if ($this->security->isGranted('ROLE_ADMIN')) {

            $variables = array('type' => $type);

            if ($id and !$content) {

                $variables["content"] = $this->content->find($id);
            }

            $variables = $this->isDefinedPush($variables, "father", $father);

            return $this->render("ClarolineCoreBundle:Home/types:".$type.".creator.twig", $variables, true);
        }

        return new Response(); //return void and not an exeption
    }

    /**
     *  Reduce some "overall complexity"
     *
     */
    private function isDefinedPush($array, $name, $variable, $method = null)
    {
        if ($method and $variable) {
            $array[$name] = $variable->$method();
        } elseif ($variable) {
            $array[$name] = $variable;
        }

        return $array;
    }
}
