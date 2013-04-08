<?php

namespace Claroline\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\Home\Content;
use Claroline\CoreBundle\Entity\Home\Type;
use Claroline\CoreBundle\Entity\Home\Content2Type;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    /**
     * Get content by id, if the content does not exists an error is given.
     * This method require claroline.common.home_service.
     *
     * @route("/content/{id}/{type}", requirements={"id" = "\d+"}, name="claroline_get_content_by_id_and_type", defaults={"type" = "home"})
     *
     * @param \String $id The id of the content.
     * @param \String $type The type of the content, this parameter is optional, but this parameter could be usefull because the contents can have different twigs templates and sizes by their type.
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

                $content2type = $manager->getRepository("ClarolineCoreBundle:Home\Content2Type")->findOneBy(
                    array('content' => $content, 'type' => $types)
                );

                if ($types and $content2type) {
                    $size = $content2type->getSize();
                }
            }
            else {

                //default values
                $type = "default";
                $size = "span12";
            }

            $menu = $this->menuAction($id, $size, $type)->getContent();

            return $this->render(
                $this->container->get('claroline.common.home_service')->defaultTemplate(
                    "ClarolineCoreBundle:types:$type.html.twig"
                ),
                array('content' => $content, 'size' => $size, 'menu' => $menu, 'type' => $type));
        }
        else
        {
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
        }
        else {
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

            $title =  $_POST['title'];
            $text =  $_POST['text'];

            $manager = $this->getDoctrine()->getManager();

            $content = new Content();

            $content->setTitle($title);
            $content->setContent($text);

            $manager->persist($content);

            $type = $manager->getRepository("ClarolineCoreBundle:Home\Type")->findOneBy(
                array('name' => $_POST['type'])
            );

            if ($type) {
                $first = $manager->getRepository("ClarolineCoreBundle:Home\Content2Type")->findOneBy(
                    array('back' => null, 'type' => $type)
                );

                $content2type = new Content2Type($first);

                $content2type->setContent($content);
                $content2type->setType($type);

                $manager->persist($content2type);

                $manager->flush();

                $response = $content->getId();
            }
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

        $manager = $this->getDoctrine()->getManager();

        $content = $manager->getRepository("ClarolineCoreBundle:Home\Content")->findOneBy(
            array('id' => $id)
        );

        if ($this->get('security.context')->isGranted('ROLE_ADMIN') and $content) {
            if (isset($_POST['title'])) {
                $content->setTitle($_POST['title']);
            }

            if (isset($_POST['text'])) {
                $content->setContent($_POST['text']);
            }

            if (isset($_POST['generated_content'])) {
                $content->setGeneratedContent($_POST['generated_content']);
            }

            if(isset($_POST['size']) and isset($_POST['type']))
            {
                $type =  $manager->getRepository("ClarolineCoreBundle:Home\Type")->findOneBy(
                    array('name' => $_POST['type'])
                );

                $content2type = $manager->getRepository("ClarolineCoreBundle:Home\Content2Type")->findOneBy(
                    array('content' => $content, 'type' => $type)
                );

                if ($content2type) {
                    $content2type->setSize($_POST['size']);
                    $manager->persist($content2type);
                }
            }

            if (isset($_POST['title']) or
                isset($_POST['text']) or
                isset($_POST['generated_content']) or
                (isset($_POST['size']) and isset($_POST['type']))) {

                    $content->setModified();

                $manager->persist($content);
                $manager->flush();

                $response = "true";
            }
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

            $content2types = $manager->getRepository("ClarolineCoreBundle:Home\Content2Type")->findBy(
                array('content' => $content)
            );

            foreach($content2types as $content2type)
            {
                $back = $content2type->getBack();
                $next = $content2type->getNext();

                if ($back) {
                    $back->setNext($content2type->getNext());
                }

                if ($next) {
                    $next->setBack($content2type->getBack());
                }

                $manager->remove($content2type);
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function creatorAction($type)
    {
        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return $this->render(
                'ClarolineCoreBundle:Home:creator.html.twig',
                array('type' => $type)
            );
        }
        else
        {
            return new Response("");
        }
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
        else {
            return new Response("");
        }
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

            if($first) {
                $content = "";

                for ($i = 0; $i < $type->getMaxContentPage() and $first != null; $i++) {
                    $menu = $this->menuAction(
                        $first->getContent()->getId(),
                        $first->getSize(),
                        $type->getName())->getContent();

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
            else {
                return " "; // Not yet content
            }
        }

        return null; // type does not exists
    }
}

