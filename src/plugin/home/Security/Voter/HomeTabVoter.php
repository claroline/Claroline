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
            case self::OPEN:
                return $this->check($token, $object);
            case self::EDIT:
            case self::DELETE:
                return $this->checkEdit($token, $object);
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    private function check(TokenInterface $token, HomeTab $object): int
    {
        $granted = false;
        switch ($object->getContext()) {
            case HomeTab::TYPE_HOME:
                $granted = true;
                break;

            case HomeTab::TYPE_ADMIN:
                $granted = $this->hasAdminToolAccess($token, 'home');
                break;

            case HomeTab::TYPE_DESKTOP:
                $homeTool = $this->orderedToolRepo->findOneByNameAndDesktop('home');
                $isOwner = $object->getUser() && $token->getUser() instanceof User && $object->getUser()->getId() === $token->getUser()->getId();

                $granted = $isOwner && $homeTool && $this->isGranted(self::OPEN, $homeTool);
                break;

            case HomeTab::TYPE_ADMIN_DESKTOP:
                $homeTool = $this->orderedToolRepo->findOneByNameAndDesktop('home');

                $granted = $homeTool && $this->isGranted(self::OPEN, $homeTool);
                break;

            case HomeTab::TYPE_WORKSPACE:
                $granted = $object->getWorkspace() && $this->isGranted(['home', self::OPEN], $object->getWorkspace());
                break;
        }

        if ($granted && $this->checkTabRestrictions($token, $object)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    private function checkEdit(TokenInterface $token, HomeTab $object): int
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
                $isOwner = $object->getUser() && $token->getUser() instanceof User && $object->getUser()->getId() === $token->getUser()->getId();

                $granted = $isOwner && $homeTool && $this->isGranted(self::EDIT, $homeTool);
                break;

            case HomeTab::TYPE_ADMIN_DESKTOP:
                $homeTool = $this->orderedToolRepo->findOneByNameAndDesktop('home');

                $granted = $homeTool && $this->isGranted(self::ADMINISTRATE, $homeTool);
                break;

            case HomeTab::TYPE_WORKSPACE:
                $granted = $object->getWorkspace() && $this->isGranted(['home', self::EDIT], $object->getWorkspace());
                break;
        }

        if ($granted) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    private function checkTabRestrictions(TokenInterface $token, HomeTab $object): bool
    {
        if (0 === $object->getRoles()->count()) {
            return true;
        }

        foreach ($object->getRoles() as $role) {
            if (in_array($role->getName(), $token->getRoleNames())) {
                return true;
            }
        }

        return false;
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
