<?php

namespace Claroline\EvaluationBundle\Serializer\Parameters;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\EvaluationBundle\Entity\Parameters\AbstractEvaluationParameters;

abstract class AbstractEvaluationParametersSerializer
{
    use SerializerTrait;

    public function serialize(AbstractEvaluationParameters $parameters, array $options = []): array
    {
        return [
            'scored' => $parameters->isScored(),
            'scoreTotal' => $parameters->getScoreTotal(),
            'successCondition' => !empty($parameters->getSuccessCondition()) ? $parameters->getSuccessCondition() : null,
            'endMessage' => $parameters->getEndMessage(),
            'successMessage' => $parameters->getSuccessMessage(),
            'failureMessage' => $parameters->getFailureMessage(),
        ];
    }

    public function deserialize(array $data, AbstractEvaluationParameters $parameters, array $options = []): AbstractEvaluationParameters
    {
        $this->sipe('scoreTotal', 'setScoreTotal', $data, $parameters);
        $this->sipe('scored', 'setScored', $data, $parameters);
        $this->sipe('successCondition', 'setSuccessCondition', $data, $parameters);
        $this->sipe('endMessage', 'setEndMessage', $data, $parameters);
        $this->sipe('successMessage', 'setSuccessMessage', $data, $parameters);
        $this->sipe('failureMessage', 'setFailureMessage', $data, $parameters);

        return $parameters;
    }
}
