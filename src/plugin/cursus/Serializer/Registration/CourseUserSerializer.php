<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Serializer\Registration;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CursusBundle\Entity\Registration\AbstractUserRegistration;
use Claroline\CursusBundle\Entity\Registration\CourseUser;
use Claroline\CursusBundle\Serializer\CourseSerializer;

class CourseUserSerializer extends AbstractUserSerializer
{
    use SerializerTrait;

    private CourseSerializer $courseSerializer;
    private ObjectManager $om;
    private FacetManager $facetManager;

    public function __construct(
        UserSerializer $userSerializer,
        CourseSerializer $courseSerializer,
        ObjectManager $om,
        FacetManager $facetManager
    ) {
        parent::__construct($userSerializer);

        $this->courseSerializer = $courseSerializer;
        $this->om = $om;
        $this->facetManager = $facetManager;
    }

    public function getClass(): string
    {
        return CourseUser::class;
    }

    /**
     * @param CourseUser $userRegistration
     */
    public function serialize(AbstractUserRegistration $userRegistration, array $options = []): array
    {
        $serialized = array_merge(parent::serialize($userRegistration, $options), [
            'course' => $this->courseSerializer->serialize($userRegistration->getCourse(), [Options::SERIALIZE_MINIMAL]),
        ]);

        if (0 !== $userRegistration->getFacetValues()->count()) {
            $serialized['data'] = [];
            foreach ($userRegistration->getFacetValues() as $field) {
                // we just flatten field facets in the base user structure
                $serialized['data'][$field->getFieldFacet()->getUuid()] = $this->facetManager->serializeFieldValue(
                    $userRegistration,
                    $field->getType(),
                    $field->getValue()
                );
            }
        }

        return $serialized;
    }

    /**
     * @param CourseUser $userRegistration
     */
    public function deserialize(array $data, AbstractUserRegistration $userRegistration, ?array $options = []): AbstractUserRegistration
    {
        parent::deserialize($data, $userRegistration, $options);

        if (isset($data['data'])) {
            foreach ($data['data'] as $fieldId => $fieldValue) {
                $fieldFacetValue = $userRegistration->getFacetValue($fieldId) ?? new FieldFacetValue();
                $fieldFacet = $this->om->getRepository(FieldFacet::class)->findOneBy(['uuid' => $fieldId]);
                if (empty($fieldFacet)) {
                    $userRegistration->removeFacetValue($fieldFacetValue);
                } else {
                    $fieldFacetValue->setUser($userRegistration->getUser());
                    $fieldFacetValue->setFieldFacet($fieldFacet);
                    $fieldFacetValue->setValue(
                        $this->facetManager->deserializeFieldValue(
                            $userRegistration->getUser(),
                            $fieldFacet->getType(),
                            $fieldValue
                        )
                    );

                    $userRegistration->addFacetValue($fieldFacetValue);
                }
            }
        }

        return $userRegistration;
    }
}
