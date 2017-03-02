<?php

namespace Icap\BlogBundle\Manager;

use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\Tag;
use Icap\BlogBundle\Repository\TagRepository;

class TagManager
{
    /** @var \Icap\BlogBundle\Repository\TagRepository */
    protected $tagRepository;

    /**
     * Constructor.
     *
     * @param \Icap\BlogBundle\Repository\TagRepository $tagRepository
     */
    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * @return \Icap\BlogBundle\Repository\TagRepository
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
     * Load or Create tag following to a given string or list of names.
     *
     * @param string or array $tagNames
     *
     * @return array tags
     */
    public function loadOrCreateTags($tagNames)
    {
        $tagNames = (is_array($tagNames)) ? $tagNames : $this->parseTagString($tagNames);

        $tags = [];
        foreach ($tagNames as $name) {
            if ($name) {
                $tags[] = $this->loadOrCreateTag($name);
            }
        }

        return $tags;
    }

    /**
     * Load or Create tag following to a given name.
     *
     * @param string $name
     *
     * @return \Icap\BlogBundle\Entity\Tag
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
     * Load a tag following to its name.
     *
     * @param string $name
     *
     * @return \Icap\BlogBundle\Entity\Tag
     */
    public function loadTag($name)
    {
        return $this->getTagRepository()->findOneByName($name);
    }

    /**
     * Load a blog tags, calculating their weights.
     *
     * @param \Icap\BlogBundle\Entity\Blog $blog
     * @param int max
     *
     * @return array tags
     */
    public function loadByBlog(Blog $blog, $max = null)
    {
        $results = $this->getTagRepository()->findByBlog($blog, true, $max);
        $tags = [];

        if (0 < count($results)) {
            $maxWeight = intval($results[0]['frequency']);
            $resultsValues = array_values($results);
            $endResult = end($resultsValues);
            $minWeight = intval($endResult['frequency']);
            $diff = $maxWeight - $minWeight;

            foreach ($results as $result) {
                /** @var \Icap\BlogBundle\Entity\Tag $tag */
                $tag = $result[0];

                if ($diff > 10) {
                    $weight = round(((intval($result['frequency']) - $minWeight) / $diff) * 9 + 1);
                } else {
                    $weight = intval($result['frequency']) - $minWeight + 1;
                }

                $tagArray = [
                    'name' => $tag->getName(),
                    'slug' => $tag->getSlug(),
                    'weight' => $weight,
                    'countPosts' => intval($result['countPosts']),
                    'text' => $tag->getName(),
                    'id' => $tag->getId(),
                ];
                array_push($tags, $tagArray);
            }
        }

        return $tags;
    }

    /**
     * Create, not persist, tag given its name.
     *
     * @param string $name
     *
     * @return TagManager the generated Tag
     */
    public function createTag($name)
    {
        $tag = new Tag();
        $tag->setName($name);

        return $tag;
    }
}
