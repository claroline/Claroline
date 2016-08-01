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

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\ScormBundle\Entity\ScormResource;
use Claroline\ScormBundle\Manager\ScormManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ScormController extends Controller
{
    private $authorization;
    private $resourceManager;
    private $scormManager;
    private $serializer;

    /**
     * @DI\InjectParams({
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "scormManager"    = @DI\Inject("claroline.manager.scorm_manager"),
     *     "serializer"      = @DI\Inject("jms_serializer")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ResourceManager $resourceManager,
        ScormManager $scormManager,
        Serializer $serializer
    ) {
        $this->authorization = $authorization;
        $this->resourceManager = $resourceManager;
        $this->scormManager = $scormManager;
        $this->serializer = $serializer;
    }

    /**
     * @EXT\Route(
     *     "/scorm/{resourceNode}/results",
     *     name="claro_scorm_results",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineScormBundle::scormResults.html.twig")
     */
    public function scormResultsAction(ResourceNode $resourceNode)
    {
        $resource = $this->resourceManager->getResourceFromNode($resourceNode);
        $this->checkScormRightAndType($resource, 'EDIT');
        $resourceTypeName = $resourceNode->getResourceType()->getName();
        $scos = $resource->getScos();
        $serializedScos = $this->serializer->serialize(
            $scos,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        switch ($resourceTypeName) {
            case 'claroline_scorm_12':
                $type = 'scorm12';
                $trackings = $this->scormManager->getScorm12TrackingsByResource($resource);
                break;
            case 'claroline_scorm_2004':
                $type = 'scorm2004';
                $trackings = $this->scormManager->getScorm2004TrackingsByResource($resource);
                break;
            default:
                $type = null;
                $trackings = [];
                break;
        }
        $serializedTrackings = $this->serializer->serialize(
            $trackings,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return [
            'resource' => $resource,
            'resourceNode' => $resourceNode,
            'type' => $type,
            'workspace' => $resourceNode->getWorkspace(),
            'scos' => $serializedScos,
            'trackings' => $serializedTrackings,
        ];
    }

    /**
     * @EXT\Route(
     *     "/scorm/{resourceNode}/tracking/sco/{scoId}/user/{user}/details",
     *     name="claro_scorm_get_tracking_details",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function getScormTrackingDetailsAction(User $user, ResourceNode $resourceNode, $scoId)
    {
        $results = [];
        $resource = $this->resourceManager->getResourceFromNode($resourceNode);
        $this->checkScormRightAndType($resource, 'EDIT');
        $resourceTypeName = $resourceNode->getResourceType()->getName();

        switch ($resourceTypeName) {
            case 'claroline_scorm_12':
                $type = 'scorm12';
                break;
            case 'claroline_scorm_2004':
                $type = 'scorm2004';
                break;
            default:
                $type = null;
                break;
        }
        $trackingDetails = $this->scormManager->getScormTrackingDetails($user, $resourceNode, $type);

        foreach ($trackingDetails as $log) {
            $details = $log->getDetails();

            if (!isset($details['scoId']) || intval($details['scoId']) === intval($scoId)) {
                $results[] = ['dateLog' => $log->getDateLog(), 'details' => $details];
            }
        }

        return new JsonResponse($results, 200);
    }

    private function checkScormRightAndType(ScormResource $scorm, $right)
    {
        $resourceTypeName = $scorm->getResourceNode()->getResourceType()->getName();
        $collection = new ResourceCollection([$scorm->getResourceNode()]);

        if (!$this->authorization->isGranted($right, $collection) || ($resourceTypeName !== 'claroline_scorm_12' && $resourceTypeName !== 'claroline_scorm_2004')) {
            throw new AccessDeniedException();
        }
    }
}
