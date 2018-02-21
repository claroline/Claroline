<?php

namespace Claroline\CoreBundle\API\Serializer\Contact;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\Contact\Options;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.contact_options")
 * @DI\Tag("claroline.serializer")
 */
class OptionsSerializer
{
    private $userSerializer;

    private $optionsRepo;
    private $userRepo;

    /**
     * OptionsSerializer constructor.
     *
     * @DI\InjectParams({
     *     "userSerializer" = @DI\Inject("claroline.serializer.user"),
     *     "om"             = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param UserSerializer $userSerializer
     * @param ObjectManager  $om
     */
    public function __construct(UserSerializer $userSerializer, ObjectManager $om)
    {
        $this->userSerializer = $userSerializer;

        $this->optionsRepo = $om->getRepository('Claroline\CoreBundle\Entity\Contact\Options');
        $this->userRepo = $om->getRepository('Claroline\CoreBundle\Entity\User');
    }

    /**
     * @param Options $options
     *
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
     * @param array        $data
     * @param Options|null $options
     *
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
        return 'Claroline\CoreBundle\Entity\Contact\Options';
    }
}
