<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle\Controller\API;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Controller\AbstractApiController;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\ScormBundle\Entity\Sco;
use Claroline\ScormBundle\Entity\Scorm;
use Claroline\ScormBundle\Manager\Exception\InvalidScormArchiveException;
use Claroline\ScormBundle\Manager\ScormManager;
use Claroline\ScormBundle\Serializer\ScoTrackingSerializer;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

class ScormController extends AbstractApiController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /* @var FinderProvider */
    protected $finder;
    /** @var ScormManager */
    private $scormManager;
    /** @var ScoTrackingSerializer */
    private $scoTrackingSerializer;
    /** @var TranslatorInterface */
    private $translator;

    /**
     * @DI\InjectParams({
     *     "authorization"         = @DI\Inject("security.authorization_checker"),
     *     "finder"                = @DI\Inject("claroline.api.finder"),
     *     "scormManager"          = @DI\Inject("claroline.manager.scorm_manager"),
     *     "scoTrackingSerializer" = @DI\Inject("claroline.serializer.scorm.sco.tracking"),
     *     "translator"            = @DI\Inject("translator")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param FinderProvider                $finder
     * @param ScormManager                  $scormManager
     * @param ScoTrackingSerializer         $scoTrackingSerializer
     * @param TranslatorInterface           $translator
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        FinderProvider $finder,
        ScormManager $scormManager,
        ScoTrackingSerializer $scoTrackingSerializer,
        TranslatorInterface $translator
    ) {
        $this->authorization = $authorization;
        $this->finder = $finder;
        $this->scormManager = $scormManager;
        $this->scoTrackingSerializer = $scoTrackingSerializer;
        $this->translator = $translator;
    }

    /**
     * @EXT\Route(
     *    "/workspace/{workspace}/scorm/archive/upload",
     *    name="apiv2_scorm_archive_upload"
     * )
     * @EXT\ParamConverter(
     *     "workspace",
     *     class="ClarolineCoreBundle:Workspace\Workspace",
     *     options={"mapping": {"workspace": "uuid"}}
     * )
     *
     * @param Workspace $workspace
     * @param Request   $request
     *
     * @return JsonResponse
     */
    public function uploadAction(Workspace $workspace, Request $request)
    {
        $files = $request->files->all();
        $data = null;
        $error = null;

        try {
            if (1 === count($files)) {
                foreach ($files as $file) {
                    $data = $this->scormManager->uploadScormArchive($workspace, $file);
                }
            } else {
                return new JsonResponse('No uploaded file', 500);
            }
        } catch (InvalidScormArchiveException $e) {
            $error = $this->translator->trans($e->getMessage(), [], 'resource');
        }

        if (empty($error)) {
            return new JsonResponse($data, 200);
        } else {
            return new JsonResponse($error, 500);
        }
    }

    /**
     * @EXT\Route(
     *    "/scorm/{scorm}/update",
     *    name="apiv2_scorm_update"
     * )
     * @EXT\ParamConverter(
     *     "scorm",
     *     class="ClarolineScormBundle:Scorm",
     *     options={"mapping": {"scorm": "uuid"}}
     * )
     *
     * @param Scorm   $scorm
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateAction(Scorm $scorm, Request $request)
    {
        $this->checkScormRights($scorm, 'EDIT');
        $data = $this->decodeRequest($request);
        $dataToUpdate = [];

        if (isset($data['ratio'])) {
            $dataToUpdate['ratio'] = $data['ratio'];
        }
        $serializedScorm = $this->scormManager->updateScorm($scorm, $dataToUpdate);

        return new JsonResponse($serializedScorm, 200);
    }

    /**
     * @EXT\Route(
     *    "/sco/{sco}/{mode}/commit",
     *    name="apiv2_scorm_sco_commit"
     * )
     * @EXT\ParamConverter(
     *     "sco",
     *     class="ClarolineScormBundle:Sco",
     *     options={"mapping": {"sco": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Sco     $sco
     * @param string  $mode
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function scoCommitAction(Sco $sco, $mode, User $user, Request $request)
    {
        $scorm = $sco->getScorm();
        $this->checkScormRights($scorm, 'OPEN');

        $data = $this->decodeRequest($request);
        $tracking = $this->scormManager->updateScoTracking($sco, $user, $mode, $data);

        return new JsonResponse($this->scoTrackingSerializer->serialize($tracking), 200);
    }

    /**
     * @EXT\Route(
     *     "/scorm/{scorm}/trackings/list",
     *     name="apiv2_scormscotracking_list"
     * )
     * @EXT\ParamConverter(
     *     "scorm",
     *     class="ClarolineScormBundle:Scorm",
     *     options={"mapping": {"scorm": "uuid"}}
     * )
     *
     * @param Scorm   $scorm
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function scoTrackingsListAction(Scorm $scorm, Request $request)
    {
        $this->checkScormRights($scorm, 'EDIT');

        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['scorm'] = $scorm->getId();
        $data = $this->finder->search('Claroline\ScormBundle\Entity\ScoTracking', $params);

        return new JsonResponse($data, 200);
    }

    private function checkScormRights(Scorm $scorm, $right)
    {
        $collection = new ResourceCollection([$scorm->getResourceNode()]);

        if (!$this->authorization->isGranted($right, $collection)) {
            throw new AccessDeniedException();
        }
    }
}
