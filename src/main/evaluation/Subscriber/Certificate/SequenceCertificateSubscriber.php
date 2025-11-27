<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\EvaluationBundle\Subscriber\Certificate;

use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\EvaluationBundle\Entity\Certificate\SequenceCertificate;
use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\EvaluationBundle\Event\SequenceEvaluationEvent;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Claroline\EvaluationBundle\Manager\SequenceCertificateManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Generates certificate when a user is evaluated in a certified sequence.
 */
class SequenceCertificateSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SequenceCertificateManager $certificateManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EvaluationEvents::SEQUENCE_EVALUATION => 'generateCertificate',
            CrudEvents::getEventName(CrudEvents::POST_DELETE, SequenceCertificate::class) => 'deleteCertificateFile',
        ];
    }

    public function generateCertificate(SequenceEvaluationEvent $event): void
    {
        if ($event->hasStatusChanged() && in_array($event->getEvaluation()->getStatus(), [EvaluationStatus::COMPLETED, EvaluationStatus::PASSED])) {
            $this->certificateManager->getCertificate($event->getEvaluation());
        }
    }

    public function deleteCertificateFile(DeleteEvent $event): void
    {
        /** @var SequenceCertificate $object */
        $certificate = $event->getObject();

        $this->certificateManager->removeCertificateFile($certificate);
    }
}
