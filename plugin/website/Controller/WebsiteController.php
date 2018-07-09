<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 7/8/14
 * Time: 10:46 AM.
 */

namespace Icap\WebsiteBundle\Controller;

use Icap\WebsiteBundle\Entity\Website;
use Icap\WebsiteBundle\Entity\WebsitePageTypeEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class WebsiteController extends Controller
{
    /**
     * @Route(
     *      "/{websiteId}",
     *      requirements={"websiteId" = "\d+"},
     *      defaults={"view" = false},
     *      name="icap_website_view",
     *      options={"expose"=true}
     * )
     * @Route(
     *      "/view/{websiteId}",
     *      requirements={"websiteId" = "\d+"},
     *      defaults={"view" = true},
     *      name="icap_website_force_view",
     *      options={"expose"=true}
     * )
     * @ParamConverter("website", class="IcapWebsiteBundle:Website", options={"id" = "websiteId"})
     */
    public function viewAction(Website $website, $view)
    {
        $this->checkAccess('OPEN', $website);
        if (!$view) {
            $isAdmin = $this->isUserGranted('ADMINISTRATE', $website);
        } else {
            $isAdmin = false;
        }
        $user = $this->getLoggedUser();
        $pageManager = $this->getWebsitePageManager();

        $viewArray = [
            '_resource' => $website,
            'workspace' => $website->getResourceNode()->getWorkspace(),
            'isAdmin' => $isAdmin,
            'user' => $user,
            'pageTypes' => [
                'blank' => WebsitePageTypeEnum::BLANK_PAGE,
                'resource' => WebsitePageTypeEnum::RESOURCE_PAGE,
                'url' => WebsitePageTypeEnum::URL_PAGE,
            ],
        ];
        if ($isAdmin) {
            $pages = $pageManager->getPageTree($website, $isAdmin, false);
            $website->setPages($pages);
            $resourceTypes = $this->get('claroline.manager.resource_manager')->getAllResourceTypes();
            $viewArray['resourceTypes'] = $resourceTypes;

            return $this->render('IcapWebsiteBundle:website:edit.html.twig', $viewArray);
        } else {
            $pages = $pageManager->getPageTree($website, $isAdmin, true);
            $website->setPages($pages);
            $currentPage = $website->getHomePage();
            if (null === $currentPage && !empty($pages) && !empty($pages[0]['children'])) {
                $currentPage = $pages[0]['children'][0];
                if (isset($currentPage) && null !== $currentPage) {
                    $currentPage = $pageManager->getPages($website, $currentPage['id'], $isAdmin, false)[0];
                }
            }

            $viewArray['currentPage'] = $currentPage;
        }

        return $this->render('IcapWebsiteBundle:website:view.html.twig', $viewArray);
    }
}
