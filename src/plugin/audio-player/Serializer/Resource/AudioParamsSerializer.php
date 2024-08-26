<?php

namespace Claroline\AudioPlayerBundle\Serializer\Resource;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AudioPlayerBundle\Entity\Resource\AudioParams;

class AudioParamsSerializer
{
    use SerializerTrait;

    public function getClass(): string
    {
        return AudioParams::class;
    }

    public function serialize(AudioParams $audioParams, array $options = []): array
    {
        return [
            'id' => $audioParams->getUuid(),
            'sectionsType' => $audioParams->getSectionsType(),
            'rateControl' => $audioParams->getRateControl(),
            'description' => $audioParams->getDescription(),
        ];
    }

    public function deserialize(array $data, AudioParams $audioParams, array $options = []): AudioParams
    {
        $this->sipe('sectionsType', 'setSectionsType', $data, $audioParams);
        $this->sipe('rateControl', 'setRateControl', $data, $audioParams);
        $this->sipe('description', 'setDescription', $data, $audioParams);

        return $audioParams;
    }
}
