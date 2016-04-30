<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 4/22/15
 */

namespace Icap\SocialmediaBundle\Controller;

use Icap\SocialmediaBundle\Entity\CommentAction;
use Icap\SocialmediaBundle\Entity\LikeAction;
use Icap\SocialmediaBundle\Entity\ShareAction;
use Icap\SocialmediaBundle\Event\Log\LogSocialmediaCommentEvent;
use Icap\SocialmediaBundle\Event\Log\LogSocialmediaLikeEvent;
use Icap\SocialmediaBundle\Event\Log\LogSocialmediaShareEvent;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;

class Controller extends BaseController
{
    const MAX_PER_PAGE = 10;
    /**
     * @return \Icap\SocialmediaBundle\Manager\LikeActionManager
     */
    protected function getLikeActionManager()
    {
        return $this->get('icap_socialmedia.manager.like_action');
    }

    /**
     * @return \Icap\SocialmediaBundle\Manager\ShareActionManager
     */
    protected function getShareActionManager()
    {
        return $this->get('icap_socialmedia.manager.share_action');
    }

    /**
     * @return \Icap\SocialmediaBundle\Manager\CommentActionManager
     */
    protected function getCommentActionManager()
    {
        return $this->get('icap_socialmedia.manager.comment_action');
    }

    /**
     * @return \Icap\SocialmediaBundle\Manager\NoteActionManager
     */
    protected function getNoteActionManager()
    {
        return $this->get('icap_socialmedia.manager.note_action');
    }

    /**
     * @return \Icap\SocialmediaBundle\Manager\WallItemManager
     */
    protected function getWallItemManager()
    {
        return $this->get('icap_socialmedia.manager.wall_item');
    }

    protected function paginateQuery($queryBuilder, $page)
    {
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);

        $pagerfanta->setMaxPerPage(self::MAX_PER_PAGE);
        $pagerfanta->setCurrentPage($page);

        return $pagerfanta;
    }

    protected function getLoggedUser()
    {
        return $this->get('security.token_storage')->getToken()->getUser();
    }

    protected function dispatch($event)
    {
        $this->get('event_dispatcher')->dispatch('log', $event);

        return $this;
    }

    protected function dispatchLikeEvent(LikeAction $like)
    {
        $resource = $like->getResource();
        if ($resource !== null) {
            $event = new LogSocialmediaLikeEvent($like);

            return $this->dispatch($event);
        }
    }

    protected function dispatchShareEvent(ShareAction $share)
    {
        $resource = $share->getResource();
        if ($resource !== null) {
            $event = new LogSocialmediaShareEvent($share);

            return $this->dispatch($event);
        }
    }

    protected function dispatchCommentEvent(CommentAction $comment, $userIds)
    {
        $resource = $comment->getResource();
        if ($resource !== null) {
            $event = new LogSocialmediaCommentEvent($comment, $userIds);

            return $this->dispatch($event);
        }
    }
}
