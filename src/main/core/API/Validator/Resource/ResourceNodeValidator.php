<?php

namespace Claroline\CoreBundle\API\Validator\Resource;

use Claroline\AppBundle\API\ValidatorInterface;
use Claroline\AppBundle\API\ValidatorProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;

class ResourceNodeValidator implements ValidatorInterface
{
    public function __construct(
        private readonly ObjectManager $om
    ) {
    }

    public static function getClass(): string
    {
        return ResourceNode::class;
    }

    public function getUniqueFields(): array
    {
        return [];
    }

    public function validate(array $data, string $mode, array $options = []): array
    {
        $errors = [];

        // implements something cleaner later
        if (ValidatorProvider::UPDATE === $mode && !isset($data['id'])) {
            return $errors;
        }

        if (!empty($data['name'])) {
            if (str_contains($data['name'], ResourceNode::PATH_SEPARATOR)) {
                $errors[] = [
                    'path' => 'name',
                    'message' => sprintf('Invalid character "%s".', ResourceNode::PATH_SEPARATOR),
                ];
            }
        }

        // validates the resource type exists
        if (isset($data['meta']) && isset($data['meta']['type'])) {
            $resourceType = $this->om
                ->getRepository(ResourceType::class)
                ->findOneBy(['name' => $data['meta']['type']]);

            if (!$resourceType) {
                $errors[] = [
                    'path' => 'meta/type',
                    'message' => sprintf('The type %s does not exist.', $data['meta']['type']),
                ];
            }
        }

        return $errors;
    }
}
