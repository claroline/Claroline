<?php
namespace Icap\DropzoneBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Entity\User;
use Icap\DropzoneBundle\Entity\Dropzone;
use Icap\DropzoneBundle\Entity\Drop;
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
     * @return array UserIds.
     * */
    public function getDropzoneUsersIds(Dropzone $dropzone)
    {

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
        /*var_dump($userIds);
        var_dump($test);
        die;
        */
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
     *  currentstate : index of the current state  in the stateArray
     *  percent : rounded progress in percent
     *  nbCorrection : corrections made by the user in this evaluation.
     *   *
     * @param Dropzone dropzone
     * @param Drop drop
     * @return array (states, currentState,percent,nbCorrection)
     **/
    public function getDropzoneProgressByUser($dropzone, $user)
    {
        $drop = $this->em
            ->getRepository('IcapDropzoneBundle:Drop')
            ->findOneBy(array('dropzone' => $dropzone, 'user' => $user));
        $nbCorrections = $this->em
            ->getRepository('IcapDropzoneBundle:Correction')
            ->countFinished($dropzone, $user);
        return $this->getDrozponeProgress($dropzone, $drop, $nbCorrections);
    }

    /**
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
     *  7 : copy corrected, Evaluation End.
     *
     *  currentstate : index of the current state  in the stateArray
     *  percent : rounded progress in percent
     *  nbCorrection : corrections made by the user in this evaluation.
     *
     * @param Dropzone dropzone
     * @param Drop drop
     * @param int number of correction the user did.
     * @return array (states, currentState,percent,nbCorrection)
     **/
    public function getDrozponeProgress(Dropzone $dropzone, Drop $drop = null, $nbCorrection = 0)
    {
        $begin_states = array('Evaluation not started', 'awaiting for drop', 'drop provided');
        $end_states = array('waiting for correction', 'corrected copy');
        $states = array();


        $states = array_merge($states, $begin_states);
        $expectedCorrections = $dropzone->getExpectedTotalCorrection();

        $currentState = 0;
        // set the states of the dropzone.
        if ($dropzone->isPeerReview()) {
            // case of peerReview
            for ($i = 0; $i < $expectedCorrections; $i++) {
                array_push($states, 'correction n°%nb_correction%/%expected_correction%');
            }

            $states = array_merge($states, $end_states);

            // getting the current state.

            // if no drop, state is 0 as default.
            if (!empty($drop)) {
                $currentState++;

                if ($drop->getFinished()) {
                    $currentState++;
                }
                // @TODO manage invalidated corrections. 
                //  update the state with the correction number.
                if ($nbCorrection > $expectedCorrections) {
                    $nbCorrection = $expectedCorrections;
                }

                $currentState += $nbCorrection;

                if ($nbCorrection >= $expectedCorrections) {
                    $currentState++;
                }

                if ($drop->countFinishedCorrections() >= $expectedCorrections) {
                    $currentState++;
                }
            }
        } else {
            // case of normal correction.
            $states = array_merge($states, $end_states);
            // if no drop, state is 0 as default.
            if (!empty($drop)) {
                $currentState++;

                if ($drop->getFinished()) {
                    $currentState += 2;
                }
                if ($drop->countFinishedCorrections() >= $expectedCorrections) {
                    $currentState++;
                }
            }
        }

        $percent = round(($currentState * 100) / (count($states) - 1));

        return array('states' => $states, 'currentState' => $currentState, 'percent' => $percent, 'nbCorrection' => $nbCorrection);
    }


    /**
     * if the dropzone option 'autocloseOpenDropsWhenTimeIsUp' is activated, and evalution allowToDrop time is over,
     *  this will close all drop not closed yet.
     * @param Dropzone $dropzone
     */
    public function closeDropzoneOpenedDrops(Dropzone $dropzone,$force=false)
    {
        if ($force || $this->isDropzoneDropTimeIsUp($dropzone)) {
            $dropRepo = $this->em->getRepository('IcapDropzoneBundle:Drop');
            $dropRepo->closeUnTerminatedDropsByDropzone($dropzone->getId());
            $dropzone->setAutoCloseState(Dropzone::AUTO_CLOSED_STATE_CLOSED);
        }
    }

    /**
     * Check if dropzone  options are ok in order to autoclose Drops
     * @param Dropzone $dropzone
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
}