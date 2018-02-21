<?php

namespace UJM\ExoBundle\Manager\Item;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\UserRepository;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Item\Shared;
use UJM\ExoBundle\Library\Validator\ValidationException;
use UJM\ExoBundle\Repository\ItemRepository;

/**
 * @DI\Service("ujm_exo.manager.share")
 */
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

    /**
     * ShareManager constructor.
     *
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager"),
     *     "itemManager" = @DI\Inject("ujm_exo.manager.item")
     * })
     *
     * @param ObjectManager $om
     * @param ItemManager   $itemManager
     */
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
     * @param \stdClass $shareRequest - an object containing the questions and users to link
     * @param User      $user
     *
     * @throws ValidationException
     */
    public function share(\stdClass $shareRequest, User $user)
    {
        $errors = $this->validateShareRequest($shareRequest);
        if (count($errors) > 0) {
            throw new ValidationException('Share request is not valid', $errors);
        }

        $adminRights = isset($shareRequest->adminRights) && $shareRequest->adminRights;

        /** @var ItemRepository $questionRepo */
        $questionRepo = $this->om->getRepository('UJMExoBundle:Item\Item');
        // Loaded questions (we load it to be sure it exist)
        $questions = $questionRepo->findByUuids($shareRequest->questions);

        /** @var UserRepository $userRepo */
        $userRepo = $this->om->getRepository('ClarolineCoreBundle:User');
        // Loaded users (we load it to be sure it exist)
        $users = $userRepo->findByIds($shareRequest->users);

        // Share each question with each user
        foreach ($questions as $question) {
            if ($this->itemManager->canEdit($question, $user)) {
                $sharedWith = $this->om
                    ->getRepository('UJMExoBundle:Item\Shared')
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
     * @param User     $user
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
     * @param \stdClass $shareRequest
     *
     * @return array
     */
    private function validateShareRequest(\stdClass $shareRequest)
    {
        $errors = [];

        if (empty($shareRequest->questions) || !is_array($shareRequest->questions)) {
            $errors[] = [
                'path' => '/questions',
                'message' => 'should be a list of question ids',
            ];
        }

        if (empty($shareRequest->users) || !is_array($shareRequest->users)) {
            $errors[] = [
                'path' => '/users',
                'message' => 'should be a list of user ids',
            ];
        }

        if (!is_bool($shareRequest->adminRights)) {
            $errors[] = [
                'path' => '/adminRights',
                'message' => 'should be boolean',
            ];
        }

        return $errors;
    }
}
