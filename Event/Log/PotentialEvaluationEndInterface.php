<?php

namespace Icap\DropzoneBundle\Event\Log;

use Icap\DropzoneBundle\Entity\Correction;

interface PotentialEvaluationEndInterface
{
    /**
     * @return Correction
     */
    public function getCorrection();
}
