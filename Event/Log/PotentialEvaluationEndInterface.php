<?php

namespace Innova\CollecticielBundle\Event\Log;

use Innova\CollecticielBundle\Entity\Correction;

interface PotentialEvaluationEndInterface
{
    /**
     * @return Correction
     */
    public function getCorrection();
}
