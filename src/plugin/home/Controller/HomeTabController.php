<?php

namespace Claroline\HomeBundle\Controller;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\HomeBundle\Entity\HomeTab;
use Claroline\HomeBundle\Manager\HomeManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/home_tab")
 */
class HomeTabController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var HomeManager */
    private $manager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        HomeManager $manager
    ) {
        $this->authorization = $authorization;
        $this->manager = $manager;
    }

    public function getName()
    {
        return 'home_tab';
    }

    public function getClass()
    {
        return HomeTab::class;
    }

    public function getIgnore()
    {
        return ['list'];
    }

    /**
     * @Route("/open/{id}", name="claro_home_tab_open", methods={"GET"})
     * @EXT\ParamConverter("homeTab", options={"mapping": {"id": "uuid"}})
     */
    public function openAction(HomeTab $homeTab): JsonResponse
    {
        $accessErrors = $this->manager->getRestrictionsErrors($homeTab);
        $isManager = $this->checkPermission('EDIT', $homeTab);
        if (empty($accessErrors) || $isManager) {
            return new JsonResponse([
                'managed' => $isManager,
                'homeTab' => $this->serializer->serialize($homeTab),
                // append access restrictions to the loaded node if any
                // to let the manager knows that other users can not enter the resource
                'accessErrors' => $accessErrors,
            ]);
        }

        return new JsonResponse([
            'managed' => $isManager,
            'homeTab' => $this->serializer->serialize($homeTab, [Options::SERIALIZE_MINIMAL]),
            // append access restrictions to the loaded node if any
            // to let the manager knows that other users can not enter the resource
            'accessErrors' => $accessErrors,
        ], 403);
    }

    /**
     * Submit access code.
     *
     * @Route("/unlock/{id}", name="claro_home_tab_unlock", methods={"POST"})
     * @EXT\ParamConverter("homeTab", options={"mapping": {"id": "uuid"}})
     */
    public function unlockAction(HomeTab $homeTab, Request $request): JsonResponse
    {
        $this->manager->unlock($homeTab, $request);

        return new JsonResponse(null, 204);
    }
}
