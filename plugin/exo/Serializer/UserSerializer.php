<?php

namespace UJM\ExoBundle\Serializer;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Serializer\AbstractSerializer;

/**
 * Serializer for user data.
 *
 * @DI\Service("ujm_exo.serializer.user")
 *
 * @todo : use standard core serializer
 */
class UserSerializer extends AbstractSerializer
{
    /** @var ObjectManager */
    private $om;

    /** @var PublicFileSerializer */
    private $fileSerializer;

    /**
     * UserSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "fileSerializer" = @DI\Inject("claroline.serializer.public_file")
     * })
     *
     * @param ObjectManager        $om
     * @param PublicFileSerializer $fileSerializer
     */
    public function __construct(
        ObjectManager $om,
        PublicFileSerializer $fileSerializer)
    {
        $this->om = $om;
        $this->fileSerializer = $fileSerializer;
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

        $userData->id = $user->getUuid();
        $userData->name = trim($user->getFirstName().' '.$user->getLastName());

        $userData->picture = $this->serializePicture($user);
        $userData->firstName = $user->getFirstName();
        $userData->lastName = $user->getLastName();
        $userData->username = $user->getUsername();
        $userData->publicUrl = $user->getPublicUrl();

        if (!$this->hasOption(Transfer::MINIMAL, $options)) {
            $userData->email = $user->getEmail();
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
            /** @var User $user */
            $user = $this->om->getRepository('ClarolineCoreBundle:User')->find($data->id);
        }

        return $user;
    }

    /**
     * Serialize the user picture.
     *
     * @param User $user
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
