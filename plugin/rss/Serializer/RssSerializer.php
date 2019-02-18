<?php

namespace Claroline\RssBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\RssBundle\Entity\Resource\RssFeed;
use Claroline\RssReaderBundle\Library\ReaderProvider;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.rss")
 * @DI\Tag("claroline.serializer")
 */
class RssSerializer
{
    use SerializerTrait;

    /**
     * @DI\InjectParams({
     *      "rssReader" = @DI\Inject("claroline.rss_reader.provider")
     * })
     */
    public function __construct(
        ReaderProvider $rssReader
    ) {
        $this->rssReader = $rssReader;
    }

    public function serialize(RssFeed $rss)
    {
        return [
            'id' => $rss->getId(),
            'url' => $rss->getUrl(),
            'items' => $this->getItems($rss->getUrl()),
        ];
    }

    public function getClass()
    {
        return RssFeed::class;
    }

    public function deserialize($data, RssFeed $rss)
    {
        $this->sipe('url', 'setUrl', $data, $rss);

        return $rss;
    }

    private function getItems($url)
    {
        // TODO : handle feed format exception...
        $data = file_get_contents($url);

        $content = strstr($data, '<?xml');
        if (!$content && 0 === strpos($data, '<rss')) {
            $content = $data;
        }

        try {
            $items = $this->rssReader
              ->getReaderFor($content)
              ->getFeedItems(10);
        } catch (\Exception $e) {
            $items = [];
        }

        return $items;
    }
}
