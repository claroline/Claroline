<?php

namespace Claroline\AppBundle\Controller;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

abstract class SecurityController
{
    use PermissionCheckerTrait;

    private $authorization;
    private $om;

    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    public function setObjectManager(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * @param string $toolName
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function canOpenAdminTool($toolName)
    {
        $tool = $this->om->getRepository('ClarolineCoreBundle:Tool\AdminTool')
            ->findOneBy(['name' => $toolName]);

        if (!$tool) {
            throw new \LogicException(
                "Annotation error: cannot found admin tool '{$toolName}'"
            );
        }

        $granted = $this->authorization->isGranted('OPEN', $tool);

        if (!$granted) {
            throw new AccessDeniedException(
                sprintf('%s cannot be opened', $toolName)
            );
        }
    }
}
