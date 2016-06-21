<?php

namespace Innova\CollecticielBundle\Manager;

use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\AgendaBundle\Entity\Event;
use Innova\CollecticielBundle\Entity\Dropzone;
use Innova\CollecticielBundle\Entity\Drop;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("innova.manager.dropzone_manager")
 */
class DropzoneManager
{
    private $container;
    private $maskManager;
    private $em;
    private $resourceNodeRepo;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container"),
     *      "maskManager" = @DI\Inject("claroline.manager.mask_manager"),
     *      "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct($container, MaskManager $maskManager, $em)
    {
        $this->container = $container;
        $this->maskManager = $maskManager;
        $this->em = $em;
        $this->resourceNodeRepo = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceNode');
    }

    /**
     *  Getting the user that have the 'open' rights.
     *  Excluded the admin profil.
     *
     * @param \Innova\CollecticielBundle\Entity\Dropzone $dropzone
     *
     * @return array UserIds.
     */
    public function getDropzoneUsersIds(Dropzone $dropzone)
    {

//        $this->container->get('innova.manager.dropzone_voter')->isAllowToEdit($dropzone);

        //getting the ressource node
        $ressourceNode = $dropzone->getResourceNode();
        // getting the rights of the ressource node
        $rights = $ressourceNode->getRights();

        // will contain the 'authorized to open' user's ids.
        $userIds = array();
        $test = array();
        // searching for roles with the 'open' right
        foreach ($rights as $ressourceRight) {
            $role = $ressourceRight->getRole(); //  current role
            $mask = $ressourceRight->getMask(); // current mask

            // getting decoded rights.
            $decodedRights = $this->maskManager->decodeMask($mask, $ressourceNode->getResourceType());
            // if this role is allowed to open and this role is not an Admin role
            if (array_key_exists('open', $decodedRights) && $decodedRights['open'] == true
                && $role->getName() != 'ROLE_ADMIN'
            ) {
                // the role has the 'open' right
                array_push($test, $role->getName());
                $users = $role->getUsers();
                foreach ($users as $user) {
                    array_push($userIds, $user->getId());
                }
            }
        }
        $userIds = array_unique($userIds);

        return $userIds;
    }

    /**
     *  ShortCut to getDropzoneProgress
     *  allow to get progress without drop and nbCorrection parameter.
     *  only need user and dropzone.
     *
     * STATES  FOR NORMAL ARE :
     *  0 : not started
     *  1 : Waiting for drop
     *  2 : Drop received, waiting for correction
     *  3 : Copy corrected , Evaluation end.
     *
     * STATES FOR PEERREVIEW (for X correction ):
     *  0 : notStarted
     *  1 : Waiting for drop
     *  2 : Drop received
     *  3 : Correction 1/x
     *  4 : Correction 2/X
     *  5 : Correction X/X
     *  6 : Waiting for correction
     *  7 : copy corrected, Evaluation End.     *
     *
     * WARNING : if a drop has the 'unlockedDrop' property, it will make the drop being at the last state.
     *
     *  currentstate : index of the current state  in the stateArray
     *  percent : rounded progress in percent
     *  nbCorrection : corrections made by the user in this evaluation.
     *   *
     *
     * @param Dropzone dropzone
     * @param Drop drop
     *
     * @return array (states, currentState,percent,nbCorrection)
     **/
    public function getDropzoneProgressByUser($dropzone, $user)
    {
        $drop = $this->em
            ->getRepository('InnovaCollecticielBundle:Drop')
            ->findOneBy(array('dropzone' => $dropzone, 'user' => $user));
        $nbCorrections = $this->em
            ->getRepository('InnovaCollecticielBundle:Correction')
            ->countFinished($dropzone, $user);

        return $this->getDrozponeProgress($dropzone, $drop, $nbCorrections);
    }

    /**
     * STATES  FOR NORMAL ARE :
     *  0 : not started
     *  1 : Waiting for drop
     *  2 : Drop received, waiting for correction
     *  3 : Copy corrected , Evaluation end.
     *
     * STATES FOR PEERREVIEW (for X correction ):
     *  0 : notStarted
     *  1 : Waiting for drop
     *  2 : Drop received
     *  3 : Correction 1/x
     *  4 : Correction 2/X
     *  5 : Correction X/X
     *  6 : Waiting for correction
     *  7 : copy corrected, Evaluation End.
     *
     * WARNING : if a drop has the 'unlockedDrop' property, it will make the drop being at the last state.
     *  currentstate : index of the current state  in the stateArray
     *  percent : rounded progress in percent
     *  nbCorrection : corrections made by the user in this evaluation.
     *
     * @param \Innova\CollecticielBundle\Entity\Dropzone                                     $dropzone
     * @param \Innova\CollecticielBundle\Entity\Drop|\Innova\CollecticielBundle\Manager\Drop $drop
     * @param int                                                                            $nbCorrection number of correction the user did.
     *
     * @return array (states, currentState,percent,nbCorrection)
     */
    public function getDrozponeProgress(Dropzone $dropzone, Drop $drop = null, $nbCorrection = 0)
    {
        $begin_states = array('Evaluation not started', 'awaiting for drop', 'drop provided');
        $end_states = array('waiting for correction', 'corrected copy');
        $states = array();

        $states = array_merge($states, $begin_states);
        $expectedCorrections = $dropzone->getExpectedTotalCorrection();

        $currentState = 0;
        // set the states of the dropzone.

        if ($dropzone->getPeerReview()) {
            // case of peerReview

            /*
             * --------------------- SPECIAL CASE  BEGIN ------------------------------
             *  particular case where the peerReview end whereas the user didnt
             *  had time to make all the expected corrections.
             *  so we make a hack to allow them to see their note and simulate that
             *  they did the expected corrections.
            */
            $allow_user_to_not_have_expected_corrections = $this->isPeerReviewEndedOrManualStateFinished($dropzone, $nbCorrection);
            /* --------------------- SPECIAL CASE  END ------------------------------*/

            if (!$allow_user_to_not_have_expected_corrections && $drop != null && !$drop->isUnlockedDrop()) {
                for ($i = 0; $i < $expectedCorrections; ++$i) {
                    array_push($states, 'correction n°%nb_correction%/%expected_correction%');
                }
            }
            $states = array_merge($states, $end_states);

            // getting the current state.

            // if no drop, state is 0 as default.
            if (!empty($drop)) {
                ++$currentState;

                if ($drop->getFinished()) {
                    ++$currentState;
                }
                // @TODO manage invalidated corrections.
                //  update the state with the correction number.
                if ($nbCorrection > $expectedCorrections) {
                    $nbCorrection = $expectedCorrections;
                }

                if (!$allow_user_to_not_have_expected_corrections && !$drop->isUnlockedDrop()) {
                    $currentState += $nbCorrection;
                    if ($nbCorrection >= $expectedCorrections) {
                        ++$currentState;
                    }
                } else {
                    ++$currentState;
                }

                if ($drop->countFinishedCorrections() >= $expectedCorrections) {
                    ++$currentState;

                    if ($allow_user_to_not_have_expected_corrections) {
                        ++$currentState;
                    }
                } elseif ($drop->isUnlockedDrop()) {
                    ++$currentState;
                }

                // admin case ( can correct more than expected )
                if ($currentState >= count($states)) {
                    $currentState = count($states) - 1;
                }
            }
        } else {
            // case of normal correction.
            $states = array_merge($states, $end_states);
            // if no drop, state is 0 as default.
            if (!empty($drop)) {
                ++$currentState;

                if ($drop->getFinished()) {
                    $currentState += 2;
                }
                if ($drop->countFinishedCorrections() >= $expectedCorrections) {
                    ++$currentState;
                }
            }
        }

        $percent = round(($currentState * 100) / (count($states) - 1));

        return array('states' => $states, 'currentState' => $currentState, 'percent' => $percent, 'nbCorrection' => $nbCorrection);
    }

    /**
     *  Test to detect the special case where the peerReview end whereas user didnt had time to make the expected
     *  number of correction.
     *
     * @param Dropzone $dropzone
     * @param $nbCorrection
     *
     * @return bool
     */
    public function isPeerReviewEndedOrManualStateFinished(Dropzone $dropzone, $nbCorrection)
    {
        $specialCase = false;
        if (($dropzone->getManualPlanning() && $dropzone->getManualState() == Dropzone::MANUAL_STATE_FINISHED) ||
            (!$dropzone->getManualPlanning() && $dropzone->getTimeRemaining($dropzone->getEndReview()) <= 0)
        ) {
            if ($dropzone->getExpectedTotalCorrection() > $nbCorrection) {
                $specialCase = true;
            }
        }

        return $specialCase;
    }

    /**
     * To know if a collecticiel is open or not (close) InnovaERV.
     */
    public function collecticielOpenOrNot(Dropzone $dropzone)
    {
        $open = false;

        /*
         * "Manuellement" InnovaERV
         */
        if ($dropzone->getManualPlanning() == 1 && $dropzone->getManualState() == Dropzone::MANUAL_STATE_ALLOW_DROP) {
            $open = true;
        }

        /*
         * "Par dates" InnovaERV
         */
        if ($dropzone->getManualPlanning() == 0) {
            $now = new \DateTime();
            if (($dropzone->getStartAllowDrop()->getTimestamp() < $now->getTimestamp())
            && ($now->getTimestamp() < $dropzone->getEndAllowDrop()->getTimestamp())) {
                $open = true;
            }
        }

        return $open;
    }

    /**
     * if the dropzone option 'autocloseOpenDropsWhenTimeIsUp' is activated, and evalution allowToDrop time is over,
     *  this will close all drop not closed yet.
     *
     * @param Dropzone $dropzone
     * @param bool     $force
     */
    public function closeDropzoneOpenedDrops(Dropzone $dropzone, $force = false)
    {
        if ($force || $this->isDropzoneDropTimeIsUp($dropzone)) {
            $dropRepo = $this->em->getRepository('InnovaCollecticielBundle:Drop');
            $dropRepo->closeUnTerminatedDropsByDropzone($dropzone->getId());
            $dropzone->setAutoCloseState(Dropzone::AUTO_CLOSED_STATE_CLOSED);
        }
    }

    /**
     * Check if dropzone  options are ok in order to autoclose Drops.
     *
     * @param Dropzone $dropzone
     *
     * @return bool
     */
    private function isDropzoneDropTimeIsUp(Dropzone $dropzone)
    {
        $dropDatePassed = false;
        if ($dropzone->getAutoCloseOpenedDropsWhenTimeIsUp() && $dropzone->getManualPlanning() == false) {
            $now = new \DateTime();
            $dropDatePassed = $now->getTimestamp() > $dropzone->getEndAllowDrop()->getTimeStamp();
        }

        return $dropDatePassed;
    }

    public function recalculateScoreByDropzone(Dropzone $dropzone)
    {
        $this->container->get('innova.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->container->get('innova.manager.dropzone_voter')->isAllowToEdit($dropzone);

        if (!$dropzone->getPeerReview()) {
            throw new AccessDeniedException();
        }
        // getting the repository
        $CorrectionRepo = $this->em->getRepository('InnovaCollecticielBundle:Correction');
        // getting all the drop corrections
        $corrections = $CorrectionRepo->findBy(['dropzone' => $dropzone->getId()]);

        $this->container->get('innova.manager.correction_manager')->recalculateScoreForCorrections($dropzone, $corrections);
    }

    /**
     * Handle events when modifying dropzone
     * Delete useless events (if manualPlanning) & update/create (if datePlanning).
     *
     * @param Dropzone $dropzone
     *
     * @return bool
     */
    public function handleEvents(Dropzone $dropzone, User $user)
    {
        $agendaManager = $this->container->get('claroline.manager.agenda_manager');
        $workspace = $dropzone->getResourceNode()->getWorkspace();

        if ($dropzone->getManualPlanning()) {
            if ($dropzone->getEventDrop() != null) {
                $event = $dropzone->getEventDrop();
                $agendaManager->deleteEvent($event);
                $dropzone->setEventDrop(null);
            }

            if ($dropzone->getEventCorrection() != null) {
                $event = $dropzone->getEventCorrection();
                $agendaManager->deleteEvent($event);
                $dropzone->setEventCorrection(null);
            }
        } elseif ($dropzone->getStartAllowDrop() != null && $dropzone->getEndAllowDrop() != null) {
            if ($dropzone->getEventDrop() != null) {
                // update event
                $eventDrop = $dropzone->getEventDrop();
                $eventDrop->setStart($dropzone->getStartAllowDrop());
                $eventDrop->setEnd($dropzone->getEndAllowDrop());
                $agendaManager->updateEvent($eventDrop);
            } else {
                // create event
                $eventDrop = $this->createAgendaEventDrop($user, $dropzone);
                // event creation + link to workspace
                $agendaManager->addEvent($eventDrop, $workspace);
                // link btween the event and the dropzone
                $dropzone->setEventDrop($eventDrop);
            }
        }

        return $dropzone;
    }

    /**
     * Return allowed document types for a Dropzone.
     */
    public function getAllowedTypes(Dropzone $dropzone)
    {
        $allowedTypes = array();
        if ($dropzone->getAllowWorkspaceResource()) {
            $allowedTypes[] = 'resource';
        }
        if ($dropzone->getAllowUpload()) {
            $allowedTypes[] = 'file';
        }
        if ($dropzone->getAllowUrl()) {
            $allowedTypes[] = 'url';
        }
        if ($dropzone->getAllowRichText()) {
            $allowedTypes[] = 'text';
        }

        return $allowedTypes;
    }

    private function createAgendaEventDrop(User $user, Dropzone $dropzone)
    {
        $translator = $this->container->get('translator');
        $em = $this->container->get('doctrine')->getEntityManager();

        $event = new Event();
        $event->setStart($dropzone->getStartAllowDrop());
        $event->setEnd($dropzone->getEndAllowDrop());
        $event->setUser($user);

        $dropzoneName = $dropzone->getResourceNode()->getName();
        $title = $translator->trans('Deposit phase of the %dropzonename% evaluation', array('%dropzonename%' => $dropzoneName), 'innova_collecticiel');
        $desc = $translator->trans('Evaluation %dropzonename% opening', array('%dropzonename%' => $dropzoneName), 'innova_collecticiel');

        $event->setTitle($title);
        $event->setDescription($desc);

        $em->persist($event);
        $em->flush();

        return $event;
    }

    /**
     * Update published column for a Dropzone.
     *
     * @param  ResourceId (id)
     * @param  Published (boolean)
     *
     * @return resource
     */
    public function updatePublished($resourceId, $published)
    {
        $resourceNodes = $this->resourceNodeRepo->findBy(array('id' => $resourceId));

        foreach ($resourceNodes as $resourceNode) {
            $resourceNode->setPublished($published);
            $this->em->persist($resourceNode);
        }
        $this->em->flush();

        return $resourceNodes;
    }
}
