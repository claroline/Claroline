<?php

namespace Claroline\RssBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\RssBundle\Entity\Resource\RssFeed;
use Claroline\RssBundle\Library\ReaderProvider;

class RssSerializer
{
    use SerializerTrait;

    /** @var ReaderProvider */
    private $rssReader;

    /**
     * @param ReaderProvider $rssReader
     */
    public function __construct(ReaderProvider $rssReader)
    {
        $this->rssReader = $rssReader;
    }

    public function serialize(RssFeed $rss)
    {
        return [
            'id' => $rss->getUuid(),
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
        try {
            $data = file_get_contents($url);
            $content = strstr($data, '<?xml');

            if (!$content && 0 === strpos($data, '<rss')) {
                $content = $data;
            }
            $items = $this->rssReader->getReaderFor($content)->getFeedItems(10);
        } catch (\Exception $e) {
            $items = [];
        }

        return $items;
    }
}
