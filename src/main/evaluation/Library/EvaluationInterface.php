<?php

namespace Claroline\EvaluationBundle\Library;

interface EvaluationInterface
{
    public function getProgression(): float;

    public function getScore(): ?float;

    public function getScoreMax(): ?float;

    public function getStatus(): ?string;

    public function isTerminated(): bool;
}
