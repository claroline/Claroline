<?php
namespace Icap\DropzoneBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Icap\DropzoneBundle\Entity\Correction;
use Icap\DropzoneBundle\Entity\Drop;
use Icap\DropzoneBundle\Event\Log\LogDropEvaluateEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @DI\Service("icap.dropzone_log.manager")
 */
class DropzoneLogManager
{
    private $entityManager;
    private $eventDispatcher;

    /**
     * @DI\InjectParams({
     *     "entityManager" = @DI\Inject("doctrine.orm.default_entity_manager"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher")
     * })
     */
    public function __construct(EntityManager $entityManager, EventDispatcher $eventDispatcher)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function logNewCorrection(Correction $correction)
    {
        $this->sendFinishedLog($correction->getDrop());
        if ($correction->getDrop()->getUser()->getId() != $correction->getUser()->getId()) {
            $drop = $this->entityManager->getRepository('IcapDropzoneBundle:Drop')->findOneBy(array('user' => $correction->getUser()));
            if ($drop !== null) {
                $this->sendFinishedLog($drop);
            }
        }
    }

    private function sendFinishedLog(Drop $drop)
    {
        if ($drop != null) {
            if ($drop->countFinishedCorrections() >= $drop->getDropzone()->getExpectedTotalCorrection()) {
                $finished = false;
                if ($drop->getDropzone()->getPeerReview() == true) {
                    $nbCorrections = $this->entityManager
                        ->getRepository('IcapDropzoneBundle:Correction')
                        ->countFinished($drop->getDropzone(), $drop->getUser());

                    if ($nbCorrections >= $drop->getDropzone()->getExpectedTotalCorrection()) {
                        $finished = true;
                    }
                } else {
                    $finished = true;
                }

                if ($finished) {
                    $grade = $drop->getCalculatedGrade();
                    $event = new LogDropEvaluateEvent($drop->getDropzone(), $drop, $grade);
                    $event->setDoer($drop->getUser());
                    $this->eventDispatcher->dispatch('log', $event);
                }
            }
        }
    }
}
