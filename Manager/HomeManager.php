<?php

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\Service;
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
    private $manager;
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
     *     "manager"        = @Inject("doctrine"),
     *     "persistence"    = @Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct($graph, $homeService, $manager, $persistence)
    {
        $this->graph = $graph;
        $this->manager = $persistence;
        $this->homeService = $homeService;
        $this->type = $manager->getRepository('ClarolineCoreBundle:Home\Type');
        $this->region = $manager->getRepository('ClarolineCoreBundle:Home\Region');
        $this->content = $manager->getRepository('ClarolineCoreBundle:Home\Content');
        $this->subContent = $manager->getRepository('ClarolineCoreBundle:Home\SubContent');
        $this->contentType = $manager->getRepository('ClarolineCoreBundle:Home\Content2Type');
        $this->contentRegion = $manager->getRepository('ClarolineCoreBundle:Home\Content2Region');
    }

    /**
     * Get Content
     *
     * @return array
     */
    public function getContent($content, $type, $father = null)
    {
        $array = array('type' => $type->getName(), 'size' => 'content-8');

        if ($father) {
            $array['father'] = $father->getId();
            $subContent = $this->subContent->findOneBy(array('child' => $content, 'father' => $father));
            $array['size'] = $subContent->getSize();
        } else {
            $contentType = $this->contentType->findOneBy(array('content' => $content, 'type' => $type));
            $array['size'] = $contentType->getSize();
        }

        $array['content'] = $content;

        return $array;
    }

    /**
     * Return the layout of contents by his type.
     *
     * @return array
     */
    public function contentLayout($type, $father = null, $region = null)
    {
        $content = $this->getContentByType($type, $father, $region);
        $array = null;

        //or is_object($this->type->findOneBy(array('name' => $type)))
        if ($content) {
            $array = array();
            $array['content'] = $content;
            $array['type'] = $type;
            $array = $this->homeService->isDefinedPush($array, 'father', $father);
            $array = $this->homeService->isDefinedPush($array, 'region', $region);
        }

        return $array;
    }

    /**
     * Get Content by type.
     * This method return a string with the content on success or null if the type does not exist.
     *
     * @return string
     */
    public function getContentByType($type, $father = null, $region = null)
    {
        $array = array();
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
                for ($i = 0; $i < $type->getMaxContentPage() and $first != null; $i++) {
                    $variables = array();
                    $variables['content'] = $first->getContent();
                    $variables['size'] = $first->getSize();
                    $variables['type'] = $type->getName();
                    $variables = $this->homeService->isDefinedPush($variables, 'father', $father, 'getId');
                    $variables = $this->homeService->isDefinedPush($variables, 'region', $region);
                    $array[] = $variables;
                    $first = $first->getNext();
                }
            } else {
                $array[] = array('content' => '', 'type' => $type->getName()); // in case of not yet content
            }
        }

        return $array;
    }

    /**
     * Get the content of the regions of the front page.
     *
     * @return array The content of regions.
     */
    public function getRegionContents()
    {
        $array = array();
        $regions = $this->region->findAll();

        foreach ($regions as $region) {
            $first = $this->contentRegion->findOneBy(array('back' => null, 'region' => $region));

            while ($first != null) {
                $contentType = $this->contentType->findOneBy(array('content' => $first->getContent()));

                if ($contentType) {
                    $type = $contentType->getType()->getName();
                } else {
                    $type = 'default';
                }

                $array[$region->getName()][] = array(
                    'content' => $first->getContent(),
                    'size' => $first->getSize(),
                    'menu' => '',
                    'type' => $type,
                    'region' => $region->getName()
                );

                $first = $first->getNext();
            }
        }

        return $array;
    }

    /**
     * Determine in what region a content is.
     */
    public function getRegion($content)
    {
        $region = $this->contentRegion->findOneBy(array('content' => $content));

        if ($region) {
            return $region->getRegion()->getName();
        }
    }

    /**
     * Get the types
     *
     * @return array An array of Type entity.
     */
    public function getTypes()
    {
        return $this->type->findAll();
    }

    /**
     * Get the open graph contents of a web page by his URL
     *
     * @return array
     */
    public function getGraph($url)
    {
        return $this->graph->get($url);
    }

    /**
     * Create a new content.
     *
     * @return The id of the new content.
     */
    public function createContent($title, $text, $generated = null, $type = null, $father = null)
    {
        if ($title or $text) {
            $content = new Content();
            $content->setTitle($title);
            $content->setContent($text);
            $content->setGeneratedContent($generated);
            $this->manager->persist($content);

            if ($father) {
                $father = $this->content->find($father);
                $first = $this->subContent->findOneBy(array('back' => null, 'father' => $father));
                $subContent = new SubContent($first);
                $subContent->setFather($father);
                $subContent->SetChild($content);
                $this->manager->persist($subContent);
            } else {
                $type = $this->type->findOneBy(array('name' => $type));
                $first = $this->contentType->findOneBy(array('back' => null, 'type' => $type));
                $contentType = new Content2Type($first);
                $contentType->setContent($content);
                $contentType->setType($type);
                $this->manager->persist($contentType);
            }

            $this->manager->flush();

            return $content->getId();
        }
    }

    /**
     * Update a content.
     *
     * @return This function doesn't return anything.
     */
    public function updateContent($content, $title, $text, $generated = null, $size = null, $type = null)
    {
        $content->setTitle($title);
        $content->setContent($text);
        $content->setGeneratedContent($generated);

        if ($size and $type) {
            $type = $this->type->findOneBy(array('name' => $type));
            $contentType = $this->contentType->findOneBy(array('content' => $content, 'type' => $type));
            $contentType->setSize($size);
            $this->manager->persist($contentType);
        }

        $content->setModified();
        $this->manager->persist($content);
        $this->manager->flush();
    }

    /**
     * Reorder Contents.
     *
     * @return This function doesn't return anything.
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

        $this->manager->persist($a);
        $this->manager->persist($b);
        $this->manager->flush();
    }

    /**
     * Delete a content and his childs.
     *
     * @return This function doesn't return anything.
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
        $this->manager->remove($content);
        $this->manager->flush();
    }

    /**
     * Create a type.
     *
     * @return This function doesn't return anything.
     */
    public function createType($name)
    {
        $type = new Type($name);
        $this->manager->persist($type);
        $this->manager->flush();

        return $type;
    }

    /**
     * Verify if a type exist.
     */
    public function typeExist($name)
    {
        $type = $this->type->findOneBy(array('name' => $name));

        if (is_object($type)) {
            return true;
        }

        return false;
    }

    /**
     * Delete a type and his childs.
     *
     * @return This function doesn't return anything.
     */
    public function deleteType($type)
    {
        $contents = $this->contentType->findBy(array('type' => $type));

        foreach ($contents as $content) {
            $this->deleteContent($content->getContent());
        }

        $this->manager->remove($type);
        $this->manager->flush();
    }

    /**
     * Delete a node entity and link together the next and back entities.
     *
     * @return string The word "true" useful in ajax.
     */
    public function deleNodeEntity($entity, $search, $function = null)
    {
        $entities = $entity->findBy($search);

        foreach ($entities as $entity) {
            $entity->detach();

            if ($function) {
                $function($entity);
            }

            $this->manager->remove($entity);
            $this->manager->flush();
        }

    }

    /**
     * Put a content in a region of home page as left, right, footer or header, this is useful for menus.
     *
     * @return string The word "true" useful in ajax.
     */
    public function contentToRegion($region, $content)
    {
        $regions = $this->contentRegion->findBy(array('content' => $content));

        if (count($regions) === 1 and $regions[0]->getRegion()->getName() === $region->getName()) {
            $this->deleteRegions($content, $regions);
        } else {
            $this->deleteRegions($content, $regions);

            $first = $this->contentRegion->findOneBy(array('back' => null, 'region' => $region));
            $contentRegion = new Content2Region($first);
            $contentRegion->setRegion($region);
            $contentRegion->setContent($content);
            $this->manager->persist($contentRegion);
            $this->manager->flush();
        }
    }

    /**
     * Delete a content from every region.
     */
    public function deleteRegions($content, $regions)
    {
        foreach ($regions as $region) {
            $region->detach();
            $this->manager->remove($region);
            $this->manager->flush();
        }
    }

    /**
     * Get the creator of contents.
     *
     * @return array
     */
    public function getCreator($type, $id = null, $content = null, $father = null)
    {
        $variables = array('type' => $type);

        if ($id and !$content) {
            $variables['content'] = $this->content->find($id);
        }

        return $this->homeService->isDefinedPush($variables, 'father', $father);
    }

    /**
     * Get the variables of the menu.
     *
     * @param string $id   The id of the content.
     * @param string $size The size (content-8) of the content.
     * @param string $type The type of the content.
     *
     * @return array
     */
    public function getMenu($id, $size, $type, $father = null, $region = null)
    {
        $variables = array('id' => $id, 'size' => $size, 'type' => $type, 'region' => $region);

        return $this->homeService->isDefinedPush($variables, 'father', $father);
    }
}
