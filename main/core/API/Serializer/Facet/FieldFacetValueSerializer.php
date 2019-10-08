<?php

namespace Claroline\CoreBundle\API\Serializer\Facet;

use Claroline\AppBundle\API\Options;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * @param FieldFacetSerializer $fieldFacetSerializer
     * @param UserSerializer       $userSerializer
     * @param ContainerInterface   $container
     */
    public function __construct(
        FieldFacetSerializer $fieldFacetSerializer,
        ContainerInterface $container
    ) {
        $this->fieldFacetSerializer = $fieldFacetSerializer;
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
                'user' => $fieldFacetValue->getUser() ? [
                  'autoId' => $fieldFacetValue->getUser()->getId(), //for old compatibility purposes
                  'id' => $fieldFacetValue->getUser()->getUuid(),
                  'name' => $fieldFacetValue->getUser()->getFirstName().' '.$fieldFacetValue->getUser()->getLastName(),
                  'firstName' => $fieldFacetValue->getUser()->getFirstName(),
                  'lastName' => $fieldFacetValue->getUser()->getLastName(),
                  'username' => $fieldFacetValue->getUser()->getUsername(),
                  'email' => $fieldFacetValue->getUser()->getEmail(),
                  ] : null,
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
        $fieldFacet = $this->container->get('Claroline\AppBundle\Persistence\ObjectManager')
            ->getRepository(FieldFacet::class)
            ->findOneByUuid($data['fieldFacet']['id']);

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
