<?php

namespace Claroline\FlashCardBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\FlashCardBundle\Repository\UserPreferenceRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.flashcard.user_preference_manager")
 */
class UserPreferenceManager
{
    /** @var ObjectManager */
    private $om;

    /** @var UserPreferenceRepository */
    private $repo;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->repo = $om->getRepository('ClarolineFlashCardBundle:UserPreference');
    }

    /**
     * Find all content for a given user and the replace him by another.
     *
     * @param User $from
     * @param User $to
     *
     * @return int
     */
    public function replaceUser(User $from, User $to)
    {
        $userPreferences = $this->repo->findByUser($from);

        if (count($userPreferences) > 0) {
            foreach ($userPreferences as $userPreference) {
                $userPreference->setUser($to);
            }

            $this->om->flush();
        }

        return count($userPreferences);
    }
}
