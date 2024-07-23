<?php

namespace Claroline\OpenBadgeBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;

class RuleSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly ObjectManager $om
    ) {
    }

    public function getName(): string
    {
        return 'open_badge_rule';
    }

    public function getClass(): string
    {
        return Rule::class;
    }

    public function serialize(Rule $rule, array $options = []): array
    {
        return [
            'id' => $rule->getUuid(),
            'data' => $rule->getData(),
            'type' => $rule->getAction(),
        ];
    }

    public function deserialize(array $data, Rule $rule = null, array $options = []): Rule
    {
        $rule->setData($data['data']);
        $rule->setAction($data['type']);

        if (isset($data['data']['workspace'])) {
            $rule->setWorkspace($this->om->getObject($data['data']['workspace'], Workspace::class));
        }

        if (isset($data['data']['resource'])) {
            $rule->setResourceNode($this->om->getObject($data['data']['resource'], ResourceNode::class));
        }

        switch ($data['type']) {
            case Rule::IN_GROUP:
                $rule->setGroup($this->om->getObject($data['data'], Group::class));
                break;
            case Rule::IN_ROLE:
                $rule->setRole($this->om->getObject($data['data'], Role::class));
                break;
        }

        return $rule;
    }
}
