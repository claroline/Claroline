<?php

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\Home\Type;
use Claroline\CoreBundle\Entity\Home\Content;
use Claroline\CoreBundle\Entity\Home\SubContent;
use Claroline\CoreBundle\Entity\Home\Content2Type;
use Claroline\CoreBundle\Entity\Home\Content2Region;

/**
 * @Service("claroline.manager.home_manager")
 */
class HomeManager
{
    private $graph;
    private $writer;
    private $security;
    private $templating;
    private $homeService;

    private $type;
    private $region;
    private $content;
    private $subContent;
    private $contentType;
    private $contentRegion;

    /**
     * @InjectParams({
     *     "graph"          = @Inject("claroline.common.graph_service"),
     *     "homeService"    = @Inject("claroline.common.home_service"),
     *     "templating"     = @Inject("templating"),
     *     "manager"        = @Inject("doctrine"),
     *     "security"       = @Inject("security.context"),
     *     "writer"         = @Inject("claroline.database.writer")
     * })
     */
    public function __construct($graph, $homeService, $templating, $manager, $security, $writer)
    {
        $this->graph = $graph;
        $this->writer = $writer;
        $this->security = $security;
        $this->templating = $templating;
        $this->homeService = $homeService;

        $this->type = $manager->getRepository('ClarolineCoreBundle:Home\Type');
        $this->region = $manager->getRepository('ClarolineCoreBundle:Home\Region');
        $this->content = $manager->getRepository('ClarolineCoreBundle:Home\Content');
        $this->subContent = $manager->getRepository('ClarolineCoreBundle:Home\SubContent');
        $this->contentType = $manager->getRepository('ClarolineCoreBundle:Home\Content2Type');
        $this->contentRegion = $manager->getRepository('ClarolineCoreBundle:Home\Content2Region');
    }

    /**
     * Alias of templating render
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function render($template, $array, $default = false)
    {
        if ($default) {
            $template = $this->homeService->defaultTemplate($template);
        }

        return new Response($this->templating->render($template, $array));
    }

    /**
     * Get Content
     *
     * @return \Array
     */
    public function getContent($content, $type, $father = null)
    {
        $array = array('type' => $type->getName(), 'size' => 'span12');

        if ($father) {

            $array['father'] = $father->getId();

            $subContent = $this->subContent->findOneBy(array('child' => $content, 'father' => $father));

            $array['size'] = $subContent->getSize();

        } else {

            $contentType = $this->contentType->findOneBy(array('content' => $content, 'type' => $type));
            $array['size'] = $contentType->getSize();
        }

        $array['menu'] = $this->getMenu(
            $content->getId(), $array['size'], $array['type'], $father
        )->getContent();

        $array['content'] = $content;

        return $array;
    }

    /**
     * Render the page of the menu.
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

        $variables = $this->homeService->isDefinedPush($variables, 'father', $father, 'getId');

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
            $variables['creator'] = $this->getCreator($type, null, null, $father)->getContent();

            $variables = $this->homeService->isDefinedPush($variables, 'father', $father);
            $variables = $this->homeService->isDefinedPush($variables, 'region', $region);

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
                $content = ' ';

                for ($i = 0; $i < $type->getMaxContentPage() and $first != null; $i++) {
                    $variables = array();

                    $variables['content'] = $first->getContent();
                    $variables['size'] = $first->getSize();
                    $variables['type'] = $type->getName();
                    $variables['menu'] = $this->getMenu(
                        $first->getContent()->getId(),
                        $first->getSize(),
                        $type->getName(),
                        $father
                    )->getContent();

                    $variables = $this->homeService->isDefinedPush($variables, 'father', $father, 'getId');
                    $variables = $this->homeService->isDefinedPush($variables, 'region', $region);

                    $content .= $this->render(
                        'ClarolineCoreBundle:Home/types:'.$type->getName().'.html.twig',
                        $variables,
                        true
                    )->getContent();

                    $first = $first->getNext();
                }

                return $content;
            }

            return ' '; // Not yet content
        }

        return null;
    }

    /**
     * Get the content of the regions of the front page.
     *
     * @return \String The content of regions.
     */
    public function getRegions()
    {
        $tmp = array();

        $regions = $this->region->findAll();

        foreach ($regions as $region) {

            $content = '';

            $first = $this->contentRegion->findOneBy(array('back' => null, 'region' => $region));

            while ($first != null) {

                $contentType = $this->contentType->findOneBy(array('content' => $first->getContent()));

                if ($contentType) {
                    $type = $contentType->getType()->getName();
                } else {
                    $type = 'default';
                }

                //@TODO Need content rights for admin users
                if (!(!$this->security->isGranted('ROLE_ADMIN') and
                    $type == 'menu' and
                    $first->getContent()->getTitle() == 'Administration')
                ) {
                    $content .= $this->render(
                        'ClarolineCoreBundle:Home/types:'.$type.'.html.twig',
                        array(
                            'content' => $first->getContent(),
                            'size' => $first->getSize(),
                            'menu' => '',
                            'type' => $type,
                            'region' => $region->getName()
                        ),
                        true
                    )->getContent();
                }

                $first = $first->getNext();
            }

            if ($content != '') {
                $tmp[$region->getName()] = $content;
            }
        }

        return $tmp;
    }

    /**
     * Get the types
     *
     * @return \Array An array of Type entity.
     */
    public function getTypes()
    {
        return $this->type->findAll();
    }

    /**
     * Render the creator of contents.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCreator($type, $id = null, $content = null, $father = null)
    {
        //cant use @Secure(roles="ROLE_ADMIN") annotation beacause this method is called in anonymous mode

        if ($this->security->isGranted('ROLE_ADMIN')) {

            $variables = array('type' => $type);

            if ($id and !$content) {

                $variables['content'] = $this->content->find($id);
            }

            $variables = $this->homeService->isDefinedPush($variables, 'father', $father);

            return $this->render('ClarolineCoreBundle:Home/types:'.$type.'.creator.twig', $variables, true);
        }

        return new Response(); //return void and not an exeption
    }

    /**
     * Get the open graph contents of a web page by his URL
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getGraph($url)
    {
        $response = 'false';

        $graph = $this->graph->get($url);

        if (isset($graph['type'])) {
            $response = $this->render(
                'ClarolineCoreBundle:Home/graph:'.$graph['type'].'.html.twig',
                array('content' => $graph),
                true
            )->getContent();
        }

        return new Response($response);
    }

    /**
     * Create a new content.
     *
     * @return The id of the new content.
     */
    public function createContent($title, $text, $generated = null, $type = null, $father = null)
    {
        $response = 'false';

        if ($title or $text) {

            $content = new Content();

            $content->setTitle($title);
            $content->setContent($text);
            $content->setGeneratedContent($generated);

            $this->writer->suspendFlush();

            $this->writer->create($content);

            if ($father) {

                $father = $this->content->find($father);
                $first = $this->subContent->findOneBy(array('back' => null, 'father' => $father));

                $subContent = new SubContent($first);
                $subContent->setFather($father);
                $subContent->SetChild($content);

                $this->writer->create($subContent);

            } else {

                $type = $this->type->findOneBy(array('name' => $type));
                $first = $this->contentType->findOneBy(array('back' => null, 'type' => $type));

                $contentType = new Content2Type($first);
                $contentType->setContent($content);
                $contentType->setType($type);

                $this->writer->create($contentType);
            }

            $this->writer->forceFlush();

            $response = $content->getId();
        }

        return new Response($response);
    }

    /**
     * Update a content.
     *
     * @return \String The word "true" useful in ajax.
     */
    public function updateContent($content, $title, $text, $generated = null, $size = null, $type = null)
    {
        $content->setTitle($title);
        $content->setContent($text);
        $content->setGeneratedContent($generated);

        $this->writer->suspendFlush();

        if ($size and $type) {

            $type = $this->type->findOneBy(array('name' => $type));
            $contentType = $this->contentType->findOneBy(array('content' => $content, 'type' => $type));
            $contentType->setSize($size);

            $this->writer->update($contentType);
        }

        $content->setModified();

        $this->writer->update($content);
        $this->writer->forceFlush();

        return new Response('true');
    }

    /**
     * Reorder Contents.
     *
     * @return \String The word "true" useful in ajax.
     */
    public function reorderContent($type, $a, $b = null)
    {
        $a = $this->contentType->findOneBy(array('type' => $type, 'content' => $a));
        $a->detach();

        if ($b) {

            $b = $this->contentType->findOneBy(array('type' => $type, 'content' => $b));

            $a->setBack($b->getBack());
            $a->setNext($b);

            if ($b->getBack()) {
                $b->getBack()->setNext($a);
            }

            $b->setBack($a);

        } else {

            $b = $this->contentType->findOneBy(array('type' => $type, 'next' => null));

            $a->setNext($b->getNext());
            $a->setBack($b);

            $b->setNext($a);
        }

        //$this->writer->suspendFlush();
        $this->writer->update($a);
        $this->writer->update($b);
        //$this->writer->forceFlush();

        return new Response('true');
    }

    /**
     * Delete a content and his childs.
     *
     * @return \String The word "true" useful in ajax.
     */
    public function deleteContent($content)
    {
        $this->deleNodeEntity($this->contentType, array('content' => $content));

        $this->deleNodeEntity(
            $this->subContent, array('father' => $content),
            function ($entity) {
                $this->deleteContent($entity->getChild());
            }
        );

        $this->deleNodeEntity($this->subContent, array('child' => $content));
        $this->deleNodeEntity($this->contentRegion, array('content' => $content));

        $this->writer->delete($content);

        return new Response('true');
    }

    /**
     * Delete a node entity and link together the next and back entities.
     *
     * @return \String The word "true" useful in ajax.
     */
    public function deleNodeEntity($entity, $search, $function = null)
    {
        $entities = $entity->findBy($search);

        foreach ($entities as $entity) {
            $entity->detach();

            if ($function) {
                $function($entity);
            }

            $this->writer->delete($entity);
        }
    }

    /**
     * Put a content in a region of home page as left, right, footer or header, this is useful for menus.
     *
     * @return \String The word "true" useful in ajax.
     */
    public function contentToRegion($region, $content)
    {
        $first = $this->contentRegion->findOneBy(array('back' => null, 'region' => $region));

        $contentRegion = new Content2Region($first);
        $contentRegion->setRegion($region);
        $contentRegion->setContent($content);

        $this->writer->create($contentRegion);

        return new Response('true');
    }
}
