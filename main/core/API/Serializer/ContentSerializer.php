<?php

namespace Claroline\CoreBundle\API\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Content;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.content")
 * @DI\Tag("claroline.serializer")
 */
class ContentSerializer
{
    use SerializerTrait;

    public function getClass()
    {
        return Content::class;
    }

    /**
     * Serializes a Content entity.
     *
     * @param Content $content
     * @param array   $options
     *
     * @return array
     */
    public function serialize(Content $content, array $options = [])
    {
        return [
            'id' => $content->getId(),
            'content' => $content->getContent(),
            'title' => $content->getTitle(),
            'type' => $content->getType(),
            'locale' => $content->getTranslatableLocale(),
        ];
    }
}
