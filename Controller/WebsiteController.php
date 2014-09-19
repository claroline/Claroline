<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 7/8/14
 * Time: 10:46 AM
 */

namespace Icap\WebsiteBundle\Controller;

use Icap\WebsiteBundle\Entity\Website;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;


class WebsiteController extends Controller{

    /**
     * @Route(
     *      "/{websiteId}",
     *      requirements={"websiteId" = "\d+"},
     *      name="icap_website_view"
     * )
     * @ParamConverter("website", class="IcapWebsiteBundle:Website", options={"id" = "websiteId"})
     * @Template()
     */
    public function viewAction(Website $website)
    {
        $this->checkAccess("OPEN", $website);
        $isAdmin = $this->isUserGranted("EDIT", $website);
        $user = $this->getLoggedUser();
        $pageManager = $this->getWebsitePageManager();
        $pages = $pageManager->getPageTree($website, $isAdmin, true, false);
        $website->setPages($pages);

        return array(
            '_resource' => $website,
            'workspace' => $website->getResourceNode()->getWorkspace(),
            'isAdmin' => $isAdmin,
            'user' => $user
        );
    }

} 