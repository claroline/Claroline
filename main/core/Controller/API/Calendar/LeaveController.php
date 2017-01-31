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

use Claroline\CoreBundle\Form\Calendar\LeaveType;
use Claroline\CoreBundle\Manager\ApiManager;
use Claroline\CoreBundle\Manager\Calendar\LeaveManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;

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
    ) {
        $this->formFactory = $formFactory;
        $this->leaveManager = $leaveManager;
        $this->om = $om;
        $this->request = $request;
        $this->apiManager = $apiManager;
    }

    /**
     * @View(serializerGroups={"api"})
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
     */
    public function getLeavesAction()
    {
        return $this->leaveManager->getAll();
    }
}
