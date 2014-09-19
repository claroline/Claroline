<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 8/28/14
 * Time: 9:37 AM
 */

namespace Icap\WebsiteBundle\Controller;

use Icap\WebsiteBundle\Entity\Website;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/*
 * @Route(
 *      "/{websiteId}",
 *      requirements={"websiteId" = "\d+"}
 * )
 */
class WebsitePageController extends Controller{
    /**
     * @Route(
     *      "/{pageId}",
     *      requirements={"pageId" = "\d+"},
     *      name="icap_website_page_view"
     * )
     * @ParamConverter("website", class="IcapWebsiteBundle:Website", options={"id" = "websiteId"})
     * @Template()
     * @Method({"GET"})
     */
    public function viewAction(Website $website, $pageId)
    {
        $this->checkAccess("OPEN", $website);
        $isAdmin = $this->isUserGranted("EDIT", $website);
        $user = $this->getLoggedUser();
        $pageManager = $this->getWebsitePageManager();
        $pages = $pageManager->getPageTree($website, $isAdmin, true, true);
        $currentPage = $pageManager->getPages($website, $pageId, $isAdmin, false);
        $website->setPages($pages);

        return array(
            '_resource' => $website,
            'workspace' => $website->getResourceNode()->getWorkspace(),
            'page' => $currentPage,
            'isAdmin' => $isAdmin,
            'user' => $user
        );
    }

    /**
     * @Route(
     *      "/page/{pageId}",
     *      requirements={"pageId" = "\d+"},
     *      name="icap_website_page_get"
     * )
     * @ParamConverter("website", class="IcapWebsiteBundle:Website", options={"id" = "websiteId"})
     * @Method({"GET"})
     */
    public function getAction(Website $website, $pageId)
    {
        $this->checkAccess("OPEN", $website);
        $isAdmin = $this->isUserGranted("EDIT", $website);

        $pageManager = $this->getWebsitePageManager();
        $page = $pageManager->getPages($website, $pageId, $isAdmin, true);
        if ($page === NULL) {
            $page = array();
        }
        $response = new JsonResponse();
        $response->setData($page);

        return $response;
    }

    /**
     * @Route(
     *      "/page",
     *      name="icap_website_page_post"
     * )
     * @ParamConverter("website", class="IcapWebsiteBundle:Website", options={"id" = "websiteId"})
     * @Method({"POST"})
     */
    public function postAction(Request $request, Website $website)
    {
        $this->checkAccess("EDIT", $website);
        $response = new JsonResponse();
        $pageManager = $this->getWebsitePageManager();
        $newPage = $pageManager->createEmptyPage($website);
        $newPageJson = $pageManager->processForm($newPage, $request->request->all(), "POST");
        $response->setData($newPageJson);

        return $response;
    }

    /**
     * @Route(
     *      "/page/{pageId}",
     *      requirements={"pageId" = "\d+"},
     *      name="icap_website_page_post"
     * )
     * @ParamConverter("website", class="IcapWebsiteBundle:Website", options={"id" = "websiteId"})
     * @Method({"PUT"})
     */
    public function putAction(Request $request, Website $website, $pageId)
    {
        $this->checkAccess("EDIT", $website);
        $response = new JsonResponse();
        $pageManager = $this->getWebsitePageManager();
        $page = $pageManager->getPages($website, $pageId, true, false);
        $pageJson = $pageManager->processForm($page, $request->request->all(), "PUT");
        $response->setData($pageJson);

        return $response;
    }

    /**
     * @Route(
     *      "/page/{pageId}/{newParentId}/{previousSiblingId}",
     *      requirements={"pageId" = "\d+", "newParentId" = "\d+", "previousSiblingId" = "\d+"},
     *      defaults={"previousSiblingId" = 0},
     *      name="icap_website_page_move"
     * )
     * @ParamConverter("website", class="IcapWebsiteBundle:Website", options={"id" = "websiteId"})
     * @Method({"PUT"})
     */
    public function moveAction(Website $website, $pageId, $newParentId, $previousSiblingId)
    {
        $this->checkAccess("EDIT", $website);
        $response = new JsonResponse();
        $pageManager = $this->getWebsitePageManager();

        try{
            $pageManager->handleMovePage($website, array(
                'pageId' => $pageId,
                'newParentId' => $newParentId,
                'previousSiblingId' => $previousSiblingId
                )
            );
        } catch(\Exception $exception) {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $response;
    }

    /**
     * @Route(
     *      "/page/{pageId}",
     *      requirements={"pageId" = "\d+"},
     *      name="icap_website_page_delete"
     * )
     * @ParamConverter("website", class="IcapWebsiteBundle:Website", options={"id" = "websiteId"})
     * @Method({"DELETE"})
     */
    public function deleteAction(Request $request, Website $website, $pageId)
    {
        $this->checkAccess("EDIT", $website);
        $response = new JsonResponse();
        $pageManager = $this->getWebsitePageManager();
        $page = $pageManager->getPage($website, $pageId, true, false);

        try{
            $pageManager->deletePage($page);
        } catch(\Exception $exception) {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $response;
    }
}