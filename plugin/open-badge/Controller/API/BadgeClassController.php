<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Controller\API;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Claroline\OpenBadgeBundle\Manager\OpenBadgeManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/badge-class")
 */
class BadgeClassController extends AbstractCrudController
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var OpenBadgeManager */
    private $manager;

    /**
     * BadgeClassController constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     * @param OpenBadgeManager      $manager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        OpenBadgeManager $manager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->manager = $manager;
    }

    public function getName()
    {
        return 'badge-class';
    }

    public function getClass()
    {
        return BadgeClass::class;
    }

    /**
     * @Route("/{badge}/users/add", name="apiv2_badge-class_add_users")
     * @EXT\Method("PATCH")
     * @EXT\ParamConverter("badge", class="ClarolineOpenBadgeBundle:BadgeClass", options={"mapping": {"badge": "uuid"}})
     *
     * @param BadgeClass $badge
     * @param Request    $request
     *
     * @return JsonResponse
     */
    public function addUserAction(BadgeClass $badge, Request $request)
    {
        $users = $this->decodeIdsString($request, User::class);

        foreach ($users as $user) {
            $this->manager->addAssertion($badge, $user);
        }

        return new JsonResponse(
            $this->serializer->serialize($badge)
        );
    }

    /**
     * @Route("/enable", name="apiv2_badge-class_enable")
     * @EXT\Method("PUT")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function enableAction(Request $request)
    {
        $badges = $this->decodeIdsString($request, BadgeClass::class);

        foreach ($badges as $badge) {
            $this->crud->replace($badge, 'enabled', true);
        }

        return new JsonResponse(
            array_map(function (BadgeClass $badge) {
                return $this->serializer->serialize($badge);
            }, $badges)
        );
    }

    /**
     * @Route("/disable", name="apiv2_badge-class_disable")
     * @EXT\Method("PUT")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function disableAction(Request $request)
    {
        $badges = $this->decodeIdsString($request, BadgeClass::class);

        foreach ($badges as $badge) {
            $this->crud->replace($badge, 'enabled', false);
        }

        return new JsonResponse(
            array_map(function (BadgeClass $badge) {
                return $this->serializer->serialize($badge);
            }, $badges)
        );
    }

    /**
     * @Route("/{badge}/users/remove", name="apiv2_badge-class_remove_users")
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter("badge", class="ClarolineOpenBadgeBundle:BadgeClass", options={"mapping": {"badge": "uuid"}})
     *
     * @param BadgeClass $badge
     * @param Request    $request
     *
     * @return JsonResponse
     */
    public function removeUserAction(BadgeClass $badge, Request $request)
    {
        $assertions = $this->decodeIdsString($request, Assertion::class);

        foreach ($assertions as $assertion) {
            $this->manager->revokeAssertion($assertion);
        }

        return new JsonResponse(
            $this->serializer->serialize($badge)
        );
    }

    /**
     * @Route("/workspace/{workspace}", name="apiv2_badge-class_workspace_badge_list")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("workspace", class="ClarolineCoreBundle:Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     *
     * @param Request   $request
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function getWorkspaceBadges(Request $request, Workspace $workspace)
    {
        return new JsonResponse(
            $this->finder->search(BadgeClass::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['workspace' => $workspace->getUuid()]]
            ))
        );
    }

    /**
     * @Route("/{badge}/assertion", name="apiv2_badge-class_assertion")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("badge", class="ClarolineOpenBadgeBundle:BadgeClass", options={"mapping": {"badge": "uuid"}})
     *
     * @param Request    $request
     * @param BadgeClass $badge
     *
     * @return JsonResponse
     */
    public function getAssertionsAction(Request $request, BadgeClass $badge)
    {
        return new JsonResponse(
            $this->finder->search(Assertion::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['badge' => $badge->getUuid(), 'revoked' => false]]
            ))
        );
    }
}
