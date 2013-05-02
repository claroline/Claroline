<?php

namespace Claroline\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\Home\Content;
use Claroline\CoreBundle\Entity\Home\Content2Type;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    /**
     * Get content by id, if the content does not exists an error is given.
     * This method require claroline.common.home_service.
     *
     * @route(
     *     "/content/{id}/{type}",
     *     requirements={"id" = "\d+"},
     *     name="claroline_get_content_by_id_and_type",
     *     defaults={"type" = "home"})
     *
     * @param \String $id The id of the content.
     * @param \String $type The type of the content, this parameter is optional, but this parameter could be usefull
     *                      because the contents can have different twigs templates and sizes by their type.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @see Claroline\CoreBundle\Library\Home\HomeService()
     */
    public function contentAction($id, $type = null)
    {
        //@todo get the types and if the $type is set use this type for twig and their size

        $manager = $this->getDoctrine()->getManager();

        $content = $manager->getRepository("ClarolineCoreBundle:Home\Content")->findOneBy(
            array('id' => $id)
        );

        if ($content) {
            if ($type) {
                $types = $manager->getRepository("ClarolineCoreBundle:Home\Type")->findOneBy(
                    array('name' => $type)
                );

                $contentType = $manager->getRepository("ClarolineCoreBundle:Home\Content2Type")->findOneBy(
                    array('content' => $content, 'type' => $types)
                );

                if ($types and $contentType) {
                    $size = $contentType->getSize();
                }
            } else {

                //default values
                $type = "default";
                $size = "span12";
            }

            $menu = $this->menuAction($id, $size, $type)->getContent();

            return $this->render(
                $this->container->get('claroline.common.home_service')->defaultTemplate(
                    "ClarolineCoreBundle:types:$type.html.twig"
                ),
                array('content' => $content, 'size' => $size, 'menu' => $menu, 'type' => $type)
            );
        } else {
            return $this->render(
                'ClarolineCoreBundle:Home:error.html.twig',
                array('path' => "Content ".$id)
            );
        }
    }

    /**
     * Render the layout of contents by type, if the type does not exists an error is given.
     *
     * @route("/", name="claro_index", defaults={"type" = "home"})
     * @route("/type/{type}", name="claro_get_content_by_type", defaults={"type" = "home"})
     *
     * @param \String $type The type of contents.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function typeAction($type)
    {
        $content = $this->getContentByType($type);

        if ($content) {
            $creator = $this->creatorAction($type)->getContent();

            return $this->render(
                'ClarolineCoreBundle:Home:layout.html.twig',
                array('content' => $content, 'creator' => $creator)
            );
        } else {
            return $this->render(
                'ClarolineCoreBundle:Home:error.html.twig',
                array('path' => $type)
            );
        }
    }

    /**
     * Create new content by POST method. This is used by ajax.
     * The response is the id of the new content in success, otherwise the response is the false word in a string.
     *
     * @route("/content/create", name="claroline_content_create")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction()
    {
        $response = "false";

        if ($this->get('security.context')->isGranted('ROLE_ADMIN') and
            isset($_POST['title']) and isset($_POST['text']) and isset($_POST['type'])) {

            $title = $_POST['title'];
            $text = $_POST['text'];
            $generated = $_POST['generated_content'];

            $manager = $this->getDoctrine()->getManager();

            $content = new Content();

            $content->setTitle($title);
            $content->setContent($text);
            $content->setGeneratedContent($generated);

            $manager->persist($content);

            $type = $manager->getRepository("ClarolineCoreBundle:Home\Type")->findOneBy(
                array('name' => $_POST['type'])
            );

            $first = $manager->getRepository("ClarolineCoreBundle:Home\Content2Type")->findOneBy(
                array('back' => null, 'type' => $type)
            );

            $contentType = new Content2Type($first);

            $contentType->setContent($content);
            $contentType->setType($type);

            $manager->persist($contentType);

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
     *
     * @param \String $id The id of the content.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction($id)
    {
        $response = "false";

        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {

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

            if (
                $request->get('title') or
                $request->get('text') or
                $request->get('generated_content') or
                ($request->get('size') and $request->get('type'))
            ) {
                $content->setModified();

                $manager->persist($content);
                $manager->flush();

                $response = "true";
            }
        }

        return new Response($response);
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function reorderAction($type, $a, $b)
    {
        $response = "false";

        $manager = $this->getDoctrine()->getManager();

        $a = $manager->getRepository("ClarolineCoreBundle:Home\Content")->find($a);
        $b = $manager->getRepository("ClarolineCoreBundle:Home\Content")->find($b);

        $type = $manager->getRepository("ClarolineCoreBundle:Home\Type")->findOneBy(array('name' => $type));

        if ($this->get('security.context')->isGranted('ROLE_ADMIN') and $a and $type) {

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

            $response = "true";
        }

        return new Response($response);
    }

    /**
     * Delete a content by POST method. This is used by ajax.
     * The response is the word true in a string in success, otherwise false.
     *
     * @route("/content/delete/{id}", name="claroline_content_delete")
     *
     * @param \String $id The id of the content.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($id)
    {
        $response = "false";

        $manager = $this->getDoctrine()->getManager();

        $content = $manager->getRepository("ClarolineCoreBundle:Home\Content")->find($id);

        if ($this->get('security.context')->isGranted('ROLE_ADMIN') and $content) {

            $contentTypes = $manager->getRepository("ClarolineCoreBundle:Home\Content2Type")->findBy(
                array('content' => $content)
            );

            foreach ($contentTypes as $contentType) {

                $contentType->detach();

                $manager->remove($contentType);
            }

            $manager->remove($content);
            $manager->flush();

            $response = "true";
        }

        return new Response($response);
    }

    /**
     * Render the HTML of the creator box.
     *
     * @param \String $type The type of the content to create.
     *
     * @route("/content/creator/{type}/{id}", name="claroline_content_creator")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function creatorAction($type, $id = null, $content = null)
    {
        if ($id and !$content) {
            $manager = $this->getDoctrine()->getManager();

            $content = $manager->getRepository("ClarolineCoreBundle:Home\Content")->find($id);
        }

        if ($this->get('security.context')->isGranted('ROLE_ADMIN') and $content) {

            return $this->render(
                'ClarolineCoreBundle:Home:creator.html.twig',
                array('content' => $content, 'type' => $type)
            );

        } else if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {

            return $this->render(
                'ClarolineCoreBundle:Home:creator.html.twig',
                array('type' => $type)
            );
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
    public function menuAction($id, $size, $type)
    {
        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {

            return $this->render(
                'ClarolineCoreBundle:Home:menu.html.twig',
                array('id' => $id, 'size' => $size, 'type' => $type)
            );
        }

        return new Response();
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

        if (isset($_POST["generated_content_url"])) {
            $url = $_POST["generated_content_url"];

            $graph = $this->container->get('claroline.common.graph_service')->get($url);

            if (isset($graph['type'])) {
                $response = $this->render(
                    $this->container->get('claroline.common.home_service')->defaultTemplate(
                        "ClarolineCoreBundle:Home/graph:".$graph['type'].".html.twig"
                    ),
                    array('content' => $graph)
                )->getContent();
            }
        }

        return new Response($response);
    }

    /**
     * Get Content by type.
     * This method return an array with the content on success or null if the type does not exist.
     *
     * @param \String $type  Name of the type.
     *
     * @return \Array
     */
    public function getContentByType($type = "home")
    {
        $manager = $this->getDoctrine()->getManager();

        $type = $manager->getRepository("ClarolineCoreBundle:Home\Type")->findOneBy(array('name' => $type));

        if ($type) {

            $first = $manager->getRepository("ClarolineCoreBundle:Home\Content2Type")->findOneBy(
                array('back' => null, 'type' => $type)
            );

            if ($first) {
                $content = "";

                for ($i = 0; $i < $type->getMaxContentPage() and $first != null; $i++) {
                    $menu = $this->menuAction(
                        $first->getContent()->getId(),
                        $first->getSize(),
                        $type->getName()
                    )->getContent();

                    $content .= $this->render(
                        $this->container->get('claroline.common.home_service')->defaultTemplate(
                            "ClarolineCoreBundle:types:".$type->getName().".html.twig"
                        ),
                        array(
                            'content' => $first->getContent(),
                            'size' => $first->getSize(),
                            'menu' => $menu,
                            'type' => $type->getName()
                        )
                    )->getContent();

                    $first = $first->getNext();
                }

                return $content;
            }

            return " "; // Not yet content
        }

        return null; // type does not exists
    }
}

