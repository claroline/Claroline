<?php

namespace Claroline\AudioPlayerBundle\Serializer\Resource;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AudioPlayerBundle\Entity\Resource\AudioParams;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.audio.params")
 * @DI\Tag("claroline.serializer")
 */
class AudioParamsSerializer
{
    use SerializerTrait;

    /**
     * @param AudioParams $audioParams
     * @param array       $options
     *
     * @return array
     */
    public function serialize(AudioParams $audioParams, array $options = [])
    {
        return [
            'id' => $audioParams->getUuid(),
            'sectionsType' => $audioParams->getSectionsType(),
            'rateControl' => $audioParams->getRateControl(),
        ];
    }

    /**
     * @param array       $data
     * @param AudioParams $audioParams
     * @param array       $options
     *
     * @return AudioParams
     */
    public function deserialize($data, AudioParams $audioParams, array $options = [])
    {
        $this->sipe('sectionsType', 'setSectionsType', $data, $audioParams);
        $this->sipe('rateControl', 'setRateControl', $data, $audioParams);

        return $audioParams;
    }
}
