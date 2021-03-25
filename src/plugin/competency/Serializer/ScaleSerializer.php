<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HeVinci\CompetencyBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\Level;
use HeVinci\CompetencyBundle\Entity\Scale;

class ScaleSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;

    private $competencyRepo;
    private $levelRepo;
    private $scaleRepo;

    /**
     * ScaleSerializer constructor.
     *
     * @param SerializerProvider $serializer
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;

        $this->competencyRepo = $om->getRepository(Competency::class);
        $this->levelRepo = $om->getRepository(Level::class);
        $this->scaleRepo = $om->getRepository(Scale::class);
    }

    public function getName()
    {
        return 'competency_scale';
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/competency/scale.json';
    }

    /**
     * @return array
     */
    public function serialize(Scale $scale, array $options = [])
    {
        $serialized = [
            'id' => $scale->getUuid(),
            'name' => $scale->getName(),
            'levels' => array_map(function (Level $level) {
                return [
                    'id' => $level->getUuid(),
                    'value' => $level->getName(),
                ];
            }, $scale->getLevels()->toArray()),
        ];

        return $serialized;
    }

    /**
     * @param array $data
     *
     * @return Scale
     */
    public function deserialize($data, Scale $scale)
    {
        $this->sipe('id', 'setUuid', $data, $scale);
        $this->sipe('name', 'setName', $data, $scale);

        if (isset($data['levels'])) {
            $scale->emptyLevels();

            foreach ($data['levels'] as $key => $levelData) {
                $level = $this->levelRepo->findOneBy(['uuid' => $levelData['id']]);

                if (!$level) {
                    $level = new Level();
                    $level->setUuid($levelData['id']);
                }
                $level->setScale($scale);
                $level->setName($levelData['value']);
                $level->setValue($key);
                $this->om->persist($level);
                $scale->addLevel($level);
            }
        }

        return $scale;
    }
}
