<?php

namespace Claroline\EvaluationBundle\Serializer\Parameters;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\EvaluationBundle\Entity\Parameters\AbstractEvaluationParameters;
use Claroline\EvaluationBundle\Entity\Parameters\ResourceParameters;

class ResourceParametersSerializer extends AbstractEvaluationParametersSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly ResourceNodeSerializer $resourceNodeSerializer,
    ) {
    }

    public static function getName(): string
    {
        return 'resource_evaluation_parameters';
    }

    public static function getClass(): string
    {
        return ResourceParameters::class;
    }

    /** @param ResourceParameters $parameters */
    public function serialize(AbstractEvaluationParameters $parameters, array $options = []): array
    {
        $serialized = parent::serialize($parameters, $options);

        $serialized['maxAttempts'] = $parameters->getMaxAttempts();
        $serialized['attemptsReachedMessage'] = $parameters->getAttemptsReachedMessage();

        if (!in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            $serialized['resource'] = $this->resourceNodeSerializer->serialize($parameters->getResource(), [SerializerInterface::SERIALIZE_MINIMAL]);
        }

        return $serialized;
    }

    /** @param ResourceParameters $parameters */
    public function deserialize(array $data, AbstractEvaluationParameters $parameters, array $options = []): ResourceParameters
    {
        parent::deserialize($data, $parameters, $options);

        $this->sipe('maxAttempts', 'setMaxAttempts', $data, $parameters);
        $this->sipe('attemptsReachedMessage', 'setAttemptsReachedMessage', $data, $parameters);

        return $parameters;
    }
}
