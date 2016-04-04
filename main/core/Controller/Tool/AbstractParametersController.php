<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

class AbstractParametersController extends Controller
{
    protected function checkAccess(Workspace $workspace)
    {
        if (!$this->get('security.authorization_checker')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }
    }
}
