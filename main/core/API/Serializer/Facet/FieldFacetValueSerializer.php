<?php

namespace Claroline\CoreBundle\API\Serializer\Facet;

use Claroline\AppBundle\API\Options;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("claroline.serializer.field_facet_value")
 * @DI\Tag("claroline.serializer")
 */
class FieldFacetValueSerializer
{
    const OPTION_MINIMAL = 'minimal';

    /** @var FieldFacetSerializer */
    private $fieldFacetSerializer;

    /** @var UserSerializer */
    private $userSerializer;

    /** @var ContainerInterface */
    private $container;

    /**
     * FieldFacetValueSerializer constructor.
     *
     * @DI\InjectParams({
     *     "fieldFacetSerializer" = @DI\Inject("claroline.serializer.field_facet"),
     *     "userSerializer"       = @DI\Inject("claroline.serializer.user"),
     *     "container"            = @DI\Inject("service_container")
     * })
     *
     * @param FieldFacetSerializer $fieldFacetSerializer
     * @param UserSerializer       $userSerializer
     * @param ContainerInterface   $container
     */
    public function __construct(
        FieldFacetSerializer $fieldFacetSerializer,
        UserSerializer $userSerializer,
        ContainerInterface $container
    ) {
        $this->fieldFacetSerializer = $fieldFacetSerializer;
        $this->userSerializer = $userSerializer;
        $this->container = $container;
    }

    /**
     * Serializes a FieldFacetValue entity for the JSON api.
     *
     * @param FieldFacetValue $fieldFacetValue - the field facet value to serialize
     * @param array           $options         - a list of serialization options
     *
     * @return array - the serialized representation of the field facet value
     */
    public function serialize(FieldFacetValue $fieldFacetValue, array $options = [])
    {
        //this is the bloc that should be used
        if (in_array(Options::PROFILE_SERIALIZE, $options)) {
        }

        //probably used by the clacoform
        $serialized = [
            'id' => $fieldFacetValue->getId(),
            'value' => $fieldFacetValue->getValue(),
            'name' => $fieldFacetValue->getFieldFacet()->getName(),
        ];

        if (!in_array(static::OPTION_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'user' => $fieldFacetValue->getUser() ? $this->userSerializer->serialize($fieldFacetValue->getUser()) : null,
                'fieldFacet' => $this->fieldFacetSerializer->serialize($fieldFacetValue->getFieldFacet()),
            ]);
        }

        return $serialized;
    }

    /**
     * Deserializes a FieldFacetValue entity for the JSON api.
     *
     * @param array                $data
     * @param FieldFacetValue|null $fieldFacetValue
     * @param array                $options         - a list of serialization options
     *
     * @return array - the serialized representation of the field facet value
     */
    public function deserialize(array $data, FieldFacetValue $fieldFacetValue = null, array $options = [])
    {
        $fieldFacet = $this->container->get('claroline.api.serializer')->deserialize(
          'Claroline\CoreBundle\Entity\Facet\FieldFacet',
          $data['fieldFacet']
        );

        $fieldFacetValue->setFieldFacet($fieldFacet);
        $value = $data['value'];

        switch ($fieldFacet->getType()) {
            case FieldFacet::DATE_TYPE:
                $date = is_string($value) ?
                    new \DateTime($value) :
                    $value;
                $fieldFacetValue->setDateValue($date);
                break;
            case FieldFacet::FLOAT_TYPE:
                $fieldFacetValue->setFloatValue($value);
                break;
            case FieldFacet::CHECKBOXES_TYPE:
                $fieldFacetValue->setArrayValue($value);
                break;
            default:
                $fieldFacetValue->setStringValue($value);
        }

        return $fieldFacetValue;
    }
}
