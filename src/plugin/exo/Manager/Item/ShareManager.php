<?php

namespace UJM\ExoBundle\Manager\Item;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Entity\Item\Shared;
use UJM\ExoBundle\Repository\ItemRepository;

class ShareManager
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var ItemManager
     */
    private $itemManager;

    public function __construct(
        ObjectManager $om,
        ItemManager $itemManager)
    {
        $this->om = $om;
        $this->itemManager = $itemManager;
    }

    /**
     * Shares a list of question to users.
     *
     * @throws InvalidDataException
     */
    public function share(array $shareRequest, User $user)
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
            if ($this->itemManager->canEdit($question, $user)) {
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
     *
     * @return Shared
     */
    private function getSharedForUser(User $user, array $shared)
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
     *
     * @return array
     */
    private function validateShareRequest(array $shareRequest)
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

    /**
     * Find all content for a given user and the replace him by another.
     *
     * @return int
     */
    public function replaceUser(User $from, User $to)
    {
        $shareds = $this->om->getRepository(Shared::class)->findByUser($from);

        if (count($shareds) > 0) {
            foreach ($shareds as $shared) {
                $shared->setUser($to);
            }

            $this->om->flush();
        }

        return count($shareds);
    }
}
