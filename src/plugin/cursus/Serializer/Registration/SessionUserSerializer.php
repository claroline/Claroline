<?php

namespace Claroline\CursusBundle\Serializer\Registration;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Facet\PanelFacetSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use Claroline\CoreBundle\Entity\Facet\PanelFacet;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CursusBundle\Entity\Registration\AbstractUserRegistration;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Claroline\CursusBundle\Serializer\CourseSerializer;
use Claroline\CursusBundle\Serializer\SessionSerializer;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SessionUserSerializer extends AbstractUserSerializer
{
    use SerializerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        UserSerializer $userSerializer,
        private readonly CourseSerializer $courseSerializer,
        private readonly SessionSerializer $sessionSerializer,
        private readonly ObjectManager $om,
        private readonly FacetManager $facetManager,
        private readonly PanelFacetSerializer $panelFacetSerializer,
        private readonly WorkspaceSerializer $workspaceSerializer
    ) {
        parent::__construct($authorization, $userSerializer);
    }

    public function getClass(): string
    {
        return SessionUser::class;
    }

    /**
     * @param SessionUser $userRegistration
     */
    public function serialize(AbstractUserRegistration $userRegistration, array $options = []): array
    {
        $course = $userRegistration->getCourse();
        $session = $userRegistration->getSession();

        $serialized = array_merge(parent::serialize($userRegistration, $options), [
            'course' => $this->courseSerializer->serialize($course, [SerializerInterface::SERIALIZE_MINIMAL]),
            'session' => $session ? $this->sessionSerializer->serialize($session, [SerializerInterface::SERIALIZE_MINIMAL]) : null,
        ]);

        if (in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            return $serialized;
        }

        if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options) && 0 !== $course->getPanelFacets()->count()) {
            // get the form definition for the edition modale in lists
            $serialized['form'] = array_map(function (PanelFacet $panelFacet) {
                return $this->panelFacetSerializer->serialize($panelFacet);
            }, $course->getPanelFacets()->toArray());
        }

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

        if ($session && $session->getWorkspace()) {
            // Only to be able to generate a link to the workspace from the user overview,
            // we do not get it through the serialized `session`. We only have the minimal representation here, and
            // we don't want to fetch the workspace when getting the full list of registrations.
            $serialized['workspace'] = $this->workspaceSerializer->serialize($session->getWorkspace(), [SerializerInterface::SERIALIZE_MINIMAL]);
        }

        return $serialized;
    }

    /**
     * @param SessionUser $userRegistration
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
