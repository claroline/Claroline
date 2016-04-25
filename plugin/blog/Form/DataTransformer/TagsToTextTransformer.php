<?php

namespace Icap\BlogBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Icap\BlogBundle\Manager\TagManager;

class TagsToTextTransformer implements DataTransformerInterface
{
    /**
     * @var TagManager
     */
    private $tagManager;

    /**
     * @param TagManager $manager
     */
    public function __construct(TagManager $manager)
    {
        $this->tagManager = $manager;
    }

    /**
     * Transforms objects (tags) to a string.
     *
     * @param Tags|null $tags
     *
     * @return string
     */
    public function transform($tags)
    {
        if (!$tags) {
            $tags = array();
        }

        $tagNames = array();
        foreach ($tags as $tag) {
            array_push($tagNames, $tag->getName());
        }

        return implode(', ', $tagNames);
    }

    /**
     * Transforms a string to an array of tags.
     *
     * @param string $tagNames
     *
     * @return array of strings (names for tags)
     */
    public function reverseTransform($tagNames)
    {
        if (!$tagNames) {
            $tagNames = '';
        }

        $tagNamesArray = $this->tagManager->parseTagString($tagNames);

        return $this->tagManager->loadOrCreateTags($tagNamesArray);
    }
}
