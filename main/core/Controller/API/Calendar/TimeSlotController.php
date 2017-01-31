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

use Claroline\CoreBundle\Form\Calendar\TimeSlotType;
use Claroline\CoreBundle\Manager\ApiManager;
use Claroline\CoreBundle\Manager\Calendar\TimeSlotManager;
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
class TimeSlotController extends FOSRestController
{
    /**
     * @DI\InjectParams({
     *     "formFactory"     = @DI\Inject("form.factory"),
     *     "timeSlotManager" = @DI\Inject("claroline.manager.calendar.time_slot_manager"),
     *     "request"         = @DI\Inject("request"),
     *     "apiManager"      = @DI\Inject("claroline.manager.api_manager"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        FormFactory          $formFactory,
        TimeSlotManager      $timeSlotManager,
        ApiManager           $apiManager,
        ObjectManager        $om,
        Request              $request
    ) {
        $this->formFactory = $formFactory;
        $this->timeSlotManager = $timeSlotManager;
        $this->om = $om;
        $this->request = $request;
        $this->apiManager = $apiManager;
    }

    /**
     * @View(serializerGroups={"api"})
     */
    public function getCreateTimeSlotFormAction()
    {
        $formType = new TimeSlotType();
        $formType->enableApi();
        $form = $this->createForm($formType);

        return $this->apiManager->handleFormView(
            'ClarolineCoreBundle:API:Calendar\createTimeSlotForm.html.twig',
            $form
        );
    }

    /**
     * @View(serializerGroups={"api"})
     */
    public function getTimeSlotsAction()
    {
        return $this->timeSlotManager->getAll();
    }
}
