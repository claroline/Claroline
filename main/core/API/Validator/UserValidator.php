<?php

namespace Claroline\CoreBundle\API\Validator;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Utils\ArrayUtils;
use Claroline\AppBundle\API\ValidatorInterface;
use Claroline\AppBundle\API\ValidatorProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\ProfileSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\UserRepository;
use  Doctrine\ORM\QueryBuilder;

class UserValidator implements ValidatorInterface
{
    /** @var ObjectManager */
    private $om;
    /** @var ProfileSerializer */
    private $profileSerializer;
    /** @var UserRepository */
    private $repo;

    /**
     * UserValidator constructor.
     *
     * @param ObjectManager     $om
     * @param ProfileSerializer $profileSerializer
     */
    public function __construct(ObjectManager $om, ProfileSerializer $profileSerializer)
    {
        $this->om = $om;
        $this->repo = $this->om->getRepository(User::class);
        $this->profileSerializer = $profileSerializer;
    }

    public function validate($data, $mode, array $options = [])
    {
        $errors = [];

        // implements something cleaner later
        if (ValidatorProvider::UPDATE === $mode && !isset($data['id'])) {
            return $errors;
        }

        // todo validate Facet values
        //the big chunk of code allows us to know if the identifiers are already taken
        //and prohibits the use of an already used address email in a username field

        if ($this->exists('username', $data['username'], isset($data['id']) ? $data['id'] : null)) {
            $errors[] = [
                'path' => 'username',
                'message' => 'The username '.$data['username'].' already exists.',
            ];
        }

        if ($this->exists('email', $data['email'], isset($data['id']) ? $data['id'] : null)) {
            $errors[] = [
                'path' => 'email',
                'message' => 'The email '.$data['email'].' already exists.',
            ];
        }

        if (isset($data['meta']) && isset($data['meta']['publicUrl'])) {
            if ($this->exists('publicUrl', $data['meta']['publicUrl'], isset($data['id']) ? $data['id'] : null)) {
                $errors[] = [
                  'path' => 'meta/publicUrl',
                  'message' => 'The public url '.$data['meta']['publicUrl'].' already exists.',
              ];
            }
        }

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

            $utils = new ArrayUtils();
            foreach ($required as $field) {
                if (!$utils->has($data, 'profile.'.$field['id'])) {
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
        /** @var QueryBuilder $qb */
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

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\User';
    }

    public function getUniqueFields()
    {
        return [
            'username' => 'username',
            'email' => 'email',
        ];
    }
}
