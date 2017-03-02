<?php

namespace UJM\ExoBundle\Serializer;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Serializer\AbstractSerializer;

/**
 * Serializer for user data.
 *
 * @DI\Service("ujm_exo.serializer.user")
 */
class UserSerializer extends AbstractSerializer
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * UserSerializer constructor.
     *
     * @param ObjectManager $om
     *
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Converts a User into a JSON-encodable structure.
     *
     * @param User  $user
     * @param array $options
     *
     * @return \stdClass
     */
    public function serialize($user, array $options = [])
    {
        $userData = new \stdClass();

        $userData->id = (string) $user->getId();
        $userData->name = trim($user->getFirstName().' '.$user->getLastName());

        if (!$this->hasOption(Transfer::MINIMAL, $options)) {
            $userData->email = $user->getMail();
        }

        return $userData;
    }

    /**
     * Converts raw data into a User entity.
     *
     * Note : we don't allow to update users here, so this method only returns the untouched entity instance.
     *
     * @param \stdClass $data
     * @param null      $user
     * @param array     $options
     *
     * @return User
     */
    public function deserialize($data, $user = null, array $options = [])
    {
        if (empty($user)) {
            $user = $this->om->getRepository('ClarolineCoreBundle:User')->find($data->id);
        }

        return $user;
    }
}
