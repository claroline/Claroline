<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AuthenticationBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AuthenticationBundle\Entity\IpUser;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/ip_user")
 */
class IpUserController extends AbstractCrudController
{
    public function getClass()
    {
        return IpUser::class;
    }

    public function getName()
    {
        return 'ip_user';
    }
}
