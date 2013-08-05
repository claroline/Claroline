<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

class AbstractParametersController extends Controller
{
    protected function checkAccess(AbstractWorkspace $workspace)
    {
        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }
    }
}
