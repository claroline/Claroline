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

use Claroline\CoreBundle\Form\Calendar\ScheduleTemplateType;
use Claroline\CoreBundle\Manager\ApiManager;
use Claroline\CoreBundle\Manager\Calendar\ScheduleTemplateManager;
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
class ScheduleTemplateController extends FOSRestController
{
    /**
     * @DI\InjectParams({
     *     "formFactory"             = @DI\Inject("form.factory"),
     *     "scheduleTemplateManager" = @DI\Inject("claroline.manager.calendar.schedule_template_manager"),
     *     "request"                 = @DI\Inject("request"),
     *     "apiManager"              = @DI\Inject("claroline.manager.api_manager"),
     *     "om"                      = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        FormFactory             $formFactory,
        ScheduleTemplateManager $scheduleTemplateManager,
        ApiManager              $apiManager,
        ObjectManager           $om,
        Request                 $request
    ) {
        $this->formFactory = $formFactory;
        $this->scheduleTemplateManager = $scheduleTemplateManager;
        $this->om = $om;
        $this->request = $request;
        $this->apiManager = $apiManager;
    }

    /**
     * @View(serializerGroups={"api"})
     */
    public function getCreateScheduleTemplateFormAction()
    {
        $formType = new ScheduleTemplateType();
        $formType->enableApi();
        $form = $this->createForm($formType);

        return $this->apiManager->handleFormView(
            'ClarolineCoreBundle:API:Calendar\createScheduleTemplateForm.html.twig',
            $form
        );
    }

    /**
     * @View(serializerGroups={"api"})
     */
    public function getScheduleTemplatesAction()
    {
        return $this->scheduleTemplateManager->getAll();
    }
}
