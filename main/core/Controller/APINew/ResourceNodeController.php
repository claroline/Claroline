<?php

namespace Claroline\CoreBundle\Controller\APINew;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ResourceNodeController extends AbstractController
{
    /**
     * @Route("/portal", name="apiv2_portal_index", options={ "method_prefix" = false })
     *
     * @param Request $request
     *
     * @return array
     */
    public function portalSearchAction(Request $request)
    {
        $options = $request->query->all();

        // Limit the search to resource nodes published to portal
        $options['filters']['publishedToPortal'] = true;

        // Limit the search to only the authorized resource types which can be displayed on the portal
        $options['filters']['resourceType'] = $this->container->get('claroline.manager.portal_manager')->getPortalEnabledResourceTypes();

        $result = $this->finder->search(
            'Claroline\CoreBundle\Entity\Resource\ResourceNode',
            $options
        );

        // unset filters
        //TODO: this workaround will be avoidable after the merge of #2901 by using the hiddenFilters key in $options to to hide the filters in the client.
        foreach ($result['filters'] as $key => $value) {
            if ($value['property'] === 'publishedToPortal' || $value['property'] === 'resourceType') {
                unset($result['filters'][$key]);
            }
        }

        return new JsonResponse($result);
    }
}
