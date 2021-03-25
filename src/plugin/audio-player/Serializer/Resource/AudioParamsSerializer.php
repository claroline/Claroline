<?php

namespace Claroline\AudioPlayerBundle\Serializer\Resource;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AudioPlayerBundle\Entity\Resource\AudioParams;

class AudioParamsSerializer
{
    use SerializerTrait;

    /**
     * @return array
     */
    public function serialize(AudioParams $audioParams, array $options = [])
    {
        return [
            'id' => $audioParams->getUuid(),
            'sectionsType' => $audioParams->getSectionsType(),
            'rateControl' => $audioParams->getRateControl(),
            'description' => $audioParams->getDescription(),
        ];
    }

    /**
     * @param array $data
     *
     * @return AudioParams
     */
    public function deserialize($data, AudioParams $audioParams, array $options = [])
    {
        $this->sipe('sectionsType', 'setSectionsType', $data, $audioParams);
        $this->sipe('rateControl', 'setRateControl', $data, $audioParams);
        $this->sipe('description', 'setDescription', $data, $audioParams);

        return $audioParams;
    }
}
