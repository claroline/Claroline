<?php

namespace  Claroline\DropZoneBundle\Event\Log;

use Claroline\DropZoneBundle\Entity\Correction;

interface PotentialEvaluationEndInterface
{
    /**
     * @return Correction
     */
    public function getCorrection();
}
