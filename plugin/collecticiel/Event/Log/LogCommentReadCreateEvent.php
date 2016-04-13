<?php

namespace Innova\CollecticielBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Innova\CollecticielBundle\Entity\Dropzone;
use Innova\CollecticielBundle\Entity\Comment;
use Innova\CollecticielBundle\Entity\CommentRead;

class LogCommentReadCreateEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-innova_collecticiel-comment-read_create';

    /**
     * @param Dropzone    $dropzone
     * @param mixed       $dropzoneChangeSet
     * @param CommentRead $comment_read
     */
    public function __construct(Dropzone $dropzone, $dropzoneChangeSet, CommentRead $comment_read)
    {
        $details = array(
            'dropzone' => array(
                'id' => $dropzone->getId(),
                'changeSet' => $dropzoneChangeSet,
            ),
            'comment_read' => array(
                'id' => $comment_read->getId(),
                'comment' => $comment_read->getComment(),
                'user' => $comment_read->getUser(),
            ),
        );

        parent::__construct($dropzone->getResourceNode(), $details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(LogGenericEvent::DISPLAYED_WORKSPACE);
    }
}
