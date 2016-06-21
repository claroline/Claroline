<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 5/7/15
 */

namespace Icap\SocialmediaBundle\Controller;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Icap\SocialmediaBundle\Entity\CommentAction;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CommentActionController extends Controller
{
    /**
     * @Route("/comments/resource/{resourceId}", name="icap_socialmedia_comments_view")
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @ParamConverter(
     *      "resource",
     *      class="ClarolineCoreBundle:Resource\ResourceNode",
     *      options={"id" = "resourceId"}
     * )
     * @Template()
     *
     * @param ResourceNode $resource
     * @param User         $user
     *
     * @return array
     */
    public function viewAction(ResourceNode $resource, User $user)
    {
        $formArray = $this->formAction($resource->getId(), $user);
        $formArray['node'] = $resource;
        $formArray['user'] = $user;

        return $formArray;
    }

    /**
     * @Route("/comment/form/{resourceId}", name="icap_socialmedia_comment_form", )
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     *
     * @param int  $resourceId
     * @param User $user
     *
     * @return array
     */
    public function formAction($resourceId, User $user)
    {
        $commentManager = $this->getCommentActionManager();
        $criteria = array('resource' => $resourceId);
        $commentsQB = $commentManager->getCommentsForPagination($resourceId);
        $pager = $this->paginateQuery($commentsQB, 1);

        return array('resourceId' => $resourceId, 'pager' => $pager);
    }

    /**
     * @Route("/comment/{resourceId}", name="icap_socialmedia_comment")
     * @Method({"POST"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param $resourceId
     * @param User $user
     *
     * @return bool
     */
    public function commentAction(Request $request, $resourceId, User $user)
    {
        $text = $request->get('social_media_comment_text');

        if ($text !== null) {
            $comment = new CommentAction();
            $comment->setText($text);
            $comment->setUser($user);
            $commentManager = $this->getCommentActionManager();
            $userIds = $commentManager->getHasCommentedUserIds($resourceId);
            $commentManager->createComment($resourceId, $comment);
            $this->dispatchCommentEvent($comment, $userIds);
        }
        if ($request->isXmlHttpRequest()) {
            $response = new JsonResponse(true);
        } else {
            $response = $this->redirectToRoute('icap_socialmedia_comments_view', array('resourceId' => $resourceId));
        }

        return $response;
    }

    /**
     * @Route("/comment/list/{resourceId}/{page}", name="icap_socialmedia_commentlist", defaults={"page" = "1"})
     *
     * @param $resourceId
     * @param $page
     *
     * @return array
     * @Template()
     */
    public function commentListAction($resourceId, $page)
    {
        $commentsQB = $this->getCommentActionManager()->getCommentsForPagination($resourceId);
        $pager = $this->paginateQuery($commentsQB, $page);

        return array('pager' => $pager, 'resourceId' => $resourceId);
    }

    /**
     * @Route("/comment/item/{id}", name="icap_socialmedia_comment_delete", requirements={"id" : "\d+"})
     * @Method({"DELETE"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param $id
     * @param User $user
     *
     * @return JsonResponse
     */
    public function deleteWallItemAction($id, User $user)
    {
        $this->getCommentActionManager()->removeComment($id, $user);
        $response = new JsonResponse(true);

        return $response;
    }
}
