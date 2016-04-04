<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\API\Calendar;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use Claroline\CoreBundle\Manager\Calendar\LeaveManager;
use Claroline\CoreBundle\Manager\ApiManager;
use Claroline\CoreBundle\Form\Calendar\LeaveType;
use Claroline\CoreBundle\Entity\Calendar\Leave;
use Symfony\Component\Form\FormFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;

/**
 * @NamePrefix("api_")
 */
class LeaveController extends FOSRestController
{
    /**
     * @DI\InjectParams({
     *     "formFactory"  = @DI\Inject("form.factory"),
     *     "leaveManager" = @DI\Inject("claroline.manager.calendar.leave_manager"),
     *     "apiManager"      = @DI\Inject("claroline.manager.api_manager"),
     *     "request"      = @DI\Inject("request"),
     *     "om"           = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        FormFactory          $formFactory,
        LeaveManager         $leaveManager,
        ApiManager           $apiManager,
        ObjectManager        $om,
        Request              $request
    )
    {
        $this->formFactory     = $formFactory;
        $this->leaveManager    = $leaveManager;
        $this->om              = $om;
        $this->request         = $request;
        $this->apiManager      = $apiManager;
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Returns the leave creation form",
     *     views = {"leave"}
     * )
     */
    public function getCreateLeaveFormAction()
    {
        $formType = new LeaveType();
        $formType->enableApi();
        $form = $this->createForm($formType);

        return $this->apiManager->handleFormView(
            'ClarolineCoreBundle:API:Calendar\createLeaveForm.html.twig', 
            $form
        );
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Returns the leave list",
     *     views = {"leave"}
     * )
     */
    public function getLeavesAction()
    {
        return $this->leaveManager->getAll();
    }
}
