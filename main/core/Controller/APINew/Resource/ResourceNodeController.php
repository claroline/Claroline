<?php

namespace Claroline\CoreBundle\Controller\APINew\Resource;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Manager\Resource\RightsManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @EXT\Route("/resource")
 */
class ResourceNodeController extends AbstractCrudController
{
    /** @var RightsManager */
    private $rightsManager;

    /**
     * ResourceNodeController constructor.
     *
     * @DI\InjectParams({
     *     "rightsManager" = @DI\Inject("claroline.manager.rights_manager")
     * })
     *
     * @param RightsManager $rightsManager
     */
    public function __construct(RightsManager $rightsManager)
    {
        $this->rightsManager = $rightsManager;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'resource_node';
    }

    /**
     * @EXT\Route("/{parent}", name="apiv2_resource_list", defaults={"parent"=null})
     *
     * @param Request $request
     * @param string  $parent
     * @param string  $class
     *
     * @return JsonResponse
     *
     * @todo do not return hidden resources to standard users
     */
    public function listAction(Request $request, $parent, $class = ResourceNode::class)
    {
        $options = $request->query->all();

        if (!empty($parent)) {
            // grab directory content
            $parentNode = $this->om
                ->getRepository(ResourceNode::class)
                ->findOneBy(['uuid' => $parent]);

            $options['hiddenFilters']['parent'] = $parentNode ? $parentNode->getId() : null;

            if ($parentNode) {
                $permissions = $this->rightsManager->getCurrentPermissionArray($parentNode);

                if (!isset($permissions['administrate']) || !$permissions['administrate']) {
                    $options['hiddenFilters']['published'] = true;
                }
            }
        } else {
            $options['hiddenFilters']['parent'] = null;
        }
        $options['hiddenFilters']['active'] = true;
        $options['hiddenFilters']['resourceTypeEnabled'] = true;

        return new JsonResponse(
            $this->finder->search(ResourceNode::class, $options)
        );
    }

    /**
     * @EXT\Route("/portal", name="apiv2_portal_index")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function portalSearchAction(Request $request)
    {
        $options = $request->query->all();

        $options['hiddenFilters']['published'] = true;

        // Limit the search to resource nodes published to portal
        $options['hiddenFilters']['publishedToPortal'] = true;

        // Limit the search to only the authorized resource types which can be displayed on the portal
        $options['hiddenFilters']['resourceType'] = $this->container->get('claroline.manager.portal_manager')->getPortalEnabledResourceTypes();

        return new JsonResponse(
            $this->finder->search(ResourceNode::class, $options)
        );
    }
}
