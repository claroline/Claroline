<?php

namespace ICAP\BlogBundle\Manager;

use Doctrine\ORM\EntityManager;
use ICAP\BlogBundle\Entity\Blog;
use ICAP\BlogBundle\Entity\Tag;
use ICAP\BlogBundle\Repository\TagRepository;

class TagManager
{
    /** @var \ICAP\BlogBundle\Repository\TagRepository */
    protected $tagRepository;

    /**
     * Constructor
     *
     * @param \ICAP\BlogBundle\Repository\TagRepository $tagRepository
     */
    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * @return \ICAP\BlogBundle\Repository\TagRepository
     */
    public function getTagRepository()
    {
        return $this->tagRepository;
    }

    /**
     * @param string $tagString
     *
     * @return array
     */
    public function parseTagString($tagString)
    {
        // 1. Split the string with commas
        // 2. Remove whitespaces around the tags
        // 3. Remove empty elements (like in "tag1,tag2, ,,tag3,tag4")
        return array_filter(array_map('trim', explode(',', $tagString)));
    }

    /**
     * Load or Create tag following to a given string or list of names
     *
     * @param string or array $tagNames
     * @return array tags
     */
    public function loadOrCreateTags($tagNames)
    {
        $tagNames = (is_array($tagNames)) ? $tagNames : $this->parseTagString($tagNames);

        $tags = array();
        foreach ($tagNames as $name) {
            if ($name) {
                $tags[] = $this->loadOrCreateTag($name);
            }
        }

        return $tags;
    }

    /**
     * Load or Create tag following to a given name
     *
     * @param string $name
     * @return \ICAP\BlogBundle\Entity\Tag
     */
    public function loadOrCreateTag($name)
    {
        $tag = $this->loadTag($name);
        if (null === $tag) {
            $tag = $this->createTag($name);
        }

        return $tag;
    }

    /**
     * Load a tag following to its name
     *
     * @param string $name
     * @return \ICAP\BlogBundle\Entity\Tag
     */
    public function loadTag($name)
    {
        return $this->getTagRepository()->findOneByName($name);
    }

    /**
     * Create, not persist, tag given its name
     *
     * @param String $name
     * @return TagManager the generated Tag
     */
    public function createTag($name)
    {
        $tag = new Tag();
        $tag->setName($name);

        return $tag;
    }
}
