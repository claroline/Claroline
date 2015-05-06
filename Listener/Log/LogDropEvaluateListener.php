<?php

namespace Icap\DropzoneBundle\Listener\Log;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\LogGroupDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogResourceDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogUserDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceRoleDeleteEvent;
use Claroline\CoreBundle\Event\Log\LogNotRepeatableInterface;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Event\LogCreateEvent;
use Claroline\CoreBundle\Manager\RoleManager;
use Doctrine\ORM\EntityManager;
use Icap\DropzoneBundle\Entity\Drop;
use Icap\DropzoneBundle\Event\Log\LogCorrectionEndEvent;
use Icap\DropzoneBundle\Event\Log\LogCorrectionUpdateEvent;
use Icap\DropzoneBundle\Event\Log\LogDropEvaluateEvent;
use Icap\DropzoneBundle\Event\Log\PotentialEvaluationEndInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 */
class LogDropEvaluateListener
{
    private $entityManager;
    private $eventDispatcher;

    /**
     * @DI\InjectParams({
     *     "entityManager" = @DI\Inject("doctrine.orm.default_entity_manager"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher")
     * })
     */
    public function __construct(EntityManager $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @DI\Observe("log")
     *
     * @param LogGenericEvent $event
     */
    public function onLog(LogGenericEvent $event)
    {
        if ($event instanceof PotentialEvaluationEndInterface) {
//            var_dump('on log !! : '.$event->getAction());
//            var_dump('instance of potential evaluation end interface');
            $correction = $event->getCorrection();
            $this->sendFinishedLog($correction->getDrop());
            if ($correction->getDrop()->getUser()->getId() != $correction->getUser()->getId()) {
                $drop = $this->entityManager->getRepository('IcapDropzoneBundle:Drop')->findOneBy(array('user' => $correction->getUser()));
                if ($drop !== null) {
                    $this->sendFinishedLog($drop);
                }
            }
//            die();
        }
    }

    private function sendFinishedLog(Drop $drop)
    {
//        var_dump('sendFinishedLog');
        if ($drop != null) {
//            var_dump('drop not null');
            if ($drop->getDropzone()->getPeerReview() === false or $drop->countFinishedCorrections() >= $drop->getDropzone()->getExpectedTotalCorrection()) {
//                var_dump('pas de peer review ou bien assez de correction');
                $finished = false;
                if ($drop->getDropzone()->getPeerReview() === true) {
//                    var_dump('peer review. mais est ce que le user a corrigé assez de copie');
                    $nbCorrections = $this->entityManager
                        ->getRepository('IcapDropzoneBundle:Correction')
                        ->countFinished($drop->getDropzone(), $drop->getUser());

                    if ($nbCorrections >= $drop->getDropzone()->getExpectedTotalCorrection()) {
                        $finished = true;
                    }
                } else {
//                    var_dump('pas de peer review donc fini !');
                    $finished = true;
                }

                if ($finished === true) {
//                    var_dump('finish');
                    $grade = $drop->getCalculatedGrade();
                    $event = new LogDropEvaluateEvent($drop->getDropzone(), $drop, $grade);
                    $event->setDoer($drop->getUser());

//                    var_dump('finish grade = '.$grade);

                    $this->eventDispatcher->dispatch('log', $event);
                }
            }
        }
    }
}
