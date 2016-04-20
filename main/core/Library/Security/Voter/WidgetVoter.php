<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * This voter is involved in access decisions for WidgetInstances.
 *
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class WidgetVoter implements VoterInterface
{
    private $em;
    private $translator;
    private $wm;

    /**
     * @DI\InjectParams({
     *     "em"           = @DI\Inject("doctrine.orm.entity_manager"),
     *     "translator"   = @DI\Inject("translator"),
     *     "wm"           = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(
        EntityManager $em,
        TranslatorInterface $translator,
        WorkspaceManager $wm
    ) {
        $this->em = $em;
        $this->translator = $translator;
        $this->wm = $wm;
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if ($object instanceof WidgetInstance) {
            return $this->canUpdate($token, $object, $attributes);
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    private function canUpdate(TokenInterface $token, $object, $attributes)
    {
        $roles = $token->getRoles();

        foreach ($roles as $role) {
            $roleStrings[] = $role->getRole();
        }

        if ($object->isAdmin()) {
            $grantedAdminTools = $this->em->getRepository('ClarolineCoreBundle:Tool\AdminTool')->findByRoles($roles);
            $allowedTools = [];

            foreach ($grantedAdminTools as $grantedAdminTool) {
                $allowedTools[] = $grantedAdminTool->getName();
            }

            if (!in_array('home_tabs', $allowedTools)) {
                return VoterInterface::ACCESS_DENIED;
            }

            return VoterInterface::ACCESS_GRANTED;
        } else {
            if ($workspace = $object->getWorkspace()) {
                //if manager: always granted
                if (in_array('ROLE_WS_MANAGER_'.$workspace->getGuid(), $roleStrings)) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                //if we have access to the parameters, always granted aswell
                $tools = $this->em
                    ->getRepository('ClarolineCoreBundle:Tool\Tool')
                    ->findDisplayedByRolesAndWorkspace($roleStrings, $workspace);

                foreach ($tools as $tool) {
                    if ($tool->getName() === 'parameters') {
                        return VoterInterface::ACCESS_GRANTED;
                    }
                }

                //else we need to check the masks (c/c from WorkspaceVoter)
                $accesses = $this->wm->getAccesses($token, array($workspace), 'home', 'edit');

                return isset($accesses[$workspace->getId()]) && $accesses[$workspace->getId()] === true ?
                    VoterInterface::ACCESS_GRANTED :
                    VoterInterface::ACCESS_DENIED;
            }

            if ($user = $object->getUser()) {
                if ($user->getId() === $token->getUser()->getId()) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }

            return VoterInterface::ACCESS_DENIED;
        }
    }

    public function supportsAttribute($attribute)
    {
        return true;
    }

    public function supportsClass($class)
    {
        return true;
    }
}
