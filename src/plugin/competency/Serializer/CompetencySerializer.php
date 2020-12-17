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

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\CompetencyAbility;
use HeVinci\CompetencyBundle\Entity\Scale;

class CompetencySerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    private $competencyRepo;
    private $scaleRepo;

    /**
     * CompetencySerializer constructor.
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om, CompetencyAbilitySerializer $competencyAbilitySerializer, ScaleSerializer $scaleSerializer)
    {
        $this->om = $om;
        $this->competencyAbilitySerializer = $competencyAbilitySerializer;
        $this->scaleSerializer = $scaleSerializer;

        $this->competencyRepo = $om->getRepository(Competency::class);
        $this->scaleRepo = $om->getRepository(Scale::class);
    }

    public function getName()
    {
        return 'competency';
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/competency/competency.json';
    }

    /**
     * @param Competency $competency
     * @param array      $options
     *
     * @return array
     */
    public function serialize(Competency $competency, array $options = [])
    {
        $serialized = [
            'id' => $competency->getUuid(),
            'name' => $competency->getName(),
            'description' => $competency->getDescription(),
            'parent' => $competency->getParent() ? $this->serialize($competency->getParent(), [Options::SERIALIZE_MINIMAL]) : null,
            'scale' => $this->scaleSerializer->serialize($competency->getScale(), [Options::SERIALIZE_MINIMAL]),
            'abilities' => array_map(function (CompetencyAbility $competencyAbility) {
                return $this->competencyAbilitySerializer->serialize($competencyAbility, [Options::SERIALIZE_MINIMAL]);
            }, $competency->getCompetencyAbilities()->toArray()),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'meta' => [
                    'resourceCount' => $competency->getResourceCount(),
                ],
                'structure' => [
                    'root' => $competency->getRoot(),
                    'lvl' => $competency->getLevel(),
                    'lft' => $competency->getLeft(),
                    'rgt' => $competency->getRight(),
                ],
            ]);
        }
        if (in_array(Options::IS_RECURSIVE, $options)) {
            $serialized['children'] = array_map(function (Competency $child) use ($options) {
                return $this->serialize($child, $options);
            }, $competency->getChildren()->toArray());
        }

        return $serialized;
    }

    /**
     * @param array      $data
     * @param Competency $competency
     *
     * @return Competency
     */
    public function deserialize($data, Competency $competency)
    {
        $this->sipe('id', 'setUuid', $data, $competency);
        $this->sipe('name', 'setName', $data, $competency);
        $this->sipe('description', 'setDescription', $data, $competency);

        $parent = isset($data['parent']['id']) ?
            $this->competencyRepo->findOneBy(['uuid' => $data['parent']['id']]) :
            null;
        $competency->setParent($parent);

        if ($parent) {
            $scale = $parent->getScale();
        } else {
            $scale = isset($data['scale']['id']) ?
                $this->scaleRepo->findOneBy(['uuid' => $data['scale']['id']]) :
                null;
        }
        $competency->setScale($scale);

        return $competency;
    }
}
