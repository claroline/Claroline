<?php

namespace Claroline\EvaluationBundle\Serializer\Parameters;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\EvaluationBundle\Entity\Parameters\AbstractEvaluationParameters;
use Claroline\EvaluationBundle\Entity\Parameters\WorkspaceParameters;
use Claroline\TemplateBundle\Entity\Template;
use Claroline\TemplateBundle\Serializer\TemplateSerializer;

class WorkspaceParametersSerializer extends AbstractEvaluationParametersSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly ObjectManager $om,
        private readonly TemplateSerializer $templateSerializer,
        private readonly WorkspaceSerializer $workspaceSerializer
    ) {
    }

    public static function getName(): string
    {
        return 'workspace_evaluation_parameters';
    }

    public static function getClass(): string
    {
        return WorkspaceParameters::class;
    }

    /** @param WorkspaceParameters $parameters */
    public function serialize(AbstractEvaluationParameters $parameters, array $options = []): array
    {
        $serialized = parent::serialize($parameters, $options);

        $serialized['certified'] = $parameters->isCertified();
        if ($parameters->getCertificateTemplate()) {
            $serialized['certificateTemplate'] = $this->templateSerializer->serialize($parameters->getCertificateTemplate(), [SerializerInterface::SERIALIZE_MINIMAL]);
        }

        if (!in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            $serialized['workspace'] = $this->workspaceSerializer->serialize($parameters->getWorkspace(), [SerializerInterface::SERIALIZE_MINIMAL]);
        }

        return $serialized;
    }

    /** @param WorkspaceParameters $parameters */
    public function deserialize(array $data, AbstractEvaluationParameters $parameters, array $options = []): WorkspaceParameters
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
