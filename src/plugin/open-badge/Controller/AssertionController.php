<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Controller;

use LogicException;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Manager\PdfManager;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Claroline\OpenBadgeBundle\Entity\Evidence;
use Claroline\OpenBadgeBundle\Manager\AssertionManager;
use Claroline\OpenBadgeBundle\Manager\BadgeManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route(path: '/badge_assertion', name: 'apiv2_badge_assertion_')]
class AssertionController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly BadgeManager $manager,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly PdfManager $pdfManager,
        private readonly AssertionManager $assertionManager
    ) {
        $this->authorization = $authorization;
    }

    public static function getClass(): string
    {
        return Assertion::class;
    }

    public static function getName(): string
    {
        return 'badge_assertion';
    }

    public function getIgnore(): array
    {
        return ['create', 'update', 'list', 'deleteBulk'];
    }

    #[Route(path: '/current-user/{workspace}', name: 'current_user_list', methods: ['GET'])]
    public function listMyAssertionsAction(Request $request, ?string $workspace = null): JsonResponse
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $user = $this->tokenStorage->getToken()->getUser();

        $filters = [
            'recipient' => $user->getUuid(),
        ];
        if ($workspace) {
            $filters['workspace'] = $workspace;
        }

        $assertions = $this->crud->list(Assertion::class, array_merge(
            $request->query->all(),
            ['hiddenFilters' => $filters]
        ));

        return new JsonResponse($assertions);
    }

    /**
     * @EXT\ParamConverter("assertion", class="Claroline\OpenBadgeBundle\Entity\Assertion", options={"mapping": {"assertion": "uuid"}})
     */
    #[Route(path: '/{assertion}/evidences', name: 'evidences', methods: ['GET'])]
    public function listEvidencesAction(Request $request, Assertion $assertion): JsonResponse
    {
        $this->checkPermission('OPEN', $assertion, [], true);

        return new JsonResponse(
            $this->crud->list(Evidence::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['assertion' => $assertion->getUuid()]]
            ))
        );
    }

    /**
     * Downloads pdf version of assertion.
     *
     *
     * @EXT\ParamConverter("assertion", class="Claroline\OpenBadgeBundle\Entity\Assertion", options={"mapping": {"assertion": "uuid"}})
     */
    #[Route(path: '/{assertion}/pdf/download', name: 'pdf_download', methods: ['GET'])]
    public function downloadPdfAction(Assertion $assertion): StreamedResponse
    {
        $this->checkPermission('OPEN', $assertion, [], true);

        $badge = $assertion->getBadge();
        $user = $assertion->getRecipient();

        $fileName = TextNormalizer::toKey($badge->getName().'-'.$user->getFirstName().$user->getLastName());

        return new StreamedResponse(function () use ($assertion): void {
            echo $this->pdfManager->fromHtml(
                $this->manager->generateCertificate($assertion)
            );
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.$fileName.'.pdf',
        ]);
    }

    protected function getDefaultHiddenFilters(): array
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            // only get assertions for the badges the current user can grant
            return [
                'fromGrantableBadges' => true,
            ];
        }

        return [];
    }

    /**
     * Transfer badges from one user to another.
     *
     *
     * @EXT\ParamConverter("userFrom", class="Claroline\CoreBundle\Entity\User", options={"mapping": {"userFrom": "uuid"}})
     * @EXT\ParamConverter("userTo", class="Claroline\CoreBundle\Entity\User", options={"mapping": {"userTo": "uuid"}})
     */
    #[Route(path: '/transfer/{userFrom}/{userTo}/', name: 'transfer', methods: ['POST'])]
    public function transferBadgesAction(User $userFrom, User $userTo): JsonResponse
    {
        $this->canAdministrate();

        $this->assertionManager->transferBadgesAction($userFrom, $userTo);

        return new JsonResponse();
    }

    private function canAdministrate(): void
    {
        $tool = $this->om->getRepository(OrderedTool::class)
            ->findOneBy(['name' => 'badges', 'contextName' => DesktopContext::getName()]);

        if (!$tool) {
            throw new LogicException("Annotation error: cannot found tool 'badges'");
        }

        $granted = $this->authorization->isGranted('ADMINISTRATE', $tool);

        if (!$granted) {
            throw new AccessDeniedException('badges cannot be opened');
        }
    }
}
