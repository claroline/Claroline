<?php

namespace Claroline\OpenBadgeBundle\Library\Rules;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\User\UserRepository;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RoleRule extends AbstractRule
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var TranslatorInterface */
    private $translator;

    /** @var UserRepository */
    private $userRepo;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        ObjectManager $om
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;

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
