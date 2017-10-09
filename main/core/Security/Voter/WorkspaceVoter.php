<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Security\Voter;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class WorkspaceVoter implements VoterInterface
{
    private $wm;

    /**
     * @DI\InjectParams({
     *     "wm" = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(WorkspaceManager $wm)
    {
        $this->wm = $wm;
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if ($object instanceof Workspace) {
            if ($object->getCreator() === $token->getUser()) {
                return VoterInterface::ACCESS_GRANTED;
            }

            //check the expiration date first
            $now = new \DateTime();
            if ($object->getEndDate()) {
                if ($now->getTimeStamp() > $object->getEndDate()->getTimeStamp()) {
                    return VoterInterface::ACCESS_DENIED;
                }
            }

            //then we do all the rest
            $toolName = isset($attributes[0]) && $attributes[0] !== 'OPEN' ?
                $attributes[0] :
                null;
            $action = isset($attributes[1]) ? strtolower($attributes[1]) : 'open';
            $accesses = $this->wm->getAccesses($token, [$object], $toolName, $action);

            return isset($accesses[$object->getId()]) && $accesses[$object->getId()] === true ?
                VoterInterface::ACCESS_GRANTED :
                VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
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
