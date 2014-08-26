<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 7/8/14
 * Time: 10:46 AM
 */

namespace Icap\WebsiteBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Icap\WebsiteBundle\Entity\Website;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

        return array(
            '_resource' => $website,
            'workspace' => $website->getResourceNode()->getWorkspace(),
            'isAdmin' => $isAdmin,
            'user' => $user
        );
    }

} 