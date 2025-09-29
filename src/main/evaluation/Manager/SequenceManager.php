<?php

namespace Claroline\EvaluationBundle\Manager;

use Claroline\AppBundle\Manager\ViewerManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
use Claroline\EvaluationBundle\Entity\Sequence\SequenceView;

class SequenceManager
{
    public function __construct(
        private readonly ViewerManager $viewerManager
    ) {
    }

    public function addView(Sequence $sequence, ?User $user = null): void
    {
        $this->viewerManager->addView(SequenceView::class, $sequence, $user);
    }
}
