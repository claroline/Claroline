<?php

namespace Claroline\CoreBundle\API\Serializer\Facet;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FieldFacetValueSerializer
{
    /** @var ObjectManager */
    private $om;

    /** @var FieldFacetSerializer */
    private $fieldFacetSerializer;

    /** @var ContainerInterface */
    private $container;

    /**
     * FieldFacetValueSerializer constructor.
     */
    public function __construct(
        FieldFacetSerializer $fieldFacetSerializer,
        ContainerInterface $container
    ) {
        $this->om = $container->get(ObjectManager::class);
        $this->fieldFacetSerializer = $fieldFacetSerializer;
        // TODO : remove dependency to container (but there is a circular reference on UserSerializer)
        $this->container = $container;
    }

    public function getName()
    {
        return 'field_facet_value';
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
        //probably used by the clacoform
        $serialized = [
            'id' => $fieldFacetValue->getId(),
            'value' => $fieldFacetValue->getValue(),
            'name' => $fieldFacetValue->getFieldFacet()->getName(),
        ];

        // I don't think this is needed
        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'user' => $fieldFacetValue->getUser() ? $this->container->get(UserSerializer::class)->serialize($fieldFacetValue->getUser(), [Options::SERIALIZE_MINIMAL]) : null,
                'fieldFacet' => $this->fieldFacetSerializer->serialize($fieldFacetValue->getFieldFacet()),
            ]);
        }

        return $serialized;
    }

    /**
     * Deserializes a FieldFacetValue entity for the JSON api.
     *
     * @param array $options - a list of serialization options
     *
     * @return FieldFacetValue
     */
    public function deserialize(array $data, FieldFacetValue $fieldFacetValue = null, array $options = [])
    {
        /** @var FieldFacet $fieldFacet */
        $fieldFacet = $this->om
            ->getRepository(FieldFacet::class)
            ->findOneBy(['uuid' => $data['fieldFacet']['id']]);

        $fieldFacetValue->setFieldFacet($fieldFacet);
        $value = $data['value'];

        switch ($fieldFacet->getType()) {
            case FieldFacet::DATE_TYPE:
                $date = is_string($value) ?
                    new \DateTime($value) :
                    $value;
                $fieldFacetValue->setDateValue($date);
                break;
            case FieldFacet::NUMBER_TYPE:
                $fieldFacetValue->setFloatValue($value);
                break;
            case FieldFacet::CASCADE_TYPE:
                $fieldFacetValue->setArrayValue($value);
                break;
            case FieldFacet::CHOICE_TYPE:
                if (is_array($value)) {
                    $fieldFacetValue->setArrayValue($value);
                } else {
                    $fieldFacetValue->setStringValue($value);
                }
                break;
            case FieldFacet::BOOLEAN_TYPE:
                $fieldFacetValue->setBoolValue($value);
                break;
            default:
                $fieldFacetValue->setStringValue($value);
        }

        return $fieldFacetValue;
    }
}
