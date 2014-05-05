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

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Translation\Translator;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

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
            $target = $attributes[0] === 'OPEN' ? null : $attributes[0];
            $accesses = $this->wm->getAccesses($token, array($object), $target);

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
