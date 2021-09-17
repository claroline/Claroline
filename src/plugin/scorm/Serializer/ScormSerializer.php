<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ScormBundle\Entity\Sco;
use Claroline\ScormBundle\Entity\Scorm;

class ScormSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var ScoSerializer */
    private $scoSerializer;

    private $scoRepo;

    /**
     * ScormSerializer constructor.
     */
    public function __construct(ObjectManager $om, ScoSerializer $scoSerializer)
    {
        $this->om = $om;
        $this->scoSerializer = $scoSerializer;

        $this->scoRepo = $om->getRepository('Claroline\ScormBundle\Entity\Sco');
    }

    public function getName()
    {
        return 'scorm';
    }

    public function serialize(Scorm $scorm, array $options = []): array
    {
        $serialized = [
            'id' => $scorm->getUuid(),
            'version' => $scorm->getVersion(),
            'hashName' => $scorm->getHashName(),
            'ratio' => $scorm->getRatio(),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized['scos'] = $this->serializeScos($scorm);
        }

        return $serialized;
    }

    public function deserialize(array $data, Scorm $scorm, array $options = []): Scorm
    {
        $this->sipe('hashName', 'setHashName', $data, $scorm);
        $this->sipe('version', 'setVersion', $data, $scorm);
        $this->sipe('ratio', 'setRatio', $data, $scorm);

        if (isset($data['scos'])) {
            $existing = $scorm->getScos()->toArray();

            $updated = $this->deserializeScos($data['scos'], $scorm, null, $options);

            // clean removed scos
            foreach ($existing as $existingSco) {
                $found = false;
                foreach ($updated as $updatedSco) {
                    if ($existingSco->getId() === $updatedSco->getId()) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $scorm->removeSco($existingSco);
                }
            }
        }

        return $scorm;
    }

    private function serializeScos(Scorm $scorm)
    {
        return array_map(function (Sco $sco) {
            return $this->scoSerializer->serialize($sco);
        }, $scorm->getRootScos());
    }

    private function deserializeScos($data, Scorm $scorm, Sco $parent = null, array $options = []): array
    {
        $updated = [];

        foreach ($data as $scoData) {
            // search by identifier to be able to retrieve and update sco when we change scorm file
            $sco = $this->scoRepo->findOneBy([
                'scorm' => $scorm,
                'identifier' => $scoData['data']['identifier'],
            ]);

            if (empty($sco)) {
                $sco = new Sco();
                $scorm->addSco($sco);
            }

            $sco->setScoParent($parent);
            $sco->setScorm($scorm);

            $updated = array_merge($updated, $this->deserializeSco($scoData, $sco, $scorm, $options));
        }

        return $updated;
    }

    // TODO : move to ScoSerializer
    private function deserializeSco($data, Sco $sco, Scorm $scorm, array $options = []): array
    {
        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $sco);
        } else {
            $sco->refreshUuid();
        }

        $this->sipe('data.entryUrl', 'setEntryUrl', $data, $sco);
        $this->sipe('data.identifier', 'setIdentifier', $data, $sco);
        $this->sipe('data.title', 'setTitle', $data, $sco);
        $this->sipe('data.visible', 'setVisible', $data, $sco);
        $this->sipe('data.parameters', 'setParameters', $data, $sco);
        $this->sipe('data.launchData', 'setLaunchData', $data, $sco);
        $this->sipe('data.maxTimeAllowed', 'setMaxTimeAllowed', $data, $sco);
        $this->sipe('data.timeLimitAction', 'setTimeLimitAction', $data, $sco);
        $this->sipe('data.block', 'setBlock', $data, $sco);
        $this->sipe('data.scoreToPassInt', 'setScoreToPassInt', $data, $sco);
        $this->sipe('data.scoreToPassDecimal', 'setScoreToPassDecimal', $data, $sco);
        $this->sipe('data.completionThreshold', 'setCompletionThreshold', $data, $sco);
        $this->sipe('data.prerequisites', 'setPrerequisites', $data, $sco);

        if (isset($data['children']) && 0 < count($data['children'])) {
            return array_merge([$sco], $this->deserializeScos($data['children'], $scorm, $sco, $options));
        }

        return [$sco];
    }
}
