<?php

namespace UJM\ExoBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\User;
use UJM\ExoBundle\Library\Options\Transfer;

/**
 * Serializer for user data.
 *
 * @todo : use standard core serializer
 */
class UserSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;

    /** @var PublicFileSerializer */
    private $fileSerializer;

    /**
     * UserSerializer constructor.
     */
    public function __construct(ObjectManager $om, PublicFileSerializer $fileSerializer)
    {
        $this->om = $om;
        $this->fileSerializer = $fileSerializer;
    }

    public function getName()
    {
        return 'exo_user';
    }

    /**
     * Converts a User into a JSON-encodable structure.
     *
     * @return array
     */
    public function serialize(User $user, array $options = [])
    {
        $serialized = [
            'id' => $user->getUuid(),
            'name' => trim($user->getFirstName().' '.$user->getLastName()),
            'picture' => $this->serializePicture($user),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'username' => $user->getUsername(),
        ];

        if (!in_array(Transfer::MINIMAL, $options)) {
            $serialized['email'] = $user->getEmail();
        }

        return $serialized;
    }

    /**
     * Converts raw data into a User entity.
     *
     * Note : we don't allow to update users here, so this method only returns the untouched entity instance.
     *
     * @param \array $data
     * @param User   $user
     *
     * @return User
     */
    public function deserialize($data, User $user = null, array $options = [])
    {
        if (empty($user)) {
            /** @var User $user */
            $user = $this->om->getRepository(User::class)->findOneBy(['uuid' => $data['id']]);
        }

        return $user;
    }

    /**
     * Serialize the user picture.
     *
     * @return array|null
     */
    private function serializePicture(User $user)
    {
        if (!empty($user->getPicture())) {
            /** @var PublicFile $file */
            $file = $this->om
                ->getRepository(PublicFile::class)
                ->findOneBy(['url' => $user->getPicture()]);

            if ($file) {
                return $this->fileSerializer->serialize($file);
            }
        }

        return null;
    }
}
