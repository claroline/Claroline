<?php

namespace Icap\BadgeBundle\Controller\Internal;

use Claroline\CoreBundle\Entity\User;
use Icap\BadgeBundle\Entity\UserBadge;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/internal/user_badge")
 */
class UserBadgeController extends Controller
{
    /**
     * @Route("/{id}", name="icap_badge_user_badge_edit", defaults={"id" = null, "_format" = "json"})
     * @Method({"PATCH"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function editAction(Request $request, User $user, UserBadge $userBadge)
    {
        if ($userBadge->getUser() !== $user) {
            throw new AccessDeniedHttpException();
        }

        return $this->processForm($request, $userBadge, 'PATCH');
    }

    private function processForm(Request $request, UserBadge $userBadge, $method = 'PUT')
    {
        $form = $this->createForm($this->get('icap_badge.form.user_badge'), $userBadge, array('method' => $method));

        $formParameters = $request->request->get($form->getName());

        // Patch for boolean value, parameters are only string and always true for boolean value
        if (isset($formParameters['is_shared'])) {
            if ('0' === $formParameters['is_shared']) {
                $formParameters['is_shared'] = false;
            }
        }

        $form->submit($formParameters, 'PATCH' !== $method);

        if ($form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($userBadge);
            $entityManager->flush();

            $view = View::create();
            $view->setStatusCode(201);

            $data = array(
                'user_badge' => array(
                    'id' => $userBadge->getId(),
                    'url' => $this->generateUrl('icap_badge_badge_share_view', array(
                        'username' => $userBadge->getUser()->getUsername(),
                        'badgeSlug' => $userBadge->getBadge()->getSlug(),
                    )),
                    'is_shared' => $userBadge->isIsShared(),
                ),
            );

            $view->setData($data);

            return $this->get('fos_rest.view_handler')->handle($view);
        }

        $view = View::create($form, 400);

        return $this->get('fos_rest.view_handler')->handle($view);
    }
}
