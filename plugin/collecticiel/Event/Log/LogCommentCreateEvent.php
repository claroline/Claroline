<?php

namespace Innova\CollecticielBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Innova\CollecticielBundle\Entity\Dropzone;
use Innova\CollecticielBundle\Entity\Comment;

class LogCommentCreateEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-innova_collecticiel-comment_create';

    /**
     * @param Dropzone $dropzone
     * @param mixed    $dropzoneChangeSet
     * @param comment  $comment
     */
    public function __construct(Dropzone $dropzone, $dropzoneChangeSet, Comment $comment)
    {
        $details = array(
            'dropzone' => array(
                'id' => $dropzone->getId(),
                'changeSet' => $dropzoneChangeSet,
            ),
            'comment' => array(
                'id' => $comment->getId(),
                'document' => $comment->getDocument()->getId(),
                'user' => $comment->getUser(),
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
