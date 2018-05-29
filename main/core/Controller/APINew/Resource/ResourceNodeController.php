<?php

namespace Claroline\CoreBundle\Controller\APINew\Resource;

use Claroline\AppBundle\Controller\AbstractCrudController;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @EXT\Route("/resource")
 */
class ResourceNodeController extends AbstractCrudController
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * ResourceNodeController constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
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
     */
    public function listAction(Request $request, $parent, $class = 'Claroline\CoreBundle\Entity\Resource\ResourceNode')
    {
        // limits the search to the current workspace
        $options = $request->query->all();
        //$options['hiddenFilters']['hidden'] = false;

        if (!empty($parent)) {
            // grab directory content
            $parentNode = $this->om
                ->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
                ->findOneBy(['uuid' => $parent]);

            $options['hiddenFilters']['parent'] = !empty($parentNode) ? $parentNode->getId() : null;
        } else {
            $options['hiddenFilters']['parent'] = null;
        }

        return new JsonResponse(
            $this->finder->search(
                'Claroline\CoreBundle\Entity\Resource\ResourceNode',
                $options
            )
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

        $result = $this->finder->search(
            'Claroline\CoreBundle\Entity\Resource\ResourceNode',
            $options
        );

        return new JsonResponse($result);
    }

    /**
     * @EXT\Route("/picker", name="apiv2_resources_picker")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function resourcesPickerAction(Request $request)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if ('anon.' === $user) {
            throw new AccessDeniedException();
        }
        $options = $request->query->all();

        $options['hiddenFilters']['active'] = true;
        $options['hiddenFilters']['resourceTypeBlacklist'] = ['directory', 'activity'];
        $options['hiddenFilters']['managerRole'] = $user->getRoles();

        $result = $this->finder->search(
            'Claroline\CoreBundle\Entity\Resource\ResourceNode',
            $options
        );

        return new JsonResponse($result);
    }
}
