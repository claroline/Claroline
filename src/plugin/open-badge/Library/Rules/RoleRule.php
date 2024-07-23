<?php

namespace Claroline\OpenBadgeBundle\Library\Rules;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Repository\UserRepository;
use Claroline\CoreBundle\Entity\User;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RoleRule extends AbstractRule
{
    private UserRepository $userRepo;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly TranslatorInterface $translator,
        ObjectManager $om
    ) {
        $this->userRepo = $om->getRepository(User::class);
    }

    public static function getType(): string
    {
        return Rule::IN_ROLE;
    }

    public function getQualifiedUsers(Rule $rule): iterable
    {
        return $this->userRepo->findByRoles([$rule->getRole()]);
    }

    public function getEvidenceMessage(): string
    {
        $now = new \DateTime();

        return $this->translator->trans('evidence_narrative_add_role', [
            '%doer%' => $this->tokenStorage->getToken()->getUser()->getUsername(),
            '%date%' => $now->format('Y-m-d H:i:s'),
        ], 'badge');
    }
}
