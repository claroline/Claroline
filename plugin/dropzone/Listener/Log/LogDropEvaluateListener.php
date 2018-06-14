<?php

namespace Icap\DropzoneBundle\Listener\Log;

use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Doctrine\ORM\EntityManager;
use Icap\DropzoneBundle\Entity\Drop;
use Icap\DropzoneBundle\Event\Log\LogDropEvaluateEvent;
use Icap\DropzoneBundle\Event\Log\PotentialEvaluationEndInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
            if ($correction->getDrop()->getUser()->getId() !== $correction->getUser()->getId()) {
                $drop = $this->entityManager->getRepository('IcapDropzoneBundle:Drop')->findOneBy(['user' => $correction->getUser()]);
                if (null !== $drop) {
                    $this->sendFinishedLog($drop);
                }
            }
        }
    }

    private function sendFinishedLog(Drop $drop)
    {
        if (null !== $drop) {
            if (false === $drop->getDropzone()->getPeerReview() || $drop->countFinishedCorrections() >= $drop->getDropzone()->getExpectedTotalCorrection()) {
                $finished = false;
                if (true === $drop->getDropzone()->getPeerReview()) {
                    $nbCorrections = $this->entityManager
                        ->getRepository('IcapDropzoneBundle:Correction')
                        ->countFinished($drop->getDropzone(), $drop->getUser());

                    if ($nbCorrections >= $drop->getDropzone()->getExpectedTotalCorrection()) {
                        $finished = true;
                    }
                } else {
                    $finished = true;
                }

                if (true === $finished) {
                    $grade = $drop->getCalculatedGrade();
                    $event = new LogDropEvaluateEvent($drop->getDropzone(), $drop, $grade);
                    $event->setDoer($drop->getUser());
                    $this->eventDispatcher->dispatch('log', $event);
                }
            }
        }
    }
}
