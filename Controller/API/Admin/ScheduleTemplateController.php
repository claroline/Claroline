<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\API\Admin;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use Claroline\CoreBundle\Manager\ScheduleTemplateManager;

/**
 * @NamePrefix("api_")
 */
class ScheduleTemplateController extends FOSRestController
{
    /**
     * @DI\InjectParams({
     *     "formFactory"             = @DI\Inject("form.factory"),
     *     "scheduleTemplateManager" = @DI\Inject("claroline.manager.schedule_template_manager"),
     *     "request"                 = @DI\Inject("request"),
     *     "om"                      = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        FormFactory             $formFactory,
        ScheduleTemplateManager $scheduleTemplateManager,
        ObjectManager           $om,
        Request                 $request
    )
    {
        $this->formFactory             = $formFactory;
        $this->scheduleTemplateManager = $scheduleTemplateManager;
        $this->om                      = $om;
        $this->request                 = $request;
    }
}
