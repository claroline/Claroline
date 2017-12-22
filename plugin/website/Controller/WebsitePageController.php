<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 8/28/14
 * Time: 9:37 AM.
 */

namespace Icap\WebsiteBundle\Controller;

use Icap\WebsiteBundle\Entity\Website;
use Icap\WebsiteBundle\Entity\WebsitePageTypeEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route(
 *      "/{websiteId}",
 *      requirements={"websiteId" = "\d+"}
 * )
 */
class WebsitePageController extends Controller
{
    /**
     * @Route(
     *      "/{pageId}",
     *      requirements={"pageId" = "\d+"},
     *      name="icap_website_page_view",
     *      options={"expose"=true}
     * )
     * @ParamConverter("website", class="IcapWebsiteBundle:Website", options={"id" = "websiteId"})
     * @Template("IcapWebsiteBundle:Website:view.html.twig")
     * @Method({"GET"})
     */
    public function viewAction(Website $website, $pageId)
    {
        $this->checkAccess('OPEN', $website);
        $user = $this->getLoggedUser();
        $pageManager = $this->getWebsitePageManager();
        $pages = $pageManager->getPageTree($website, false, true);
        $currentPage = $pageManager->getPages($website, $pageId, false, false);
        $website->setPages($pages);

        return [
            '_resource' => $website,
            'workspace' => $website->getResourceNode()->getWorkspace(),
            'currentPage' => $currentPage[0],
            'user' => $user,
            'pageTypes' => [
                'blank' => WebsitePageTypeEnum::BLANK_PAGE,
                'resource' => WebsitePageTypeEnum::RESOURCE_PAGE,
                'url' => WebsitePageTypeEnum::URL_PAGE,
            ],
        ];
    }

    /**
     * @Route(
     *      "/page/{pageId}",
     *      requirements={"pageId" = "\d+"},
     *      name="icap_website_page_get",
     *      options={"expose"=true}
     * )
     * @ParamConverter("website", class="IcapWebsiteBundle:Website", options={"id" = "websiteId"})
     * @Method({"GET"})
     */
    public function getAction(Website $website, $pageId)
    {
        $response = new JsonResponse();
        $this->checkAccess('OPEN', $website);
        try {
            $isAdmin = $this->isUserGranted('ADMINISTRATE', $website);

            $pageManager = $this->getWebsitePageManager();
            $page = $pageManager->getPages($website, $pageId, $isAdmin, true);
            if ($page === null) {
                $page = [];
            }
            $response->setData($page);
        } catch (\Exception $exception) {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $response;
    }

    /**
     * @Route(
     *      "/page/{parentPageId}",
     *      requirements={"parentPageId" = "\d+"},
     *      name="icap_website_page_post",
     *      options={"expose"=true}
     * )
     * @ParamConverter("website", class="IcapWebsiteBundle:Website", options={"id" = "websiteId"})
     * @Method({"POST"})
     */
    public function postAction(Request $request, Website $website, $parentPageId)
    {
        $response = new JsonResponse();
        $user = $this->getLoggedUser();
        if ($user !== null) {
            try {
                $this->checkAccess('ADMINISTRATE', $website);
                $pageManager = $this->getWebsitePageManager();
                $parentPage = $pageManager->getPages($website, $parentPageId, true, false)[0];
                $newPage = $pageManager->createEmptyPage($website, $parentPage);
                $newPageJson = $pageManager->processForm($website, $newPage, $request->request->all(), 'POST');
                $response->setData($newPageJson);
            } catch (\Exception $exception) {
                $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            $response->setStatusCode(Response::HTTP_NETWORK_AUTHENTICATION_REQUIRED);
        }

        return $response;
    }

    /**
     * @Route(
     *      "/page/{pageId}",
     *      requirements={"pageId" = "\d+"},
     *      name="icap_website_page_put",
     *      options={"expose"=true}
     * )
     * @ParamConverter("website", class="IcapWebsiteBundle:Website", options={"id" = "websiteId"})
     * @Method({"PUT"})
     */
    public function putAction(Request $request, Website $website, $pageId)
    {
        $response = new JsonResponse();
        $user = $this->getLoggedUser();
        if ($user !== null) {
            try {
                $this->checkAccess('ADMINISTRATE', $website);
                $pageManager = $this->getWebsitePageManager();
                $page = $pageManager->getPages($website, $pageId, true, false)[0];
                $pageJson = $pageManager->processForm($website, $page, $request->request->all(), 'PUT');
                $response->setData($pageJson);
            } catch (\Exception $exception) {
                $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            $response->setStatusCode(Response::HTTP_NETWORK_AUTHENTICATION_REQUIRED);
        }

        return $response;
    }

    /**
     * @Route(
     *      "/page/{pageId}/setHomepage",
     *      requirements={"pageId" = "\d+"},
     *      name="icap_website_page_set_homepage",
     *      options={"expose"=true}
     * )
     * @ParamConverter("website", class="IcapWebsiteBundle:Website", options={"id" = "websiteId"})
     * @Method({"PUT"})
     */
    public function setHomepageAction(Website $website, $pageId)
    {
        $response = new JsonResponse();
        $user = $this->getLoggedUser();
        if ($user !== null) {
            try {
                $this->checkAccess('ADMINISTRATE', $website);
                $pageManager = $this->getWebsitePageManager();
                $page = $pageManager->getPages($website, $pageId, true, false)[0];
                $pageManager->changeHomepage($website, $page);
                $response->setStatusCode(Response::HTTP_OK);
            } catch (\Exception $exception) {
                $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            $response->setStatusCode(Response::HTTP_NETWORK_AUTHENTICATION_REQUIRED);
        }

        return $response;
    }

    /**
     * @Route(
     *      "/page/{pageId}/{newParentId}/{previousSiblingId}",
     *      requirements={"pageId" = "\d+", "newParentId" = "\d+", "previousSiblingId" = "\d+"},
     *      defaults={"previousSiblingId" = 0},
     *      name="icap_website_page_move",
     *      options={"expose"=true}
     * )
     * @ParamConverter("website", class="IcapWebsiteBundle:Website", options={"id" = "websiteId"})
     * @Method({"PUT"})
     */
    public function moveAction(Website $website, $pageId, $newParentId, $previousSiblingId)
    {
        $response = new JsonResponse();
        $user = $this->getLoggedUser();
        if ($user !== null) {
            $this->checkAccess('ADMINISTRATE', $website);
            $pageManager = $this->getWebsitePageManager();
            try {
                $pageManager->handleMovePage($website, [
                        'pageId' => $pageId,
                        'newParentId' => $newParentId,
                        'previousSiblingId' => $previousSiblingId,
                    ]
                );
            } catch (\Exception $exception) {
                $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            $response->setStatusCode(Response::HTTP_NETWORK_AUTHENTICATION_REQUIRED);
        }

        return $response;
    }

    /**
     * @Route(
     *      "/page/{pageId}",
     *      requirements={"pageId" = "\d+"},
     *      name="icap_website_page_delete",
     *      options={"expose"=true}
     * )
     * @ParamConverter("website", class="IcapWebsiteBundle:Website", options={"id" = "websiteId"})
     * @Method({"DELETE"})
     */
    public function deleteAction(Request $request, Website $website, $pageId)
    {
        $response = new JsonResponse();
        $user = $this->getLoggedUser();
        if ($user !== null) {
            $this->checkAccess('ADMINISTRATE', $website);
            $pageManager = $this->getWebsitePageManager();
            $page = $pageManager->getPages($website, $pageId, true, false)[0];
            try {
                $pageManager->deletePage($page);
            } catch (\Exception $exception) {
                $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            $response->setStatusCode(Response::HTTP_NETWORK_AUTHENTICATION_REQUIRED);
        }

        return $response;
    }
}
