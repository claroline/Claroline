<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FormaLibre\ReservationBundle\Controller\API;

use Claroline\CoreBundle\Annotations\ApiMeta;
use Claroline\CoreBundle\Controller\APINew\AbstractCrudController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

/**
 * @ApiMeta(class="FormaLibre\ReservationBundle\Entity\ResourceType")
 * @EXT\Route("/reservationresourcetype")
 */
class ResourceTypeController extends AbstractCrudController
{
    public function getName()
    {
        return 'reservation_resource_type';
    }
}
