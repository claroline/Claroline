<?php

namespace Icap\DropzoneBundle\Manager;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\MaskManager;
use Icap\DropzoneBundle\Entity\Drop;
use Icap\DropzoneBundle\Entity\Dropzone;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap.manager.dropzone_manager")
 */
class DropzoneManager
{
    private $container;
    private $maskManager;
    private $em;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container"),
     *        "maskManager" = @DI\Inject("claroline.manager.mask_manager"),
     *      "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct($container, MaskManager $maskManager, $em)
    {
        $this->container = $container;
        $this->maskManager = $maskManager;
        $this->em = $em;
    }

    /**
     *  Getting the user that have the 'open' rights.
     *  Excluded the admin profil.
     *
     * @param \Icap\DropzoneBundle\Entity\Dropzone $dropzone
     *
     * @return array UserIds
     */
    public function getDropzoneUsersIds(Dropzone $dropzone)
    {
        $this->container->get('icap.manager.dropzone_voter')->isAllowToEdit($dropzone);

        //getting the ressource node
        $ressourceNode = $dropzone->getResourceNode();
        // getting the rights of the ressource node
        $rights = $ressourceNode->getRights();

        // will contain the 'authorized to open' user's ids.
        $userIds = [];
        $test = [];
        // searching for roles with the 'open' right
        foreach ($rights as $ressourceRight) {
            $role = $ressourceRight->getRole(); //  current role
            $mask = $ressourceRight->getMask(); // current mask

            // getting decoded rights.
            $decodedRights = $this->maskManager->decodeMask($mask, $ressourceNode->getResourceType());
            // if this role is allowed to open and this role is not an Admin role
            if (array_key_exists('open', $decodedRights) && $decodedRights['open'] === true
                && $role->getName() !== 'ROLE_ADMIN'
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
            ->getRepository('IcapDropzoneBundle:Drop')
            ->findOneBy(['dropzone' => $dropzone, 'user' => $user]);
        $nbCorrections = $this->em
            ->getRepository('IcapDropzoneBundle:Correction')
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
     * @param \Icap\DropzoneBundle\Entity\Dropzone                               $dropzone
     * @param \Icap\DropzoneBundle\Entity\Drop|\Icap\DropzoneBundle\Manager\Drop $drop
     * @param int                                                                $nbCorrection number of correction the user did
     *
     * @return array (states, currentState,percent,nbCorrection)
     */
    public function getDrozponeProgress(Dropzone $dropzone, Drop $drop = null, $nbCorrection = 0)
    {
        $begin_states = ['Evaluation not started', 'awaiting for drop', 'drop provided'];
        $end_states = ['waiting for correction', 'corrected copy'];
        $states = [];

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

            if (!$allow_user_to_not_have_expected_corrections && $drop !== null && !$drop->isUnlockedDrop()) {
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

        return ['states' => $states, 'currentState' => $currentState, 'percent' => $percent, 'nbCorrection' => $nbCorrection];
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
        if (($dropzone->getManualPlanning() && $dropzone->getManualState() === Dropzone::MANUAL_STATE_FINISHED) ||
            (!$dropzone->getManualPlanning() && $dropzone->getTimeRemaining($dropzone->getEndReview()) <= 0)
        ) {
            if ($dropzone->getExpectedTotalCorrection() > $nbCorrection) {
                $specialCase = true;
            }
        }

        return $specialCase;
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
            $dropRepo = $this->em->getRepository('IcapDropzoneBundle:Drop');
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
        if ($dropzone->getAutoCloseOpenedDropsWhenTimeIsUp() && $dropzone->getManualPlanning() === false) {
            $now = new \DateTime();
            $dropDatePassed = $now->getTimestamp() > $dropzone->getEndAllowDrop()->getTimeStamp();
        }

        return $dropDatePassed;
    }

    public function recalculateScoreByDropzone(Dropzone $dropzone)
    {
        $this->container->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);
        $this->container->get('icap.manager.dropzone_voter')->isAllowToEdit($dropzone);

        if (!$dropzone->getPeerReview()) {
            throw new AccessDeniedException();
        }
        // getting the repository
        $CorrectionRepo = $this->em->getRepository('IcapDropzoneBundle:Correction');
        // getting all the drop corrections
        $corrections = $CorrectionRepo->findBy(['dropzone' => $dropzone->getId()]);

        $this->container->get('icap.manager.correction_manager')->recalculateScoreForCorrections($dropzone, $corrections);
    }

    private function isBetweenDates($begin, $end, $dateToTest)
    {
        return $dateToTest > $begin && $dateToTest <= $end;
    }

    public function getResourcesNodeIdsForDownload(Dropzone $dropzone, $beginDate, $endDate)
    {
        $ids = [];

        // on veut récupérer uniquement les drops terminés.
        foreach ($dropzone->getDrops() as $drop) {
            if ($drop->getFinished()) {

                //si date début & date de fin
                // no date => get all completed drops
                // if dates are not null , get only complete drop between the 2 dates.
                if (($beginDate === null && $endDate === null) ||
                    ($beginDate !== null && $endDate !== null &&
                        $this->isBetweenDates($beginDate, $endDate, $drop->getDropDate()))
                ) {
                    // on récupère le dossier parent des documents.
                    $documents = $drop->getDocuments();
                    if (count($documents) > 0) {
                        $doc = $documents[0];
                        if ($doc->getResourceNode()) {
                            $rootId = $doc->getResourceNode()->getParent()->getId();
                            array_push($ids, $rootId);
                        }
                    }
                }
            }
        }

        return $ids;
    }

    /**
     * Imports dropzone object from array
     * (see dropzoneImporter for structure and description).
     *
     * @param array $data
     * @param $rootPath
     *
     * @return Dropzone
     */
    public function importDropzone(array $data, $rootPath)
    {
        $dropzone = new Dropzone();
        if (isset($data['data'])) {
            $dropzoneData = $data['data'];

            $dropzone->setEditionState($dropzoneData['editionState']);
            $dropzone->setInstruction($dropzoneData['instruction'] ? $this->getFromFile($dropzoneData['instruction'], $rootPath) : null);
            $dropzone->setCorrectionInstruction($dropzoneData['correctionInstruction'] ? $this->getFromFile($dropzoneData['correctionInstruction'], $rootPath) : null);
            $dropzone->setSuccessMessage($dropzoneData['successMessage'] ? $this->getFromFile($dropzoneData['successMessage'], $rootPath) : null);
            $dropzone->setFailMessage($dropzoneData['failMessage'] ? $this->getFromFile($dropzoneData['failMessage'], $rootPath) : null);
            $dropzone->setAllowWorkspaceResource($dropzoneData['allowWorkspaceResource']);
            $dropzone->setAllowUpload($dropzoneData['allowUpload']);
            $dropzone->setAllowUrl($dropzoneData['allowUrl']);
            $dropzone->setAllowRichText($dropzoneData['allowRichText']);
            $dropzone->setPeerReview($dropzoneData['peerReview']);
            $dropzone->setExpectedTotalCorrection($dropzoneData['expectedTotalCorrection']);
            $dropzone->setDisplayNotationToLearners($dropzoneData['displayNotationToLearners']);
            $dropzone->setDisplayNotationMessageToLearners($dropzoneData['displayNotationMessageToLearners']);
            $dropzone->setMinimumScoreToPass($dropzoneData['minimumScoreToPass']);
            $dropzone->setManualPlanning($dropzoneData['manualPlanning']);
            $dropzone->setManualState($dropzoneData['manualState']);
            $dropzone->setStartAllowDrop($dropzoneData['startAllowDrop'] ? new \DateTime($dropzoneData['startAllowDrop']) : null);
            $dropzone->setEndAllowDrop($dropzoneData['endAllowDrop'] ? new \DateTime($dropzoneData['endAllowDrop']) : null);
            $dropzone->setStartReview($dropzoneData['startReview'] ? new \DateTime($dropzoneData['startReview']) : null);
            $dropzone->setEndReview($dropzoneData['endReview'] ? new \DateTime($dropzoneData['endReview']) : null);
            $dropzone->setAllowCommentInCorrection($dropzoneData['allowCommentInCorrection']);
            $dropzone->setForceCommentInCorrection($dropzoneData['forceCommentInCorrection']);
            $dropzone->setDiplayCorrectionsToLearners($dropzoneData['diplayCorrectionsToLearners']);
            $dropzone->setAllowCorrectionDeny($dropzoneData['allowCorrectionDeny']);
            $dropzone->setTotalCriteriaColumn($dropzoneData['totalCriteriaColumn']);
            $dropzone->setAutoCloseOpenedDropsWhenTimeIsUp($dropzoneData['autoCloseOpenedDropsWhenTimeIsUp']);
            $dropzone->setAutoCloseState($dropzoneData['autoCloseState']);
            $dropzone->setNotifyOnDrop($dropzoneData['notifyOnDrop']);
        }

        return $dropzone;
    }

    public function exportDropzone(Workspace $workspace, array &$files, Dropzone $dropzone)
    {
        return [
            'editionState' => $dropzone->getEditionState(),
            'instruction' => $this->makeFile($dropzone->getInstruction(), $files),
            'correctionInstruction' => $this->makeFile($dropzone->getCorrectionInstruction(), $files),
            'successMessage' => $this->makeFile($dropzone->getSuccessMessage(), $files),
            'failMessage' => $this->makeFile($dropzone->getFailMessage(), $files),
            'allowWorkspaceResource' => $dropzone->getAllowWorkspaceResource(),
            'allowUpload' => $dropzone->getAllowUpload(),
            'allowUrl' => $dropzone->getAllowUrl(),
            'allowRichText' => $dropzone->getAllowRichText(),
            'peerReview' => $dropzone->getPeerReview(),
            'expectedTotalCorrection' => $dropzone->getExpectedTotalCorrection(),
            'displayNotationToLearners' => $dropzone->getDisplayNotationToLearners(),
            'displayNotationMessageToLearners' => $dropzone->getDisplayNotationMessageToLearners(),
            'minimumScoreToPass' => $dropzone->getMinimumScoreToPass(),
            'manualPlanning' => $dropzone->getManualPlanning(),
            'manualState' => $dropzone->getManualState(),
            'startAllowDrop' => $dropzone->getstartAllowDrop() ? $dropzone->getstartAllowDrop()->format('Y-m-d H:i:s') : null,
            'endAllowDrop' => $dropzone->getEndAllowDrop() ? $dropzone->getEndAllowDrop()->format('Y-m-d H:i:s') : null,
            'startReview' => $dropzone->getStartReview() ? $dropzone->getStartReview()->format('Y-m-d H:i:s') : null,
            'endReview' => $dropzone->getEndReview() ? $dropzone->getEndReview()->format('Y-m-d H:i:s') : null,
            'allowCommentInCorrection' => $dropzone->getAllowCommentInCorrection(),
            'forceCommentInCorrection' => $dropzone->getForceCommentInCorrection(),
            'diplayCorrectionsToLearners' => $dropzone->getDiplayCorrectionsToLearners(),
            'allowCorrectionDeny' => $dropzone->getAllowCorrectionDeny(),
            'totalCriteriaColumn' => $dropzone->getTotalCriteriaColumn(),
            'autoCloseOpenedDropsWhenTimeIsUp' => $dropzone->getAutoCloseOpenedDropsWhenTimeIsUp(),
            'autoCloseState' => $dropzone->getAutoCloseState(),
            'notifyOnDrop' => $dropzone->getNotifyOnDrop(),

        ];
    }

    private function makeFile($content, &$files)
    {
        $uid = uniqid().'.txt';
        $tmpPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.$uid;
        file_put_contents($tmpPath, $content);
        $files[$uid] = $tmpPath;

        return $uid;
    }

    private function getFromFile($filePath, $rootPath)
    {
        return file_get_contents($rootPath.DIRECTORY_SEPARATOR.$filePath);
    }
}
