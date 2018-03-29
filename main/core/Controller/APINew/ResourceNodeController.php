<?php

namespace Claroline\CoreBundle\Controller\APINew;

use Claroline\AppBundle\Controller\AbstractCrudController;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * This controller will probably need to change heavily in the future.
 */
class ResourceNodeController extends AbstractCrudController
{
    /* var TokenStorageInterface */
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
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @Route("/portal", name="apiv2_portal_index", options={ "method_prefix" = false })
     *
     * @todo probably move this somewhere else
     *
     * @param Request $request
     *
     * @return array
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
     * @Route("/resourcespicker", name="apiv2_resources_picker", options={ "method_prefix" = false })
     *
     * @param Request $request
     *
     * @return array
     */
    public function resourcesPickerAction(Request $request)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if ($user === 'anon.') {
            throw new AccessDeniedException();
        }
        $options = $request->query->all();

        $options['hiddenFilters']['active'] = true;
        $options['hiddenFilters']['resourceTypeBlacklist'] = ['directory', 'activity'];
        $options['hiddenFilters']['roles'] = $user->getRoles();

        $result = $this->finder->search(
            'Claroline\CoreBundle\Entity\Resource\ResourceNode',
            $options
        );

        return new JsonResponse($result);
    }

    /**
     * @return array
     */
    public function getName()
    {
        return 'resourcenode';
    }
}
