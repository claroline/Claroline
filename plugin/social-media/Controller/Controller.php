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
use Icap\SocialmediaBundle\Manager\CommentActionManager;
use Icap\SocialmediaBundle\Manager\LikeActionManager;
use Icap\SocialmediaBundle\Manager\NoteActionManager;
use Icap\SocialmediaBundle\Manager\ShareActionManager;
use Icap\SocialmediaBundle\Manager\WallItemManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class Controller extends BaseController
{
    const MAX_PER_PAGE = 10;

    public function setLikeActionManager(LikeActionManager $likeActionManager)
    {
        $this->likeActionManager = $likeActionManager;
    }

    /**
     * @return \Icap\SocialmediaBundle\Manager\LikeActionManager
     */
    protected function getLikeActionManager()
    {
        return $this->likeActionManager;
    }

    public function setShareActionManager(ShareActionManager $shareActionManager)
    {
        $this->shareActionManager = $shareActionManager;
    }

    /**
     * @return \Icap\SocialmediaBundle\Manager\ShareActionManager
     */
    protected function getShareActionManager()
    {
        return $this->shareActionManager;
    }

    public function setCommentActionManger(CommentActionManager $commentActionManager)
    {
        $this->commentActionManager = $commentActionManager;
    }

    /**
     * @return \Icap\SocialmediaBundle\Manager\CommentActionManager
     */
    protected function getCommentActionManager()
    {
        return $this->commentActionManager;
    }

    /**
     * @return \Icap\SocialmediaBundle\Manager\NoteActionManager
     */
    public function setNoteActionManager(NoteActionManager $noteActionManager)
    {
        return $this->noteActionManager = $noteActionManager;
    }

    /**
     * @return \Icap\SocialmediaBundle\Manager\NoteActionManager
     */
    protected function getNoteActionManager()
    {
        return $this->noteActionManager;
    }

    /**
     * @return \Icap\SocialmediaBundle\Manager\WallItemManager
     */
    public function setWallItemManager(WallItemManager $wallItemManager)
    {
        return $this->wallItemManager = $wallItemManager;
    }

    /**
     * @return \Icap\SocialmediaBundle\Manager\WallItemManager
     */
    protected function getWallItemManager()
    {
        return $this->wallItemManager;
    }

    public function setTokenStorage(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    protected function getLoggedUser()
    {
        return $this->tokenStorage->getToken()->getUser();
    }

    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    protected function dispatch($event)
    {
        $this->dispatcher->dispatch('log', $event);

        return $this;
    }

    protected function dispatchLikeEvent(LikeAction $like)
    {
        $resource = $like->getResource();
        if (null !== $resource) {
            $event = new LogSocialmediaLikeEvent($like);

            return $this->dispatch($event);
        }
    }

    protected function dispatchShareEvent(ShareAction $share)
    {
        $resource = $share->getResource();
        if (null !== $resource) {
            $event = new LogSocialmediaShareEvent($share);

            return $this->dispatch($event);
        }
    }

    protected function dispatchCommentEvent(CommentAction $comment, $userIds)
    {
        $resource = $comment->getResource();
        if (null !== $resource) {
            $event = new LogSocialmediaCommentEvent($comment, $userIds);

            return $this->dispatch($event);
        }
    }
}
