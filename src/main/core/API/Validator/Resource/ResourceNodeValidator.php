<?php

namespace Claroline\CoreBundle\API\Validator\Resource;

use Claroline\AppBundle\API\ValidatorInterface;
use Claroline\AppBundle\API\ValidatorProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;

class ResourceNodeValidator implements ValidatorInterface
{
    /**
     * The char used by Gedmo\Tree extension to generate the path of the resource.
     * It cannot be used in the resource name.
     */
    private const PATH_SEPARATOR = '`';

    public function __construct(
        private readonly ObjectManager $om
    ) {
    }

    public static function getClass(): string
    {
        return ResourceNode::class;
    }

    public function getUniqueFields()
    {
        return [
            'code' => 'code',
        ];
    }

    /**
     * @param array  $data
     * @param string $mode
     */
    public function validate($data, $mode, array $options = []): array
    {
        $errors = [];

        // implements something cleaner later
        if (ValidatorProvider::UPDATE === $mode && !isset($data['id'])) {
            return $errors;
        }

        if (!empty($data['name'])) {
            if (str_contains($data['name'], self::PATH_SEPARATOR)) {
                $errors[] = [
                    'path' => 'name',
                    'message' => sprintf('Invalid character "%s".', self::PATH_SEPARATOR),
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
