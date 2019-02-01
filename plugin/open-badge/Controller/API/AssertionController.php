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
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Claroline\OpenBadgeBundle\Entity\Evidence;
use Claroline\OpenBadgeBundle\Manager\OpenBadgeManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @EXT\Route("/assertion")
 */
class AssertionController extends AbstractCrudController
{
    /**
     * @DI\InjectParams({
     *     "tokenStorage" = @DI\Inject("security.token_storage"),
     *     "manager"      = @DI\Inject("claroline.manager.open_badge_manager"),
     * })
     *
     * @param TwigEngine     $templating
     * @param FinderProvider $finder
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        OpenBadgeManager $manager
    ) {
        $this->manager = $manager;
        $this->tokenStorage = $tokenStorage;
    }

    public function getName()
    {
        return 'badge-assertion';
    }

    /**
     * @EXT\Route("/{assertion}/evidences", name="apiv2_assertion_evidences")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("assertion", class="ClarolineOpenBadgeBundle:Assertion", options={"mapping": {"assertion": "uuid"}})
     *
     * @return JsonResponse
     */
    public function getEvidencesAction(Request $request, Assertion $assertion)
    {
        return new JsonResponse(
            $this->finder->search(Evidence::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['assertion' => $assertion->getUuid()]]
            ))
        );
    }

    /**
     * @EXT\Route("/current-user", name="apiv2_assertion_current_user_list")
     * @EXT\Method("GET")
     *
     * @return JsonResponse
     */
    public function getMyAssertionsAction(Request $request)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $assertions = $this->finder->search(Assertion::class, array_merge(
            $request->query->all(),
            ['hiddenFilters' => ['recipient' => $user->getUuid()]]
        ));

        return new JsonResponse($assertions);
    }

    /**
     * @EXT\Route("/user/{user}", name="apiv2_assertion_user_list")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("user", class="ClarolineCoreBundle:User", options={"mapping": {"user": "uuid"}})
     *
     * @return JsonResponse
     */
    public function getUserAssertionsAction(Request $request, User $user)
    {
        $assertions = $this->finder->search(Assertion::class, array_merge(
            $request->query->all(),
            ['hiddenFilters' => ['recipient' => $user->getUuid()]]
        ));

        return new JsonResponse($assertions);
    }

    public function getClass()
    {
        return Assertion::class;
    }
}
