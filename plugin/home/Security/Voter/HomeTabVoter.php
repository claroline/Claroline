<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\HomeBundle\Security\Voter;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\Tool\OrderedToolRepository;
use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Claroline\HomeBundle\Entity\HomeTab;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class HomeTabVoter extends AbstractVoter
{
    /** @var ObjectManager */
    private $om;

    /** @var OrderedToolRepository */
    private $orderedToolRepo;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;

        $this->orderedToolRepo = $this->om->getRepository(OrderedTool::class);
    }

    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        switch ($attributes[0]) {
            case self::EDIT:   return $this->check($token, $object);
            case self::DELETE: return $this->check($token, $object);
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function check(TokenInterface $token, HomeTab $object)
    {
        $granted = false;
        switch ($object->getContext()) {
            case HomeTab::TYPE_HOME:
                $granted = $this->isAdmin($token) || $this->isGranted('ROLE_HOME_MANAGER');
                break;

            case HomeTab::TYPE_ADMIN:
                $granted = $this->hasAdminToolAccess($token, 'home');
                break;

            case HomeTab::TYPE_DESKTOP:
                $homeTool = $this->orderedToolRepo->findOneByNameAndDesktop('home');
                $isOwner = $object->getUser() && $token->getUser() instanceof User && $object->getUser()->getId() === $token->getUser();

                $granted = $isOwner && $homeTool && $this->isGranted('EDIT', $homeTool);
                break;

            case HomeTab::TYPE_ADMIN_DESKTOP:
                $homeTool = $this->orderedToolRepo->findOneByNameAndDesktop('home');

                $granted = $homeTool && $this->isGranted('ADMINISTRATE', $homeTool);
                break;

            case HomeTab::TYPE_WORKSPACE:
                $granted = $object->getWorkspace() && $this->isGranted(self::EDIT, $object->getWorkspace());
                break;
        }

        if ($granted) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    public function getClass()
    {
        return HomeTab::class;
    }

    public function getSupportedActions()
    {
        return [self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE];
    }
}
