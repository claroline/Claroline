<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 4/24/15
 */

namespace Icap\SocialmediaBundle\Listener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class ResourceActionsListener.
 */
class ResourceActionsListener
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var HttpKernelInterface
     */
    private $httpKernel;

    public function __construct(
        RequestStack $requestStack,
        HttpKernelInterface $httpKernel
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->httpKernel = $httpKernel;
    }

    public function onLikeAction($event)
    {
        $this->redirect(
            [
                '_controller' => 'IcapSocialmediaBundle:LikeAction:form',
                'resourceId' => $event->getResource()->getResourceNode()->getId(),
            ],
            $event
        );
    }

    public function onShareAction($event)
    {
        $this->redirect(
            [
                '_controller' => 'IcapSocialmediaBundle:ShareAction:form',
                'resourceId' => $event->getResource()->getResourceNode()->getId(),
            ],
            $event
        );
    }

    public function onCommentAction($event)
    {
        $this->redirect(
            [
                '_controller' => 'IcapSocialmediaBundle:CommentAction:form',
                'resourceId' => $event->getResource()->getResourceNode()->getId(),
            ],
            $event
        );
    }

    public function onNoteAction($event)
    {
        $this->redirect(
            [
                '_controller' => 'IcapSocialmediaBundle:NoteAction:form',
                'resourceId' => $event->getResource()->getResourceNode()->getId(),
            ],
            $event
        );
    }

    protected function redirect($params, $event)
    {
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}
