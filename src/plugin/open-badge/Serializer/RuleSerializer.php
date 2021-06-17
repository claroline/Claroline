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

    /** @var ObjectManager */
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function getName()
    {
        return 'open_badge_rule';
    }

    public function getClass()
    {
        return Rule::class;
    }

    public function serialize(Rule $rule, array $options = [])
    {
        return [
            'id' => $rule->getUuid(),
            'data' => $rule->getData(),
            'type' => $rule->getAction(),
        ];
    }

    /**
     * Deserializes data into a Rule entity.
     *
     * @param array $data
     * @param Rule  $rule
     *
     * @return Rule
     */
    public function deserialize($data, Rule $rule = null, array $options = [])
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
            case Rule::RESOURCE_PASSED:
            case Rule::RESOURCE_PARTICIPATED:
                $rule->setResourceNode($this->om->getObject($data['data'], ResourceNode::class));
                $rule->setResourceNode($this->om->getObject($data['data'], ResourceNode::class));
                break;
            case Rule::WORKSPACE_PASSED:
                $rule->setWorkspace($this->om->getObject($data['data'], Workspace::class));
                break;
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
