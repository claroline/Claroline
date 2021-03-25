<?php

namespace Icap\BlogBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Icap\BlogBundle\Entity\Blog;

class BlogSerializer
{
    use SerializerTrait;

    private $blogOptionsSerializer;

    /**
     * BlogSerializer constructor.
     */
    public function __construct(BlogOptionsSerializer $blogOptionsSerializer)
    {
        $this->blogOptionsSerializer = $blogOptionsSerializer;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return Blog::class;
    }

    public function getName()
    {
        return 'blog';
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/blog/blog.json';
    }

    /**
     * @return array - The serialized representation of a blog
     */
    public function serialize(Blog $blog, array $options = [])
    {
        return [
            'id' => $blog->getUuid(),
            'name' => $blog->getResourceNode()->getName(),
            'infos' => $blog->getInfos(),
            'options' => $this->blogOptionsSerializer->serialize($blog, $blog->getOptions()),
        ];
    }

    /**
     * @param array       $data
     * @param Blog | null $blog
     *
     * @return Blog - The deserialized blog entity
     */
    public function deserialize($data, Blog $blog = null, array $options = [])
    {
        if (empty($blog)) {
            $blog = new Blog();
        }

        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $blog);
        }

        $this->sipe('name', 'setName', $data, $blog);
        $this->sipe('infos', 'setInfos', $data, $blog);

        if (isset($data['options'])) {
            $blog->setOptions($this->blogOptionsSerializer->deserialize($data['options'], $blog->getOptions(), $options));
        }

        return $blog;
    }
}
