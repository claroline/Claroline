<?php

namespace Claroline\OpenBadgeBundle\Serializer;

use Claroline\AppBundle\API\Options as ApiOptions;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\OpenBadgeBundle\Entity\Evidence;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class EvidenceSerializer
{
    use SerializerTrait;

    /** @var RouterInterface */
    private $router;

    /** @var ResourceNodeSerializer */
    private $resourceNodeSerializer;

    /** @var WorkspaceSerializer */
    private $workspaceSerializer;

    /**
     * EvidenceSerializer constructor.
     *
     * @param RouterInterface        $router
     * @param ResourceNodeSerializer $resourceNodeSerializer
     * @param WorkspaceSerializer    $workspaceSerializer
     */
    public function __construct(
        RouterInterface $router,
        ResourceNodeSerializer $resourceNodeSerializer,
        WorkspaceSerializer $workspaceSerializer
    ) {
        $this->router = $router;
        $this->resourceNodeSerializer = $resourceNodeSerializer;
        $this->workspaceSerializer = $workspaceSerializer;
    }

    public function getName()
    {
        return 'open_badge_evidence';
    }

    public function getClass()
    {
        return Evidence::class;
    }

    /**
     * Serializes a Assertion entity.
     *
     * @param Evidence $evidence
     * @param array    $options
     *
     * @return array
     */
    public function serialize(Evidence $evidence, array $options = [])
    {
        $data = [
            'id' => $evidence->getUuid(),
            'narrative' => $evidence->getNarrative(),
            'name' => $evidence->getName(),
        ];

        if (in_array(Options::ENFORCE_OPEN_BADGE_JSON, $options)) {
            $data['id'] = $this->router->generate('apiv2_open_badge__evidence', ['evidence' => $evidence->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL);
            $data['type'] = 'Evidence';
        } else {
            if ($evidence->getResourceEvidence()) {
                $data['resource'] = $this->resourceNodeSerializer->serialize($evidence->getResourceEvidence()->getResourceNode(), [ApiOptions::SERIALIZE_MINIMAL]);
            } else {
                $data['resource'] = null;
            }

            if ($evidence->getWorkspaceEvidence()) {
                $data['workspace'] = $this->workspaceSerializer->serialize($evidence->getWorkspaceEvidence()->getWorkspace(), [ApiOptions::SERIALIZE_MINIMAL]);
            } else {
                $data['workspace'] = null;
            }
        }

        return $data;
    }

    /**
     * Deserializes a Evidence entity.
     *
     * @param array    $data
     * @param Evidence $evidence
     * @param array    $options
     *
     * @return Evidence
     */
    public function deserialize(array $data, Evidence $evidence = null, array $options = [])
    {
        $this->sipe('id', 'setUuid', $data, $evidence);
        $this->sipe('name', 'setName', $data, $evidence);
        $this->sipe('narrative', 'setNarrative', $data, $evidence);

        return $evidence;
    }
}
