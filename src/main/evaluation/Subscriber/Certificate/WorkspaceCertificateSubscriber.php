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

use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\EvaluationBundle\Event\WorkspaceEvaluationEvent;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Claroline\EvaluationBundle\Manager\WorkspaceCertificateManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Generates certificate when a user is evaluated in a certified workspace.
 */
class WorkspaceCertificateSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly WorkspaceCertificateManager $certificateManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EvaluationEvents::WORKSPACE_EVALUATION => 'generateCertificate',
        ];
    }

    public function generateCertificate(WorkspaceEvaluationEvent $event): void
    {
        if ($event->hasStatusChanged() && in_array($event->getEvaluation()->getStatus(), [EvaluationStatus::COMPLETED, EvaluationStatus::PASSED])) {
            $this->certificateManager->getCertificate($event->getEvaluation());
        }
    }
}
