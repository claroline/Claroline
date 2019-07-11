<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace  Icap\NotificationBundle\Controller\APINew;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Icap\NotificationBundle\Entity\NotificationViewer;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/notifications")
 */
class NotificationsViewerController extends AbstractCrudController
{
    /**
     * @DI\InjectParams({
     *    "tokenStorage" = @DI\Inject("security.token_storage"),
     *    "finder"       = @DI\Inject("claroline.api.finder")
     * })
     *
     * @param StrictDispatcher $eventDispatcher
     * @param MailManager      $mailManager
     */
    public function __construct(
        $tokenStorage,
        $finder
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->finder = $finder;
    }

    public function getName()
    {
        return 'notifications';
    }

    /**
     * @EXT\Route(
     *    "/current",
     *    name="apiv2_get_notifications_current",
     *    options={ "method_prefix" = false }
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * find notifications here
     */
    public function getCurrent(Request $request)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $filters = [
          'user' => $user->getId(),
        ];

        return new JsonResponse(
          $this->finder->search(NotificationViewer::class,
          array_merge($request->query->all(), ['hiddenFilters' => $filters]))
        );
    }

    public function getClass()
    {
        return NotificationViewer::class;
    }
}
