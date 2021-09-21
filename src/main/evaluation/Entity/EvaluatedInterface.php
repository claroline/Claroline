<?php

namespace Claroline\EvaluationBundle\Entity;

interface EvaluatedInterface
{
    public function getScore(): ?float;

    public function getSuccessThreshold(): ?float;

    public function getSuccessMessage(): ?string;

    public function getFailureMessage(): ?string;
}
