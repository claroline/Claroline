<?php

namespace Claroline\RssBundle\Entity\Resource;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * RSS feed.
 *
 * @ORM\Table(name="claro_rss_feed")
 * @ORM\Entity()
 */
class RssFeed extends AbstractResource
{
    /**
     * @ORM\Column()
     *
     * @var string
     */
    private $url;

    /**
     * Get rss feed url.
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Set rss feed url.
     */
    public function setUrl(string $url)
    {
        $this->url = $url;
    }
}
