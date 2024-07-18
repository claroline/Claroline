<?php

namespace Claroline\AppBundle\Controller;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Context\AdministrationContext;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @deprecated
 */
abstract class AbstractSecurityController
{
    use PermissionCheckerTrait;

    private ObjectManager $om;

    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorization): void
    {
        $this->authorization = $authorization;
    }

    public function setObjectManager(ObjectManager $om): void
    {
        $this->om = $om;
    }

    protected function canOpenAdminTool(string $toolName): void
    {
        $tool = $this->om->getRepository(OrderedTool::class)
            ->findOneBy(['name' => $toolName, 'contextName' => AdministrationContext::getName()]);

        if (!$tool) {
            throw new \LogicException("Annotation error: cannot found admin tool '{$toolName}'");
        }

        $granted = $this->authorization->isGranted('OPEN', $tool);

        if (!$granted) {
            throw new AccessDeniedException(sprintf('%s cannot be opened', $toolName));
        }
    }
}
