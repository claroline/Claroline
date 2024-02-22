<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle\Controller;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\ScormBundle\Entity\Sco;
use Claroline\ScormBundle\Entity\Scorm;
use Claroline\ScormBundle\Entity\ScoTracking;
use Claroline\ScormBundle\Exception\InvalidScormArchiveException;
use Claroline\ScormBundle\Manager\EvaluationManager;
use Claroline\ScormBundle\Manager\ScormManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ScormController
{
    use RequestDecoderTrait;
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly TranslatorInterface $translator,
        private readonly FinderProvider $finder,
        private readonly SerializerProvider $serializer,
        private readonly ScormManager $scormManager,
        private readonly EvaluationManager $evaluationManager
    ) {
        $this->authorization = $authorization;
    }

    /**
     * @Route("/workspace/{workspace}/scorm/archive/upload", name="apiv2_scorm_archive_upload")
     *
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function uploadAction(Workspace $workspace, Request $request): JsonResponse
    {
        $files = $request->files->all();

        if (empty($files)) {
            throw new InvalidDataException('No archive to import.');
        }

        try {
            $file = array_pop($files); // we can only accept one file
            $data = $this->scormManager->uploadScormArchive($workspace, $file);
        } catch (InvalidScormArchiveException $e) {
            throw new InvalidDataException($this->translator->trans($e->getMessage(), [], 'resource'));
        }

        return new JsonResponse($data, 200);
    }

    /**
     * @Route("/scorm/{scorm}", name="apiv2_scorm_update", methods={"PUT"})
     *
     * @EXT\ParamConverter("scorm", class="Claroline\ScormBundle\Entity\Scorm", options={"mapping": {"scorm": "uuid"}})
     */
    public function updateAction(Scorm $scorm, Request $request): JsonResponse
    {
        $this->checkPermission('EDIT', $scorm->getResourceNode(), [], true);

        return new JsonResponse(
            $this->scormManager->updateScorm($scorm, $this->decodeRequest($request))
        );
    }

    /**
     * @Route("/sco/{sco}/commit", name="apiv2_scormscotracking_update", methods={"PUT"})
     *
     * @EXT\ParamConverter("sco", class="Claroline\ScormBundle\Entity\Sco", options={"mapping": {"sco": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function updateTrackingAction(Sco $sco, User $user, Request $request): JsonResponse
    {
        $scorm = $sco->getScorm();
        $this->checkPermission('OPEN', $scorm->getResourceNode(), [], true);

        $data = $this->decodeRequest($request);
        $tracking = $this->evaluationManager->updateScoTracking($sco, $user, $data);

        return new JsonResponse(
            $this->serializer->serialize($tracking)
        );
    }

    /**
     * @Route("/scorm/{scorm}/trackings/list", name="apiv2_scormscotracking_list")
     *
     * @EXT\ParamConverter("scorm", class="Claroline\ScormBundle\Entity\Scorm", options={"mapping": {"scorm": "uuid"}})
     */
    public function listTrackingsAction(Scorm $scorm, Request $request): JsonResponse
    {
        $this->checkPermission('EDIT', $scorm->getResourceNode(), [], true);

        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['scorm'] = $scorm->getId();

        return new JsonResponse(
            $this->finder->search(ScoTracking::class, $params)
        );
    }

    /**
     * @Route("/scorm/{scorm}/trackings/export", name="apiv2_scormscotracking_export")
     *
     * @EXT\ParamConverter("scorm", class="Claroline\ScormBundle\Entity\Scorm", options={"mapping": {"scorm": "uuid"}})
     */
    public function exportTrackingsAction(Scorm $scorm): StreamedResponse
    {
        $this->checkPermission('EDIT', $scorm->getResourceNode(), [], true);

        // I use finder to automatically retrieve info from ResourceUserEvaluation
        // which are exposed by ScoTracking serializer
        $trackingList = $this->finder->search(ScoTracking::class, ['filters' => [
            'scorm' => $scorm->getId(),
        ]]);

        $fileName = "results-{$scorm->getResourceNode()->getName()}";
        $fileName = TextNormalizer::toKey($fileName);

        return new StreamedResponse(function () use ($trackingList) {
            // Prepare CSV file
            $handle = fopen('php://output', 'w+');

            // Create header
            fputcsv($handle, [
                $this->translator->trans('last_name', [], 'platform'),
                $this->translator->trans('first_name', [], 'platform'),
                $this->translator->trans('email', [], 'platform'),
                $this->translator->trans('views', [], 'platform'),
                $this->translator->trans('attempts', [], 'platform'),
                $this->translator->trans('last_session_date', [], 'scorm'),
                $this->translator->trans('total_time', [], 'platform'),
                $this->translator->trans('score', [], 'platform'),
                $this->translator->trans('score_min', [], 'platform'),
                $this->translator->trans('score_max', [], 'platform'),
                $this->translator->trans('progression', [], 'platform'),
                $this->translator->trans('status', [], 'platform'),
            ], ';', '"');

            foreach ($trackingList['data'] as $tracking) {
                // put Workspace evaluation
                fputcsv($handle, [
                    $tracking['user']['lastName'],
                    $tracking['user']['firstName'],
                    $tracking['user']['email'],
                    $tracking['views'],
                    $tracking['attempts'],
                    $tracking['latestDate'],
                    $tracking['totalTime'],
                    $tracking['scoreRaw'],
                    $tracking['scoreMin'],
                    $tracking['scoreMax'],
                    $tracking['progression'],
                    $tracking['lessonStatus'],
                ], ';', '"');
            }

            fclose($handle);

            return $handle;
        }, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'.csv"',
        ]);
    }
}
