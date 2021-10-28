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
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Claroline\OpenBadgeBundle\Entity\Evidence;
use Claroline\OpenBadgeBundle\Manager\BadgeManager;
use Dompdf\Dompdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/assertion")
 */
class AssertionController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var BadgeManager */
    private $manager;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        PlatformConfigurationHandler $config,
        BadgeManager $manager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->authorization = $authorization;
        $this->config = $config;
        $this->manager = $manager;
        $this->tokenStorage = $tokenStorage;
    }

    public function getClass()
    {
        return Assertion::class;
    }

    public function getName()
    {
        return 'badge-assertion';
    }

    /**
     * @Route("/current-user", name="apiv2_assertion_current_user_list", methods={"GET"})
     */
    public function listMyAssertionsAction(Request $request): JsonResponse
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $user = $this->tokenStorage->getToken()->getUser();
        $assertions = $this->finder->search(Assertion::class, array_merge(
            $request->query->all(),
            ['hiddenFilters' => ['recipient' => $user->getUuid()]]
        ));

        return new JsonResponse($assertions);
    }

    /**
     * @Route("/{assertion}/evidences", name="apiv2_assertion_evidences", methods={"GET"})
     * @EXT\ParamConverter("assertion", class="ClarolineOpenBadgeBundle:Assertion", options={"mapping": {"assertion": "uuid"}})
     */
    public function listEvidencesAction(Request $request, Assertion $assertion): JsonResponse
    {
        $this->checkPermission('OPEN', $assertion, [], true);

        return new JsonResponse(
            $this->finder->search(Evidence::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['assertion' => $assertion->getUuid()]]
            ))
        );
    }

    /**
     * Downloads pdf version of assertion.
     *
     * @Route("/{assertion}/pdf/download", name="apiv2_assertion_pdf_download", methods={"GET"})
     * @EXT\ParamConverter("assertion", class="ClarolineOpenBadgeBundle:Assertion", options={"mapping": {"assertion": "uuid"}})
     */
    public function downloadPdfAction(Assertion $assertion): StreamedResponse
    {
        $this->checkPermission('OPEN', $assertion, [], true);

        $badge = $assertion->getBadge();
        $user = $assertion->getRecipient();

        $content = $this->manager->generateCertificate($assertion);

        $dompdf = new Dompdf();
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->set_option('tempDir', $this->config->getParameter('server.tmp_dir'));
        $dompdf->loadHtml($content);

        // Render the HTML as PDF
        $dompdf->render();

        $fileName = trim($badge->getName().''.$user->getFirstName().$user->getLastName());
        $fileName = str_replace(' ', '_', $fileName);

        return new StreamedResponse(function () use ($dompdf) {
            echo $dompdf->output();
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.$fileName.'.pdf',
        ]);
    }
}
