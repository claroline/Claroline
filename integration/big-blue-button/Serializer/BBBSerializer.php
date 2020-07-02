<?php

namespace Claroline\BigBlueButtonBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\BigBlueButtonBundle\Entity\BBB;

class BBBSerializer
{
    use SerializerTrait;

    /**
     * Serializes a BBB entity for the JSON api.
     *
     * @param BBB   $bbb     - the BBB resource to serialize
     * @param array $options - a list of serialization options
     *
     * @return array - the serialized representation of the BBB resource
     */
    public function serialize(BBB $bbb, array $options = [])
    {
        return [
            'id' => $bbb->getUuid(),
            'welcomeMessage' => $bbb->getWelcomeMessage(),
            'endMessage' => $bbb->getEndMessage(),
            'newTab' => $bbb->isNewTab(),
            'moderatorRequired' => $bbb->isModeratorRequired(),
            'record' => $bbb->isRecord(),
            'ratio' => $bbb->getRatio(),
            'activated' => $bbb->isActivated(),
            'customUsernames' => $bbb->hasCustomUsernames(),
            'restrictions' => [
                'server' => $bbb->getServer(),
            ],
        ];
    }

    /**
     * @param array $data
     * @param BBB   $bbb
     *
     * @return BBB
     */
    public function deserialize($data, BBB $bbb)
    {
        $this->sipe('welcomeMessage', 'setWelcomeMessage', $data, $bbb);
        $this->sipe('endMessage', 'setEndMessage', $data, $bbb);
        $this->sipe('newTab', 'setNewTab', $data, $bbb);
        $this->sipe('moderatorRequired', 'setModeratorRequired', $data, $bbb);
        $this->sipe('record', 'setRecord', $data, $bbb);
        $this->sipe('ratio', 'setRatio', $data, $bbb);
        $this->sipe('activated', 'setActivated', $data, $bbb);
        $this->sipe('customUsernames', 'setCustomUsernames', $data, $bbb);
        $this->sipe('restrictions.server', 'setServer', $data, $bbb);

        return $bbb;
    }
}
