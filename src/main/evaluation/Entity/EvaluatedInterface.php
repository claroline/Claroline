<?php

namespace Claroline\EvaluationBundle\Entity;

interface EvaluatedInterface
{
    /**
     * Is the entity evaluated ?
     */
    public function isEvaluated(): bool;

    public function isRequired(): bool;

    public function getWeightingCoefficient(): float;

    // TODO : I will need to store the unit too (percent or points) but I don't want to break into multiple props to keep it as simple as possible
    // either store a json ({value: 10, unit: 'percent'}) or a string I will be able to parse (10% / 50points)
    public function getSuccessCondition();

    // TODO : not needed in Interface
    public function getSuccessMessage(): string;

    public function getFailedMessage(): string;
}
