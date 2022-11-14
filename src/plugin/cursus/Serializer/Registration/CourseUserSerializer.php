<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Serializer\Registration;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CursusBundle\Entity\Registration\AbstractUserRegistration;
use Claroline\CursusBundle\Entity\Registration\CourseUser;
use Claroline\CursusBundle\Serializer\CourseSerializer;

class CourseUserSerializer extends AbstractUserSerializer
{
    use SerializerTrait;

    /** @var CourseSerializer */
    private $courseSerializer;

    public function __construct(UserSerializer $userSerializer, CourseSerializer $courseSerializer)
    {
        parent::__construct($userSerializer);

        $this->courseSerializer = $courseSerializer;
    }

    public function getClass()
    {
        return CourseUser::class;
    }

    /**
     * @param CourseUser $courseUser
     */
    public function serialize(AbstractUserRegistration $courseUser, array $options = []): array
    {
        return array_merge(parent::serialize($courseUser, $options), [
            'course' => $this->courseSerializer->serialize($courseUser->getCourse(), [Options::SERIALIZE_MINIMAL]),
        ]);
    }
}
