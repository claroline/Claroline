<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Controller\Model\HasOrganizationsTrait;
use Claroline\CoreBundle\Entity\Location;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/location", name="apiv2_location_")
 */
class LocationController extends AbstractCrudController
{
    use HasOrganizationsTrait;
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization
    ) {
        $this->authorization = $authorization;
    }

    public static function getName(): string
    {
        return 'location';
    }

    public static function getClass(): string
    {
        return Location::class;
    }
}
