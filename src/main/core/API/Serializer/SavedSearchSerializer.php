<?php

namespace Claroline\CoreBundle\API\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\SavedSearch;
use Claroline\CoreBundle\Entity\User;

class SavedSearchSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var UserSerializer */
    private $userSerializer;

    /**
     * SavedSearchSerializer constructor.
     */
    public function __construct(ObjectManager $om, UserSerializer $userSerializer)
    {
        $this->om = $om;
        $this->userSerializer = $userSerializer;
    }

    public function getClass()
    {
        return SavedSearch::class;
    }

    public function getName()
    {
        return 'saved_search';
    }

    /**
     * Serializes a SavedSearch entity.
     *
     * @return array
     */
    public function serialize(SavedSearch $savedSearch, array $options = [])
    {
        return [
            'id' => $savedSearch->getUuid(),
            'list' => $savedSearch->getList(),
            'filters' => $savedSearch->getFilters(),
            'user' => $savedSearch->getUser() ? $this->userSerializer->serialize($savedSearch->getUser(), [Options::SERIALIZE_MINIMAL]) : null,
        ];
    }

    /**
     * Deserializes data into a SavedSearch entity.
     *
     * @param array $data
     *
     * @return SavedSearch
     */
    public function deserialize($data, SavedSearch $savedSearch, array $options = [])
    {
        $this->sipe('id', 'setUuid', $data, $savedSearch);
        $this->sipe('list', 'setList', $data, $savedSearch);
        $this->sipe('filters', 'setFilters', $data, $savedSearch);

        if (isset($data['user'])) {
            /** @var User $user */
            $user = $this->om->getObject($data['user'], User::class);
            if ($user) {
                $savedSearch->setUser($user);
            }
        }

        return $savedSearch;
    }
}
