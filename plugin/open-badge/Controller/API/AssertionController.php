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
use Claroline\PdfGeneratorBundle\Manager\PdfManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @EXT\Route("/assertion")
 */
class AssertionController extends AbstractCrudController
{
    /** @var string */
    private $filesDir;

    /** @var OpenBadgeManager */
    private $manager;

    /** @var PdfManager */
    private $pdfManager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param TwigEngine     $templating
     * @param FinderProvider $finder
     */
    public function __construct(
        $filesDir,
        OpenBadgeManager $manager,
        PdfManager $pdfManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->filesDir = $filesDir;
        $this->manager = $manager;
        $this->pdfManager = $pdfManager;
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

    /**
     * @EXT\Route(
     *     "/{assertion}/pdf/download",
     *     name="apiv2_assertion_pdf_download"
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "assertion",
     *     class="ClarolineOpenBadgeBundle:Assertion",
     *     options={"mapping": {"assertion": "uuid"}}
     * )
     * @EXT\ParamConverter("currentUser", converter="current_user", options={"allowAnonymous"=false})
     *
     * Downloads pdf version of assertion
     *
     * @param Assertion $assertion
     * @param User      $currentUser
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function assertionPdfDownloadAction(Assertion $assertion, User $currentUser)
    {
        $badge = $assertion->getBadge();
        $user = $assertion->getRecipient();
        $pdfName = $badge->getName().'_'.$user->getFirstName().$user->getLastName();

        $content = $this->manager->generateCertificate($assertion);
        $pdf = $this->pdfManager->create($content, $pdfName, $currentUser, 'badges');

        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$pdfName.'.pdf"',
        ];

        return new Response(
            file_get_contents($this->filesDir.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.$pdf->getPath()),
            200,
            $headers
        );
    }

    public function getClass()
    {
        return Assertion::class;
    }
}
