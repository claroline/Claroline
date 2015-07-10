<?php

namespace FormaLibre\SupportBundle\Controller;

use FormaLibre\SupportBundle\Manager\SupportManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('formalibre_support_management_tool')")
 */
class AdminSupportController extends Controller
{
    private $supportManager;

    /**
     * @DI\InjectParams({
     *     "supportManager" = @DI\Inject("formalibre.manager.support_manager")
     * })
     */
    public function __construct(SupportManager $supportManager)
    {
        $this->supportManager = $supportManager;
    }

    /**
     * @EXT\Route(
     *     "/admin/support/management/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="formalibre_admin_support_management",
     *     defaults={"page"=1, "search"="", "max"=50, "orderedBy"="statusDate","order"="DESC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminSupportManagementAction(
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'statusDate',
        $order = 'DESC'
    )
    {
        $tickets = $this->supportManager->getAllTickets(
            $search,
            $orderedBy,
            $order,
            true,
            $page,
            $max
        );

        return array(
            '$search' => $search,
            'page' => $page,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order,
            'tickets' => $tickets
        );
    }
}
