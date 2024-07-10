<?php

namespace Claroline\EvaluationBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\EvaluationBundle\Entity\Skill\Ability;
use Claroline\EvaluationBundle\Entity\Skill\Skill;
use Claroline\EvaluationBundle\Entity\Skill\SkillsFramework;

class SkillsFrameworkSerializer
{
    use SerializerTrait;

    public function getName(): string
    {
        return 'skills_framework';
    }

    public function getClass(): string
    {
        return SkillsFramework::class;
    }

    public function getSchema(): string
    {
        return '#/main/evaluation/skills-framework.json';
    }

    public function serialize(SkillsFramework $skillsFramework, ?array $options = []): array
    {
        $serialized = [
            'id' => $skillsFramework->getUuid(),
            'name' => $skillsFramework->getName(),
            'description' => $skillsFramework->getDescription(),
        ];

        if (!in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            $serialized['permissions'] = [
                'administrate' => true,
            ];

            $serialized['skills'] = [];
            $skills = $skillsFramework->getSkills();
            foreach ($skills as $skill) {
                $serialized['skills'][] = $this->serializeSkill($skill);
            }
        }

        return $serialized;
    }

    public function deserialize(array $data, SkillsFramework $skillsFramework, ?array $options = []): SkillsFramework
    {
        if (!in_array(SerializerInterface::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $skillsFramework);
        } else {
            $skillsFramework->refreshUuid();
        }

        $this->sipe('name', 'setName', $data, $skillsFramework);
        $this->sipe('description', 'setDescription', $data, $skillsFramework);

        if (array_key_exists('skills', $data)) {
            $currentSkills = $skillsFramework->getSkills()->toArray();
            $ids = [];

            foreach ($data['skills'] as $skillIndex => $skillData) {
                if ($skillData['id']) {
                    $skill = $skillsFramework->getSkill($skillData['id']);
                }

                if (empty($skill)) {
                    $skill = new Skill();
                    $skillsFramework->addSkill($skill);
                }

                $skill->setOrder($skillIndex);
                $this->deserializeSkill($skillData, $skill, $options);
                $ids[] = $skill->getUuid();
            }

            // removes skills which no longer exists
            foreach ($currentSkills as $currentSkill) {
                if (empty($currentSkill->getParent()) && !in_array($currentSkill->getUuid(), $ids)) {
                    $skillsFramework->removeSkill($currentSkill);
                }
            }
        }

        return $skillsFramework;
    }

    private function serializeSkill(Skill $skill): array
    {
        $serialized = [
            'id' => $skill->getUuid(),
            'description' => $skill->getDescription(),
        ];

        if (!empty($skill->getChildren())) {
            $serialized['children'] = [];
            $skills = $skill->getChildren();
            foreach ($skills as $skill) {
                $serialized['children'][] = $this->serializeSkill($skill);
            }
        }

        if (!empty($skill->getAbilities())) {
            $serialized['abilities'] = [];
            $abilities = $skill->getAbilities();
            foreach ($abilities as $ability) {
                $serialized['abilities'][] = [
                    'id' => $ability->getUuid(),
                    'description' => $ability->getDescription(),
                ];
            }
        }

        return $serialized;
    }

    private function deserializeSkill(array $data, Skill $skill, ?array $options = []): Skill
    {
        if (!in_array(SerializerInterface::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $skill);
        } else {
            $skill->refreshUuid();
        }

        $this->sipe('description', 'setDescription', $data, $skill);

        if (array_key_exists('abilities', $data)) {
            $currentAbilities = $skill->getAbilities()->toArray();
            $ids = [];

            foreach ($data['abilities'] as $abilityIndex => $abilityData) {
                if ($abilityData['id']) {
                    $ability = $skill->getAbility($abilityData['id']);
                }

                if (empty($ability)) {
                    $ability = new Ability();
                    $skill->addAbility($ability);
                }

                $ability->setOrder($abilityIndex);
                $this->deserializeAbility($abilityData, $ability, $options);
                $ids[] = $ability->getUuid();
            }

            // removes skills which no longer exists
            foreach ($currentAbilities as $currentAbility) {
                if (!in_array($currentAbility->getUuid(), $ids)) {
                    $skill->removeAbility($currentAbility);
                }
            }
        }

        if (array_key_exists('children', $data)) {
            $currentSkills = $skill->getChildren()->toArray();
            $ids = [];

            foreach ($data['children'] as $skillIndex => $skillData) {
                if ($skillData['id']) {
                    $child = $skill->getChild($skillData['id']);
                }

                if (empty($child)) {
                    $child = new Skill();
                    $skill->addChild($child);
                }

                $child->setOrder($skillIndex);
                $this->deserializeSkill($skillData, $child, $options);
                $ids[] = $child->getUuid();
            }

            // removes skills which no longer exists
            foreach ($currentSkills as $currentSkill) {
                if (!in_array($currentSkill->getUuid(), $ids)) {
                    $skill->removeChild($currentSkill);
                }
            }
        }

        return $skill;
    }

    private function deserializeAbility(array $data, Ability $ability, ?array $options = []): Ability
    {
        if (!in_array(SerializerInterface::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $ability);
        } else {
            $ability->refreshUuid();
        }

        $this->sipe('description', 'setDescription', $data, $ability);

        return $ability;
    }
}
