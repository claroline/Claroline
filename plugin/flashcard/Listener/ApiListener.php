<?php

namespace Claroline\FlashCardBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Claroline\FlashCardBundle\Manager\CardLearningManager;
use Claroline\FlashCardBundle\Manager\CardLogManager;
use Claroline\FlashCardBundle\Manager\SessionManager;
use Claroline\FlashCardBundle\Manager\UserPreferenceManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /** @var SessionManager */
    private $sessionManager;

    /** @var CardLearningManager */
    private $cardLearningManager;

    /** @var UserPreferenceManager */
    private $userPreferenceManager;

    /** @var CardLogManager */
    private $cardLogManager;

    /**
     * @DI\InjectParams({
     *     "sessionManager"        = @DI\Inject("claroline.flashcard.session_manager"),
     *     "cardLearningManager"   = @DI\Inject("claroline.flashcard.card_learning_manager"),
     *     "userPreferenceManager" = @DI\Inject("claroline.flashcard.user_preference_manager"),
     *     "cardLogManager"        = @DI\Inject("claroline.flashcard.card_log_manager")
     * })
     *
     * @param SessionManager        $sessionManager
     * @param CardLearningManager   $cardLearningManager
     * @param UserPreferenceManager $userPreferenceManager
     * @param CardLogManager        $cardLogManager
     */
    public function __construct(
        SessionManager $sessionManager,
        CardLearningManager $cardLearningManager,
        UserPreferenceManager $userPreferenceManager,
        CardLogManager $cardLogManager
    ) {
        $this->sessionManager = $sessionManager;
        $this->cardLearningManager = $cardLearningManager;
        $this->userPreferenceManager = $userPreferenceManager;
        $this->cardLogManager = $cardLogManager;
    }

    /**
     * @DI\Observe("merge_users")
     *
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        // Replace user of Session nodes
        $sessionCount = $this->sessionManager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineFlashCardBundle] updated Session count: $sessionCount");

        // Replace user of CardLearning nodes
        $cardLearningCount = $this->cardLearningManager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineFlashCardBundle] updated CardLearning count: $cardLearningCount");

        // Replace user of UserPreference nodes
        $userPreferenceCount = $this->userPreferenceManager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineFlashCardBundle] updated UserPreference count: $userPreferenceCount");

        // Replace user of CardLog nodes
        $cardLogCount = $this->cardLogManager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineFlashCardBundle] updated CardLog count: $cardLogCount");
    }
}
