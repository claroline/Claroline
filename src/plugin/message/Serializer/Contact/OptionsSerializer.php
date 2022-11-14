<?php

namespace Claroline\MessageBundle\Serializer\Contact;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\MessageBundle\Entity\Contact\Options;

class OptionsSerializer
{
    private $userSerializer;

    private $optionsRepo;
    private $userRepo;

    /**
     * OptionsSerializer constructor.
     */
    public function __construct(UserSerializer $userSerializer, ObjectManager $om)
    {
        $this->userSerializer = $userSerializer;

        $this->optionsRepo = $om->getRepository(Options::class);
        $this->userRepo = $om->getRepository(User::class);
    }

    public function getName()
    {
        return 'message_options';
    }

    /**
     * @return array
     */
    public function serialize(Options $options)
    {
        return [
            'id' => $options->getId(),
            'data' => $options->getOptions(),
            'user' => $this->userSerializer->serialize($options->getUser()),
        ];
    }

    /**
     * @return Options
     */
    public function deserialize(array $data, Options $options = null)
    {
        if (empty($options)) {
            $options = $this->optionsRepo->findOneBy(['id' => $data['id']]);
        }
        if (empty($options)) {
            $options = new Options();
        }
        if (isset($data['user'])) {
            /** @var User $user */
            $user = isset($data['user']['id']) ? $this->userRepo->findOneBy(['uuid' => $data['user']['id']]) : null;
            $options->setUser($user);
        }
        if (isset($data['data'])) {
            $options->setOptions($data['data']);
        }

        return $options;
    }

    public function getClass()
    {
        return Options::class;
    }
}
