<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 5/23/16
 */

namespace Claroline\CoreBundle\Controller;

use JMS\Serializer\SerializerBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PortalController extends Controller
{
    /**
     * @EXT\Route("/", name="claro_portal_index")
     * @EXT\Route("/{path}", name="claro_portal_index_2", requirements={"path" = "^((?!api).)*$"})
     * @EXT\Method({"GET", "HEAD"})
     * @EXT\Template("ClarolineCoreBundle:Portal:index.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $types = $this->get('claroline.manager.portal_manager')->getPortalEnabledResourceTypesForSearch();

        return array('types' => $types);
    }

    /**
     * @EXT\Route("/api/index", name="claro_portal_api_get", options = { "expose" = true })
     * @EXT\Method({"GET", "HEAD"})
     */
    public function getPortalAction()
    {
        $resources = $this->get('claroline.manager.portal_manager')->getLastPublishedResourcesForEnabledTypes();

        return $this->jsonResponse($resources);
    }

    /**
     * @EXT\Route(
     *     "/api/search/{resourceType}",
     *     name="claro_portal_api_search",
     *     defaults={"resourceType" = "all"},
     *     options = { "expose" = true }
     * )
     * @EXT\Method({"GET", "POST"})
     *
     * @param Request $request
     * @param $resourceType
     *
     * @return array
     */
    public function searchPortalAction(Request $request, $resourceType)
    {
        $portalManager = $this->get('claroline.manager.portal_manager');
        $query = $request->get('query', '');
        $page = $request->get('page', 1);
        $paginatedCollection = $portalManager->searchResourcesByType($query, $page, $resourceType);

        return $this->jsonResponse($paginatedCollection);
    }

    private function jsonResponse($data)
    {
        $serializer = SerializerBuilder::create()->build();
        $jsonData = $serializer->serialize($data, 'json');
        $response = new Response($jsonData);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
