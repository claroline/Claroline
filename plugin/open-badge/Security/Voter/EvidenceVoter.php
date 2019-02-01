<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Security\Voter;

use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Claroline\OpenBadgeBundle\Entity\Evidence;
use Claroline\OpenBadgeBundle\Manager\OpenBadgeManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class EvidenceVoter extends AbstractVoter
{
    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.manager.open_badge_manager")
     * })
     */
    public function setManager(OpenBadgeManager $manager)
    {
        $this->manager = $manager;
    }

    //ready to be overrided
    public function checkCreation(TokenInterface $token, $object)
    {
        return $this->manager->isAllowedBadgeManagement($token, $object->getBadge());
    }

    //ready to be overrided
    public function checkDelete(TokenInterface $token, $object)
    {
        return $this->isAllowedBadgeManagement($token, $object->getBadge());
    }

    //ready to be overrided
    public function checkEdit(TokenInterface $token, $object)
    {
        return $this->isAllowedBadgeManagement($token, $object->getBadge());
    }

    public function getClass()
    {
        return Evidence::class;
    }
}
