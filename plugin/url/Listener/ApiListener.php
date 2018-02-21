<?php

namespace HeVinci\UrlBundle\Listener;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\Resource\DecorateResourceNodeEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /** @var ObjectManager */
    private $om;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager"),
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * @DI\Observe("serialize_resource_node")
     */
    public function onSerialize(DecorateResourceNodeEvent $event)
    {
        // Restrict listener to Url resources only
        $resourceNode = $event->getResourceNode();
        if ('hevinci_url' === $resourceNode->getResourceType()->getName()) {
            $isYoutube = false;
            $embedYoutubeUrl = null;

            $resource = $this->om->getRepository('HeVinciUrlBundle:Url')->findOneByResourceNode($resourceNode);

            // Is it a youtube video ?
            $youtubeId = $this->getYoutubeId($resource->getUrl());
            if (false !== $youtubeId) {
                $isYoutube = true;

                // Only add remote youTube thumbnail if no local resource node thumbnail is defined
                if (null === $resourceNode->getThumbnail()) {
                    $embedYoutubeUrl = 'https://www.youtube.com/embed/'.$youtubeId;
                    $thumbnailUrl = 'http://img.youtube.com/vi/'.$youtubeId.'/hqdefault.jpg';
                    $event->add('poster', $thumbnailUrl);
                }
            }

            $event->add('url', [
                'isYoutube' => $isYoutube,
                'embedYoutubeUrl' => $embedYoutubeUrl,
                'isExternal' => $this->isExternal($resource->getUrl()),
            ]);
        }
    }

    private function getYoutubeId($url)
    {
        $return = false;

        $parsedUrl = parse_url($url);

        if (array_key_exists('host', $parsedUrl)) {
            switch ($parsedUrl['host']) {
                case 'www.youtube.com':
                    parse_str($parsedUrl['query'], $parsedQuery);
                    $return = $parsedQuery['v'];
                    break;
                case 'youtu.be':
                    $return = substr($parsedUrl['path'], 1);
                    break;
                default:
                    break;
            }
        }

        return $return;
    }

    private function isExternal($url)
    {
        $components = parse_url($url);

        return !empty($components['host']) && strcasecmp($components['host'], $_SERVER['HTTP_HOST']);
    }
}
