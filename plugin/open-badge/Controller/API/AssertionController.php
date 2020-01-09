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
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Claroline\OpenBadgeBundle\Entity\Evidence;
use Claroline\OpenBadgeBundle\Manager\OpenBadgeManager;
use Dompdf\Dompdf;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @EXT\Route("/assertion")
 */
class AssertionController extends AbstractCrudController
{
    /** @var PlatformConfigurationHandler */
    private $config;

    /** @var OpenBadgeManager */
    private $manager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * AssertionController constructor.
     *
     * @param PlatformConfigurationHandler $config
     * @param OpenBadgeManager             $manager
     * @param TokenStorageInterface        $tokenStorage
     */
    public function __construct(
        PlatformConfigurationHandler $config,
        OpenBadgeManager $manager,
        TokenStorageInterface $tokenStorage
    ) {
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
     * @EXT\Route("/{assertion}/evidences", name="apiv2_assertion_evidences")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("assertion", class="ClarolineOpenBadgeBundle:Assertion", options={"mapping": {"assertion": "uuid"}})
     *
     * @param Request   $request
     * @param Assertion $assertion
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
     * @param Request $request
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
     * @param Request $request
     * @param User    $user
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
     * Downloads pdf version of assertion.
     *
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
     * @param Assertion $assertion
     * @param User      $currentUser
     * @param Request   $request
     *
     * @throws \Exception
     *
     * @return StreamedResponse
     */
    public function assertionPdfDownloadAction(Assertion $assertion, User $currentUser, Request $request)
    {
        $badge = $assertion->getBadge();
        $user = $assertion->getRecipient();

        $content = $this->manager->generateCertificate($assertion, $request->server->get('DOCUMENT_ROOT').$request->getBasePath());

        $dompdf = new Dompdf();
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->set_option('tempDir', $this->config->getParameter('server.tmp_dir'));
        $dompdf->loadHtml($content);

        // Render the HTML as PDF
        $dompdf->render();

        $fileName = trim($badge->getName().''.$user->getFirstName().$user->getLastName());
        $fileName = str_replace(' ', '_', $fileName);

        return new StreamedResponse(function () use ($dompdf, $fileName) {
            echo $dompdf->output();
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.$fileName.'.pdf',
        ]);
    }
}
