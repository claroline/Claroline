<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 4/21/15
 */

namespace Icap\BlogBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\NotifiableInterface;
use Icap\BlogBundle\Entity\Post;

class LogPostPublishEvent extends AbstractLogResourceEvent implements NotifiableInterface
{
    const ACTION = 'resource-icap_blog-post_publish';
    protected $blog;
    protected $post;
    protected $details;

    public function __construct(Post $post)
    {
        $this->blog = $post->getBlog();
        $this->post = $post;

        $this->details = array(
            'post' => array(
                'blog' => $this->blog->getId(),
                'title' => $post->getTitle(),
                'slug' => $post->getSlug(),
                'published' => $post->isPublished(),
                'author' => $post->getAuthor()->getFirstName().' '.$post->getAuthor()->getLastName(),
                'authorId' => $post->getAuthor()->getId(),
            ),
        );

        parent::__construct($this->blog->getResourceNode(), $this->details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE);
    }

    /**
     * Get sendToFollowers boolean.
     *
     * @return bool
     */
    public function getSendToFollowers()
    {
        $isPublished = $this->post->isPublished();

        return $isPublished;
    }

    /**
     * Get includeUsers array of user ids.
     *
     * @return array
     */
    public function getIncludeUserIds()
    {
        return array();
    }

    /**
     * Get excludeUsers array of user ids.
     *
     * @return array
     */
    public function getExcludeUserIds()
    {
        return array();
    }

    /**
     * Get actionKey string.
     *
     * @return string
     */
    public function getActionKey()
    {
        return $this::ACTION;
    }

    /**
     * Get iconTypeUrl string.
     *
     * @return string
     */
    public function getIconKey()
    {
        return 'blog';
    }

    /**
     * Get details.
     *
     * @return array
     */
    public function getNotificationDetails()
    {
        $notificationDetails = array_merge($this->details, array());
        $notificationDetails['resource'] = array(
            'id' => $this->blog->getId(),
            'name' => $this->resource->getName(),
            'type' => $this->resource->getResourceType()->getName(),
        );

        return $notificationDetails;
    }

    /**
     * Get if event is allowed to create notification or not.
     *
     * @return bool
     */
    public function isAllowedToNotify()
    {
        return true;
    }
}
