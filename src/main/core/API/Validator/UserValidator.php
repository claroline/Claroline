<?php

namespace Claroline\CoreBundle\API\Validator;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Utils\ArrayUtils;
use Claroline\AppBundle\API\ValidatorInterface;
use Claroline\AppBundle\API\ValidatorProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\ProfileSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\UserManager;

class UserValidator implements ValidatorInterface
{
    /** @var ObjectManager */
    private $om;
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var UserManager */
    private $manager;
    /** @var ProfileSerializer */
    private $profileSerializer;

    public function __construct(
        ObjectManager $om,
        PlatformConfigurationHandler $config,
        UserManager $manager,
        ProfileSerializer $profileSerializer
    ) {
        $this->om = $om;
        $this->config = $config;
        $this->manager = $manager;
        $this->profileSerializer = $profileSerializer;
    }

    public function validate($data, $mode, array $options = [])
    {
        $errors = [];

        // implements something cleaner later
        if (ValidatorProvider::UPDATE === $mode && !isset($data['id'])) {
            return $errors;
        }

        if (ValidatorProvider::CREATE === $mode) {
            // check the platform user limit
            $restrictions = $this->config->getParameter('restrictions') ?? [];
            if (isset($restrictions['users']) && isset($restrictions['max_users']) && $restrictions['users'] && $restrictions['max_users']) {
                $usersCount = $this->manager->countEnabledUsers();
                if ($usersCount >= $restrictions['max_users']) {
                    $errors[] = [
                        'path' => '',
                        'message' => 'The user limit of the platform has been reached.',
                    ];
                }
            }
        }

        // validate username format
        $regex = $this->config->getParameter('username_regex');
        if ($regex && !preg_match($regex, $data['username'])) {
            $errors[] = [
                'path' => 'username',
                'message' => 'The username '.$data['username'].' contains illegal characters.',
            ];
        }

        // checks username is available
        if (isset($data['username']) && $this->exists('username', $data['username'], isset($data['id']) ? $data['id'] : null)) {
            $errors[] = [
                'path' => 'username',
                'message' => 'The username '.$data['username'].' already exists.',
            ];
        }

        // check email is not already used
        if (isset($data['email']) && $this->exists('email', $data['email'], isset($data['id']) ? $data['id'] : null)) {
            $errors[] = [
                'path' => 'email',
                'message' => 'The email '.$data['email'].' already exists.',
            ];
        }

        // check public url is not already used
        if (isset($data['meta']) && isset($data['meta']['publicUrl'])) {
            if ($this->exists('publicUrl', $data['meta']['publicUrl'], isset($data['id']) ? $data['id'] : null)) {
                $errors[] = [
                  'path' => 'meta/publicUrl',
                  'message' => 'The public url '.$data['meta']['publicUrl'].' already exists.',
              ];
            }
        }

        // check if the administrative code is unique if the platform is configured to
        if (isset($data['administrativeCode']) && $this->config->getParameter('is_user_admin_code_unique')) {
            if ($this->exists('publicUrl', $data['administrativeCode'], isset($data['id']) ? $data['id'] : null)) {
                $errors[] = [
                    'path' => 'meta/publicUrl',
                    'message' => 'The administrative code '.$data['administrativeCode'].' already exists.',
                ];
            }
        }

        // todo validate Facet values
        if (in_array(Options::VALIDATE_FACET, $options)) {
            $facets = $this->profileSerializer->serialize([Options::REGISTRATION]);
            $required = [];

            foreach ($facets as $facet) {
                foreach ($facet['sections'] as $section) {
                    foreach ($section['fields'] as $field) {
                        if ($field['required']) {
                            $required[] = $field;
                        }
                    }
                }
            }

            foreach ($required as $field) {
                if (!ArrayUtils::has($data, 'profile.'.$field['id'])) {
                    $errors[] = [
                        'path' => 'profile/'.$field['id'],
                        'message' => 'The field '.$field['label'].' is required',
                    ];
                }
            }
        }

        return $errors;
    }

    /**
     * Check if a user exists with the given data.
     *
     * @param string      $propName
     * @param string      $propValue
     * @param string|null $userId
     *
     * @return bool
     */
    private function exists($propName, $propValue, $userId = null)
    {
        $qb = $this->om->createQueryBuilder();
        $qb
            ->select('COUNT(DISTINCT user)')
            ->from('Claroline\CoreBundle\Entity\User', 'user')
            ->where('user.'.$propName.' = :value')
            ->setParameter('value', $propValue);

        if (isset($userId)) {
            $parameter = is_numeric($userId) ? 'id' : 'uuid';
            $qb->andWhere("user.{$parameter} != :{$parameter}")->setParameter($parameter, $userId);
        }

        return 0 < $qb->getQuery()->getSingleScalarResult();
    }

    public static function getClass(): string
    {
        return User::class;
    }

    public function getUniqueFields()
    {
        return [
            'username' => 'username',
            'email' => 'email',
        ];
    }
}
