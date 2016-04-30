<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 5/6/15
 */

namespace Icap\SocialmediaBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;

class WallController extends Controller
{
    /**
     * @Route("/wall/{publicUrl}", name="icap_socialmedia_wall_view")
     * @Method({"GET"})
     * @Template()
     * @ParamConverter("loggedUser", options={"authenticatedUser" = true})
     * @ParamConverter(
     *      "profileUser",
     *      class="ClarolineCoreBundle:User",
     *      options={"publicUrl" = "publicUrl"}
     * )
     *
     * @param $loggedUser
     * @param $profileUser
     *
     * @return array
     */
    public function viewAction(User $loggedUser, User $profileUser)
    {
        $wallList = $this->itemListAction($profileUser->getId(), 1, $loggedUser);
        $wallList['user'] = $profileUser;

        return $wallList;
    }

    /**
     * @Route("/wall/list/{page}/{userId}", name="icap_socialmedia_walllist", defaults={"page" : "1"})
     * @Method({"GET"})
     * @Template()
     * @ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param $userId
     * @param $page
     * @param $user
     *
     * @return array
     */
    public function itemListAction($userId, $page, $user)
    {
        $isOwner = false;
        if ($user->getId() == $userId) {
            $isOwner = true;
        }
        $likesQB = $this->getWallItemManager()->getWallItemsForPagination($userId, $isOwner);
        $pager = $this->paginateQuery($likesQB, $page);

        return array(
            'pager' => $pager,
            'userId' => $userId,
            'isOwner' => $isOwner, );
    }

    /**
     * @Route("/wall/item/{id}", name="icap_socialmedia_wall_item_delete", requirements={"id" : "\d+"})
     * @Method({"DELETE"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param $id
     * @param User $user
     *
     * @return array
     */
    public function deleteWallItemAction($id, User $user)
    {
        $this->getWallItemManager()->removeItem($id, $user);
        $response = new JsonResponse(true);

        return $response;
    }
}
