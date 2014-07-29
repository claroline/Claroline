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

use Claroline\CoreBundle\Entity\Event;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * This voter is involved in access decisions for facets
 *
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class EventVoter
{
    private $container;
    private $validAttributes;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Attributes can either be "delete" or "edit"
     *
     * @param TokenInterface $token
     * @param $object
     * @param array $attributes
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if ($object instanceof Event) {
            return $this->eventVote($object, $token, $attributes[0]);
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    private function eventVote(Event $event, TokenInterface $token, $action)
    {
        $security = $this->container->get('security.context');
        if (strtolower($action) === 'edit' || strtolower($action) === 'delete') {
            $isManager = $event->getWorkspace() ?
                $security->isGranted('ROLE_WS_MANAGER_' . $event->getWorkspace()->getGuid()):
                false;

            $isCreator = $security->getToken()->getUser()->getUsername() ===  $event->getUser()->getUsername();

            return ($isManager | $isCreator) ? VoterInterface::ACCESS_GRANTED: VoterInterface::ACCESS_DENIED;
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