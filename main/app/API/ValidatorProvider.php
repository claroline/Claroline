<?php

namespace Claroline\AppBundle\API;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use JMS\DiExtraBundle\Annotation as DI;
use JVal\Validator;

/**
 * @DI\Service("claroline.api.validator")
 */
class ValidatorProvider
{
    /** @var string */
    const CREATE = 'create';
    /** @var string */
    const UPDATE = 'update';
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;

    /**
     * The list of registered validators in the platform.
     *
     * @var array
     */
    private $validators = [];

    /**
     * GroupValidator constructor.
     *
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer" = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param ObjectManager      $om
     * @param SerializerProvider $serializer
     */
    public function __construct(ObjectManager $om, SerializerProvider $serializer)
    {
        $this->om = $om;
        $this->serializer = $serializer;
    }

    /**
     * Registers a new validator.
     *
     * @param ValidatorInterface $validator
     */
    public function add(ValidatorInterface $validator)
    {
        $this->validators[$validator->getClass()] = $validator;
    }

    /**
     * Gets a registered validator instance.
     *
     * @param string $class
     *
     * @return ValidatorInterface
     *
     * @throws \Exception
     */
    public function get($class)
    {
        if (empty($this->validators[$class])) {
            throw new \Exception(
                sprintf('No validator found for class "%s" Maybe you forgot to add the "claroline.validator" tag to your validator.', $class)
            );
        }

        return $this->validators[$class];
    }

    /**
     * Checks if the provider has a registered validator for `class`.
     *
     * @param string $class
     *
     * @return bool
     */
    public function has($class)
    {
        return !empty($this->validators[$class]);
    }

    /**
     * Validates `data` using the `class` validator.
     *
     * @param string $class          - the class of the validator to use
     * @param mixed  $data           - the data to validate
     * @param string $mode           - 'create' or 'update'
     * @param bool   $throwException - if true an InvalidDataException is thrown instead of returning the errors
     *
     * @return array - the list of validation errors
     *
     * @throws InvalidDataException
     */
    public function validate($class, $data, $mode, $throwException = false)
    {
        $schema = $this->serializer->getSchema($class);

        //schema isn't always there yet
        if ($schema) {
            $validator = Validator::buildDefault();
            $errors = $validator->validate($this->toObject($data), $schema/*, 3rd param for uri resolution*/);

            if (!empty($errors) && $throwException) {
                throw new InvalidDataException(
                    sprintf('Invalid data for "%s".', $class),
                    $errors
                );
            }

            if (count($errors) > 0) {
                return $errors;
            }
        }

        //validate uniques
        try {
            $validator = $this->get($class);
        } catch (\Exception $e) {
            //no custom validator
            $uniqueFields = [];
            $identifiers = $this->serializer->getIdentifiers($class);

            foreach ($identifiers as $identifier) {
                $uniqueFields[$identifier] = $identifier;
            }

            return $this->validateUnique($uniqueFields, $data, $mode, $class);
        }

        //can be deduced from the mapping, but we won't know
        //wich field is related to wich data prop in that case
        $uniqueFields = $validator->getUniqueFields();
        $errors = $this->validateUnique($uniqueFields, $data, $mode, $class);

        //custom validation
        $errors = array_merge($errors, $validator->validate($data));

        if (!empty($errors) && $throwException) {
            throw new InvalidDataException(
                sprintf('Invalid data for "%s".', $class),
                $errors
            );
        }

        return $errors;
    }

    /**
     * @param array $data
     *
     * @return \stdClass
     */
    public function toObject(array $data)
    {
        return json_decode(json_encode($data));
    }

    //only if uniqueFields in data
    private function validateUnique(array $uniqueFields, array $data, $mode, $class)
    {
        $errors = [];

        foreach ($uniqueFields as $dataProp => $entityProp) {
            if (isset($data[$dataProp])) {
                $qb = $this->om->createQueryBuilder();

                $qb->select('DISTINCT o')
               ->from($class, 'o')
               ->where("o.{$entityProp} LIKE :{$entityProp}")
               ->setParameter($entityProp, $data[$dataProp]);

                if (self::UPDATE === $mode) {
                    $qb->setParameter('uuid', $data['id'])
                   ->andWhere('o.uuid != :uuid');
                }

                $objects = $qb->getQuery()->getResult();

                if (count($objects) > 0) {
                    $errors[] = ['path' => $dataProp, 'message' => "{$entityProp} already exists and should be unique"];
                }
            }
        }

        return $errors;
    }
}
