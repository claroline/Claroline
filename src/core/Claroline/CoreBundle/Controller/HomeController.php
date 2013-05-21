<?php

namespace Claroline\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\Home\Content;
use Claroline\CoreBundle\Entity\Home\SubContent;
use Claroline\CoreBundle\Entity\Home\Type;
use Claroline\CoreBundle\Entity\Home\Content2Type;
use Claroline\CoreBundle\Entity\Home\Content2Region;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\Secure;

class HomeController extends Controller
{
    /**
     * Get content by id, if the content does not exists an error is given.
     * This method require claroline.common.home_service.
     *
     * @route(
     *     "/content/{id}/{type}/{father}",
     *     requirements={"id" = "\d+"},
     *     name="claroline_get_content_by_id_and_type",
     *     defaults={"type" = "home", "father" = null})
     *
     * @param \String $id The id of the content.
     * @param \String $type The type of the content, this parameter is optional, but this parameter could be usefull
     *                      because the contents can have different twigs templates and sizes by their type.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @see Claroline\CoreBundle\Library\Home\HomeService()
     */
    public function contentAction($id, $type = "default", $father = null)
    {
        $variables = array(
            "type" => $type,
            "size" => "span12"
        );

        $manager = $this->getDoctrine()->getManager();

        $content = $manager->getRepository("ClarolineCoreBundle:Home\Content")->find($id);
        $type = $manager->getRepository("ClarolineCoreBundle:Home\Type")->findOneBy(array('name' => $type));

        if ($content) {

            if ($father) {

                $variables["father"] = $father;

                $father = $manager->getRepository("ClarolineCoreBundle:Home\Content")->find($father);

                $subContent = $manager->getRepository("ClarolineCoreBundle:Home\SubContent")->findOneBy(
                    array('child' => $content, 'father' => $father)
                );

                $variables["size"] = $subContent->getSize();

            } else {

                $contentType = $manager->getRepository("ClarolineCoreBundle:Home\Content2Type")->findOneBy(
                    array('content' => $content, 'type' => $type)
                );

                $variables["size"] = $contentType->getSize();
            }

            $variables["menu"] = $this->menuAction($id, $variables["size"], $variables["type"], $father)->getContent();
            $variables["content"] = $content;

            return $this->render(
                $this->container->get('claroline.common.home_service')->defaultTemplate(
                    "ClarolineCoreBundle:Home/types:".$variables["type"].".html.twig"
                ),
                $variables
            );
        }

        return $this->render('ClarolineCoreBundle:Home\:error.html.twig', array('path' => "Content ".$id));
    }

    /**
     * Render the layout of contents by type, if the type does not exists an error is given.
     *
     * @param \String $type The type of contents.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function typeAction($type, $father = null, $region = null)
    {
        $content = $this->getContentByType($type, $father, $region);

        $variables = array();

        if ($content) {

            $variables["content"] = $content;
            $variables["creator"] = $this->creatorAction($type, null, null, $father)->getContent();

            $variables = $this->isDefinedPush($variables, "father", $father);
            $variables = $this->isDefinedPush($variables, "region", $region);

            return $this->render("ClarolineCoreBundle:Home:layout.html.twig", $variables);
        }

        return $this->render('ClarolineCoreBundle:Home:error.html.twig', array('path' => $type));
    }

    /**
     *
     * @route("/types", name="claroline_types_manager")
     * @Secure(roles="ROLE_ADMIN")
     *
     */
    public function typesAction()
    {
        $manager = $this->getDoctrine()->getManager();

        $types = $manager->getRepository("ClarolineCoreBundle:Home\Type")->findAll();

        $variables = array(
            "region" => $this->getRegions(),
            "content" => $this->render(
                "ClarolineCoreBundle:Home:types.html.twig",
                array('types' => $types)
            )->getContent()
        );

        return $this->render("ClarolineCoreBundle:Home:home.html.twig", $variables);
    }

    /**
     * @route("/type/{type}", name="claro_get_content_by_type")
     * @route("/", name="claro_index")
     */
    public function homeAction($type = "home", $father = null)
    {
        $variables = array(
            "region" => $this->getRegions(),
            "content" => $this->typeAction($type, $father)->getContent()
        );

        return $this->render("ClarolineCoreBundle:Home:home.html.twig", $variables);
    }

    public function getRegions()
    {
        $tmp = array();

        $manager = $this->getDoctrine()->getManager();

        $regions = $manager->getRepository("ClarolineCoreBundle:Home\Region")->findAll();

        foreach ($regions as $region) {

            $content = "";

            $first = $manager->getRepository("ClarolineCoreBundle:Home\Content2Region")->findOneBy(
                array('back' => null, 'region' => $region)
            );

            for ($i = 0; $first != null; $i++) {

                $contentType = $manager->getRepository("ClarolineCoreBundle:Home\Content2Type")->findOneBy(
                    array('content' => $first->getContent())
                );

                if ($contentType) {
                    $type = $contentType->getType()->getName();
                } else {
                    $type = "default";
                }

                //@TODO Need content rights for admin user
                if (!(!$this->get('security.context')->isGranted('ROLE_ADMIN') and
                    $type == "menu" and
                    $first->getContent()->getTitle() == 'Administration')
                ) {
                    $content .= $this->render(
                        $this->container->get('claroline.common.home_service')->defaultTemplate(
                            "ClarolineCoreBundle:Home/types:".$type.".html.twig"
                        ),
                        array(
                            'content' => $first->getContent(),
                            'size' => $first->getSize(),
                            'menu' => "",
                            'type' => $type,
                            'region' => $region->getName()
                        )
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

    /**
     * Create new content by POST method. This is used by ajax.
     * The response is the id of the new content in success, otherwise the response is the false word in a string.
     *
     * @route("/content/create", name="claroline_content_create")
     * @Secure(roles="ROLE_ADMIN")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction()
    {
        $request = $this->get('request');
        $response = "false";

        if ($request->get('title') or $request->get('text')) {

            $manager = $this->getDoctrine()->getManager();

            $content = new Content();

            $content->setTitle($request->get('title'));
            $content->setContent($request->get('text'));
            $content->setGeneratedContent($request->get('generated'));

            $manager->persist($content);

            if ($request->get('father')) {
                $father = $manager->getRepository("ClarolineCoreBundle:Home\Content")->find($request->get('father'));

                $first = $manager->getRepository("ClarolineCoreBundle:Home\SubContent")->findOneBy(
                    array('back' => null, 'father' => $father)
                );

                $subContent = new SubContent($first);
                $subContent->setFather($father);
                $subContent->SetChild($content);

                $manager->persist($subContent);

            } else {

                $type = $manager->getRepository("ClarolineCoreBundle:Home\Type")->findOneBy(
                    array('name' => $request->get('type'))
                );

                $first = $manager->getRepository("ClarolineCoreBundle:Home\Content2Type")->findOneBy(
                    array('back' => null, 'type' => $type)
                );

                $contentType = new Content2Type($first);

                $contentType->setContent($content);
                $contentType->setType($type);

                $manager->persist($contentType);
            }

            $manager->flush();

            $response = $content->getId();
        }

        return new Response($response);
    }

    /**
     * Update a content by POST method. This is used by ajax.
     * The response is the word true in a string in success, otherwise false.
     *
     * @route("/content/update/{id}", name="claroline_content_update")
     * @Secure(roles="ROLE_ADMIN")
     *
     * @param \String $id The id of the content.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction($id)
    {
        $manager = $this->getDoctrine()->getManager();

        $content = $manager->getRepository("ClarolineCoreBundle:Home\Content")->findOneBy(array('id' => $id));

        $request = $this->get('request');

        $content->setTitle($request->get('title'));
        $content->setContent($request->get('text'));
        $content->setGeneratedContent($request->get('generated_content'));

        if ($request->get('size') and $request->get('type')) {
            $type = $manager->getRepository("ClarolineCoreBundle:Home\Type")->findOneBy(
                array('name' => $request->get('type'))
            );

            $contentType = $manager->getRepository("ClarolineCoreBundle:Home\Content2Type")->findOneBy(
                array('content' => $content, 'type' => $type)
            );

            $contentType->setSize($request->get('size'));
            $manager->persist($contentType);
        }

        $content->setModified();

        $manager->persist($content);
        $manager->flush();

        return new Response("true");
    }

    /**
     * Reorder contents in types. This method is used by ajax.
     * The response is the word true in a string in success, otherwise false.
     *
     * @param \String $type The type of the content.
     * @param \String $a The id of the content 1.
     * @param \String $b The id of the content 2.
     *
     * @route(
     *     "/content/reorder/{type}/{a}/{b}",
     *     requirements={"a" = "\d+"},
     *     name="claroline_content_reorder")
     *
     * @Secure(roles="ROLE_ADMIN")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function reorderAction($type, $a, $b)
    {
        $manager = $this->getDoctrine()->getManager();

        $a = $manager->getRepository("ClarolineCoreBundle:Home\Content")->find($a);
        $b = $manager->getRepository("ClarolineCoreBundle:Home\Content")->find($b);

        $type = $manager->getRepository("ClarolineCoreBundle:Home\Type")->findOneBy(array('name' => $type));

        $a = $manager->getRepository("ClarolineCoreBundle:Home\Content2Type")->findOneBy(
            array(
                'type' => $type,
                'content' => $a
            )
        );

        $a->detach();

        if ($b) {
            $b = $manager->getRepository("ClarolineCoreBundle:Home\Content2Type")->findOneBy(
                array(
                    'type' => $type,
                    'content' => $b
                )
            );

            $a->setBack($b->getBack());
            $a->setNext($b);

            if ($b->getBack()) {
                $b->getBack()->setNext($a);
            }

            $b->setBack($a);

        } else {
            $b = $manager->getRepository("ClarolineCoreBundle:Home\Content2Type")->findOneBy(
                array(
                    'type' => $type,
                    'next' => null
                )
            );

            $a->setNext($b->getNext());
            $a->setBack($b);

            $b->setNext($a);
        }

        $manager->persist($a);
        $manager->persist($b);

        $manager->flush();

        return new Response("true");
    }

    /**
     * Delete a content by POST method. This is used by ajax.
     * The response is the word true in a string in success, otherwise false.
     *
     * @route("/content/delete/{id}", name="claroline_content_delete")
     * @Secure(roles="ROLE_ADMIN")
     *
     * @param \String $id The id of the content.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($id)
    {
        $manager = $this->getDoctrine()->getManager();

        $content = $manager->getRepository("ClarolineCoreBundle:Home\Content")->find($id);

        $this->deleNodeEntity("ClarolineCoreBundle:Home\Content2Type", array('content' => $content));

        $this->deleNodeEntity(
            "ClarolineCoreBundle:Home\SubContent", array('father' => $content),
            function ($entity) {
                $this->deleteAction($entity->getChild()->getId());
            }
        );

        $this->deleNodeEntity("ClarolineCoreBundle:Home\SubContent", array('child' => $content));
        $this->deleNodeEntity("ClarolineCoreBundle:Home\Content2Region", array('content' => $content));

        $manager->remove($content);
        $manager->flush();

        return new Response("true");
    }

    /**
     * @route(
     *     "/region/{region}/{id}",
     *     requirements={"id" = "\d+"},
     *     name="claroline_content_to_region"
     * )
     *
     * @Secure(roles="ROLE_ADMIN")
     *
     */
    public function contentToRegionAction($region, $id)
    {
        $manager = $this->getDoctrine()->getManager();

        $content = $manager->getRepository("ClarolineCoreBundle:Home\Content")->find($id);
        $region = $manager->getRepository("ClarolineCoreBundle:Home\Region")->findOneBy(
            array("name" => $region)
        );

        $first = $manager->getRepository("ClarolineCoreBundle:Home\Content2Region")->findOneBy(
            array("back" => null, "region" => $region)
        );

        $contentRegion = new Content2Region($first);
        $contentRegion->setRegion($region);
        $contentRegion->setContent($content);

        $manager->persist($contentRegion);
        $manager->flush();

        return new Response("true");
    }

    /**
     * Render the HTML of the creator box.
     *
     * @param \String $type The type of the content to create.
     *
     * @route(
     *     "/content/creator/{type}/{id}/{father}",
     *     name="claroline_content_creator",
     *     defaults={"father" = null}
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function creatorAction($type, $id = null, $content = null, $father = null)
    {
        //cant use @Secure(roles="ROLE_ADMIN") annotation beacause this method is called in anonymous mode

        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {

            $path = $this->container->get('claroline.common.home_service')->defaultTemplate(
                "ClarolineCoreBundle:Home/types:".$type.".creator.twig"
            );

            $variables = array('type' => $type);

            if ($id and !$content) {
                $manager = $this->getDoctrine()->getManager();

                $variables["content"] = $manager->getRepository("ClarolineCoreBundle:Home\Content")->find($id);
            }

            $variables = $this->isDefinedPush($variables, "father", $father);

            return $this->render($path, $variables);
        }

        return new Response();
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
    public function menuAction($id, $size, $type, $father = null)
    {
        $variables = array('id' => $id, 'size' => $size, 'type' => $type);

        $variables = $this->isDefinedPush($variables, "father", $father, "getId");

        return $this->render('ClarolineCoreBundle:Home:menu.html.twig', $variables);
    }

    /**
     * Render the HTML of the menu of sizes of the contents.
     *
     * @param \String $id The id of the content.
     * @param \String $size The size (span12) of the content.
     * @param \String $type The type of the content.
     *
     * @route("/content/size/{id}/{size}/{type}", name="claroline_content_size")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sizeAction($id, $size, $type)
    {
        return $this->render(
            'ClarolineCoreBundle:Home:sizes.html.twig',
            array('id' => $id, 'size' => $size, 'type' => $type)
        );
    }

    /**
     * Render the HTML of a content generated by an external url with Open Grap meta tags
     *
     * @route("/content/graph", name="claroline_content_graph")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function graphAction()
    {
        $response = "false";
        $request = $this->get('request');

        $url = $request->get("generated_content_url");

        $graph = $this->container->get('claroline.common.graph_service')->get($url);

        if (isset($graph['type'])) {
            $response = $this->render(
                $this->container->get('claroline.common.home_service')->defaultTemplate(
                    "ClarolineCoreBundle:Home/graph:".$graph['type'].".html.twig"
                ),
                array('content' => $graph)
            )->getContent();
        }

        return new Response($response);
    }

    /**
     * Render the HTML of the regions.
     *
     * @route(
     *     "/content/region/{id}",
     *     name="claroline_region"
     * )
     *
     * @param \String $id The id of the content.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function regionAction($id)
    {
        return $this->render('ClarolineCoreBundle:Home:regions.html.twig', array('id' => $id));
    }

    /**
     * Get Content by type.
     * This method return an array with the content on success or null if the type does not exist.
     *
     * @param \String $type  Name of the type.
     *
     * @return \Array
     */
    public function getContentByType($type = "home", $father = null, $region)
    {
        $manager = $this->getDoctrine()->getManager();

        $type = $manager->getRepository("ClarolineCoreBundle:Home\Type")->findOneBy(array('name' => $type));

        if ($type) {
            if ($father) {

                $father = $manager->getRepository("ClarolineCoreBundle:Home\Content")->find($father);

                $first = $manager->getRepository("ClarolineCoreBundle:Home\SubContent")->findOneBy(
                    array('back' => null, 'father' => $father)
                );

            } else {
                $first = $manager->getRepository("ClarolineCoreBundle:Home\Content2Type")->findOneBy(
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
                    $variables["menu"] = $this->menuAction(
                        $first->getContent()->getId(),
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

    /**
     *  Reduce "overall complexity"
     *
     *  @Secure(roles="ROLE_ADMIN")
     *
     */
    private function deleNodeEntity($name, $search, $function = null)
    {
        $manager = $this->getDoctrine()->getManager();

        $entities = $manager->getRepository($name)->findBy($search);

        foreach ($entities as $entity) {
            $entity->detach();

            if ($function) {
                $function($entity);
            }

            $manager->remove($entity);
        }
    }

    /**
     *  Reduce "overall complexity"
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

