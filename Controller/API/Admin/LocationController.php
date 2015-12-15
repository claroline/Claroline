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
use FOS\RestBundle\Controller\FOSRestController;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\LocationManager;
use Claroline\CoreBundle\Entity\Organization\Location;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

/**
 * @NamePrefix("api_")
 */
class LocationController extends FOSRestController
{
    /**
     * @DI\InjectParams({
     *     "formFactory"     = @DI\Inject("form.factory"),
     *     "locationManager" = @DI\Inject("claroline.manager.location_manager"),
     *     "request"         = @DI\Inject("request"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        FormFactory          $formFactory,
        LocationManager      $locationManager,
        ObjectManager        $om,
        Request              $request
    )
    {
        $this->formFactory     = $formFactory;
        $this->locationManager = $locationManager;
        $this->om              = $om;
        $this->request         = $request;
    }


    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Returns the location list",
     *     views = {"location"}
     * )
     */
    public function getLocationsAction()
    {
        return $this->locationManager->getByType(Location::TYPE_DEPARTMENT);
    }

    public function getCreateFormAction()
    {
        
    }
}
