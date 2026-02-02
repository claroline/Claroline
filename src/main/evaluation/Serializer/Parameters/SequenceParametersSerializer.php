<?php

namespace Claroline\EvaluationBundle\Serializer\Parameters;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\EvaluationBundle\Entity\Parameters\AbstractEvaluationParameters;
use Claroline\EvaluationBundle\Entity\Parameters\SequenceParameters;
use Claroline\EvaluationBundle\Serializer\Sequence\SequenceSerializer;
use Claroline\TemplateBundle\Entity\Template;
use Claroline\TemplateBundle\Serializer\TemplateSerializer;

class SequenceParametersSerializer extends AbstractEvaluationParametersSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly ObjectManager $om,
        private readonly TemplateSerializer $templateSerializer,
        private readonly SequenceSerializer $sequenceSerializer,
    ) {
    }

    public static function getName(): string
    {
        return 'sequence_evaluation_parameters';
    }

    public static function getClass(): string
    {
        return SequenceParameters::class;
    }

    /** @param SequenceParameters $parameters */
    public function serialize(AbstractEvaluationParameters $parameters, array $options = []): array
    {
        $serialized = parent::serialize($parameters, $options);

        $serialized['certified'] = $parameters->isCertified();
        if ($parameters->getCertificateTemplate()) {
            $serialized['certificateTemplate'] = $this->templateSerializer->serialize($parameters->getCertificateTemplate(), [SerializerInterface::SERIALIZE_MINIMAL]);
        }

        if (!in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            $serialized['sequence'] = $this->sequenceSerializer->serialize($parameters->getSequence(), [SerializerInterface::SERIALIZE_MINIMAL]);
        }

        return $serialized;
    }

    /** @param SequenceParameters $parameters */
    public function deserialize(array $data, AbstractEvaluationParameters $parameters, array $options = []): SequenceParameters
    {
        parent::deserialize($data, $parameters, $options);

        $this->sipe('certified', 'setCertified', $data, $parameters);

        if (array_key_exists('certificateTemplate', $data)) {
            $template = null;
            if (!empty($data['certificateTemplate']) && !empty($data['certificateTemplate']['id'])) {
                $template = $this->om->getRepository(Template::class)->findOneBy(['uuid' => $data['certificateTemplate']['id']]);
            }
            $parameters->setCertificateTemplate($template);
        }

        return $parameters;
    }
}
