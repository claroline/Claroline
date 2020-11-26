<?php

namespace Claroline\BigBlueButtonBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\BigBlueButtonBundle\Entity\BBB;
use Claroline\BigBlueButtonBundle\Manager\BBBManager;
use Claroline\CoreBundle\API\Serializer\Resource\AbstractResourceSerializer;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;

class BBBSerializer
{
    use SerializerTrait;

    /** @var ResourceNodeSerializer */
    private $nodeSerializer;
    /** @var BBBManager */
    private $manager;

    public function __construct(
        ResourceNodeSerializer $nodeSerializer,
        BBBManager $manager
    ) {
        $this->nodeSerializer = $nodeSerializer;
        $this->manager = $manager;
    }

    public function serialize(BBB $bbb, array $options = []): array
    {
        $serialized = [
            'id' => $bbb->getUuid(),
            'welcomeMessage' => $bbb->getWelcomeMessage(),
            'endMessage' => $bbb->getEndMessage(),
            'newTab' => $bbb->isNewTab(),
            'moderatorRequired' => $bbb->isModeratorRequired(),
            'record' => $bbb->isRecord(),
            'ratio' => $bbb->getRatio(),
            'customUsernames' => $bbb->hasCustomUsernames(),
            'runningOn' => $bbb->getRunningOn(),
            'restrictions' => [
                'disabled' => !$bbb->isActivated(),
                'server' => $bbb->getServer(),
            ],
            'info' => $this->manager->getMeetingInfo($bbb),
        ];

        if (in_array(AbstractResourceSerializer::SERIALIZE_NODE, $options)) {
            $serialized['node'] = $this->nodeSerializer->serialize($bbb->getResourceNode(), [Options::SERIALIZE_MINIMAL]);
        }

        return $serialized;
    }

    public function deserialize(array $data, BBB $bbb): BBB
    {
        $this->sipe('welcomeMessage', 'setWelcomeMessage', $data, $bbb);
        $this->sipe('endMessage', 'setEndMessage', $data, $bbb);
        $this->sipe('newTab', 'setNewTab', $data, $bbb);
        $this->sipe('moderatorRequired', 'setModeratorRequired', $data, $bbb);
        $this->sipe('record', 'setRecord', $data, $bbb);
        $this->sipe('ratio', 'setRatio', $data, $bbb);
        $this->sipe('customUsernames', 'setCustomUsernames', $data, $bbb);
        $this->sipe('restrictions.server', 'setServer', $data, $bbb);

        if (isset($data['restrictions']) && isset($data['restrictions']['disabled'])) {
            $bbb->setActivated(!$data['restrictions']['disabled']);
        }

        return $bbb;
    }
}
