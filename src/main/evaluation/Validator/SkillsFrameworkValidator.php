<?php

namespace Claroline\EvaluationBundle\Validator;

use Claroline\AppBundle\API\ValidatorInterface;
use Claroline\EvaluationBundle\Entity\SkillsFramework;

class SkillsFrameworkValidator implements ValidatorInterface
{
    public static function getClass(): string
    {
        return SkillsFramework::class;
    }

    public function validate(array $data, string $mode, array $options = []): array
    {
        // TODO : check a skill with children don't have abilities

        return [];
    }

    public function getUniqueFields(): array
    {
        return [];
    }
}
