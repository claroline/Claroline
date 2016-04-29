<?php

namespace Icap\DropzoneBundle\Listener\Log;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Entity\Log\Log;
use Doctrine\ORM\EntityManager;
use Icap\DropzoneBundle\Entity\Drop;
use Icap\DropzoneBundle\Event\Log\LogDropEvaluateEvent;
use Icap\DropzoneBundle\Event\Log\PotentialEvaluationEndInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
            $correction = $event->getCorrection();
            $this->sendFinishedLog($correction->getDrop());
            if ($correction->getDrop()->getUser()->getId() != $correction->getUser()->getId()) {
                $drop = $this->entityManager->getRepository('IcapDropzoneBundle:Drop')->findOneBy(array('user' => $correction->getUser()));
                if ($drop !== null) {
                    $this->sendFinishedLog($drop);
                }
            }
        }
    }

    private function sendFinishedLog(Drop $drop)
    {
        if ($drop != null) {
            if ($drop->getDropzone()->getPeerReview() === false || $drop->countFinishedCorrections() >= $drop->getDropzone()->getExpectedTotalCorrection()) {
                $finished = false;
                if ($drop->getDropzone()->getPeerReview() === true) {
                    $nbCorrections = $this->entityManager
                        ->getRepository('IcapDropzoneBundle:Correction')
                        ->countFinished($drop->getDropzone(), $drop->getUser());

                    if ($nbCorrections >= $drop->getDropzone()->getExpectedTotalCorrection()) {
                        $finished = true;
                    }
                } else {
                    $finished = true;
                }

                if ($finished === true) {
                    $grade = $drop->getCalculatedGrade();
                    $event = new LogDropEvaluateEvent($drop->getDropzone(), $drop, $grade);
                    $event->setDoer($drop->getUser());
                    $this->eventDispatcher->dispatch('log', $event);
                }
            }
        }
    }
}
