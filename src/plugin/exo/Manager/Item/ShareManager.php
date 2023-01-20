<?php

namespace UJM\ExoBundle\Manager\Item;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Entity\Item\Shared;
use UJM\ExoBundle\Repository\ItemRepository;

class ShareManager
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
    }

    public function canEdit(Item $question, User $user): bool
    {
        $shared = $this->om->getRepository(Shared::class)
            ->findOneBy([
                'question' => $question,
                'user' => $user,
            ]);

        if ($shared && $shared->hasAdminRights()) {
            // User has admin rights so he can delete question
            return true;
        }

        return false;
    }

    /**
     * Shares a list of question to users.
     *
     * @throws InvalidDataException
     */
    public function share(array $shareRequest): void
    {
        $errors = $this->validateShareRequest($shareRequest);
        if (count($errors) > 0) {
            throw new InvalidDataException('Share request is not valid', $errors);
        }

        $adminRights = isset($shareRequest['adminRights']) && $shareRequest['adminRights'];

        /** @var ItemRepository $questionRepo */
        $questionRepo = $this->om->getRepository(Item::class);
        // Loaded questions (we load it to be sure it exist)
        $questions = $questionRepo->findByUuids($shareRequest['questions']);

        // Loaded users (we load it to be sure it exist)
        $users = $this->om->findByIds(User::class, $shareRequest['users']);

        // Share each question with each user
        foreach ($questions as $question) {
            if ($this->authorization->isGranted('edit', $question)) {
                $sharedWith = $this->om
                    ->getRepository(Shared::class)
                    ->findBy(['question' => $question]);

                foreach ($users as $user) {
                    $shared = $this->getSharedForUser($user, $sharedWith);
                    if (empty($shared)) {
                        $shared = new Shared();
                        $shared->setQuestion($question);
                        $shared->setUser($user);
                    }

                    $shared->setAdminRights($adminRights);
                    $this->om->persist($shared);
                }
            }
        }

        $this->om->flush();
    }

    /**
     * Gets an existing share link for a user in the share list of the question.
     *
     * @param Shared[] $shared
     */
    private function getSharedForUser(User $user, array $shared): ?Shared
    {
        $userLink = null;
        foreach ($shared as $shareLink) {
            if ($shareLink->getUser() === $user) {
                $userLink = $shareLink;
                break;
            }
        }

        return $userLink;
    }

    /**
     * Validates a share request.
     */
    private function validateShareRequest(array $shareRequest): array
    {
        $errors = [];

        if (empty($shareRequest['questions']) || !is_array($shareRequest['questions'])) {
            $errors[] = [
                'path' => '/questions',
                'message' => 'should be a list of question ids',
            ];
        }

        if (empty($shareRequest['users']) || !is_array($shareRequest['users'])) {
            $errors[] = [
                'path' => '/users',
                'message' => 'should be a list of user ids',
            ];
        }

        if (!is_bool($shareRequest['adminRights'])) {
            $errors[] = [
                'path' => '/adminRights',
                'message' => 'should be boolean',
            ];
        }

        return $errors;
    }
}
