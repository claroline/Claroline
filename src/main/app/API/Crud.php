<?php

namespace Claroline\AppBundle\API;

use Claroline\AppBundle\Event\Crud\CrudEvent;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AppBundle\Security\ObjectCollection;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Provides common CRUD operations.
 */
class Crud
{
    use PermissionCheckerTrait;

    /** @var string */
    public const COLLECTION_ADD = 'add';
    /** @var string */
    public const COLLECTION_REMOVE = 'remove';
    /** @var string */
    public const PROPERTY_SET = 'set';
    // TODO : remove me. only for retro compatibility it should be always the case
    // but I don't know if it will break things if I do it now
    public const THROW_EXCEPTION = 'throw_exception';

    public const NO_PERMISSIONS = 'NO_PERMISSIONS';
    public const NO_VALIDATION = 'NO_VALIDATION';

    public function __construct(
        private readonly ObjectManager $om,
        private readonly StrictDispatcher $dispatcher,
        private readonly FinderProvider $finder,
        private readonly SerializerProvider $serializer,
        private readonly ValidatorProvider $validator,
        private readonly SchemaProvider $schema,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->authorization = $authorization;
    }

    public function get(string $class, mixed $id, string $idProp = 'id', ?array $options = []): ?object
    {
        $object = null;
        if ('id' === $idProp) {
            $object = $this->om->getRepository($class)->findOneBy(['uuid' => $id]);
        } else {
            $identifiers = $this->schema->getIdentifiers($class);
            if (!in_array($idProp, $identifiers)) {
                throw new \LogicException(sprintf('You can only get entities with an identifier property (identifiers: %s).', implode(', ', $identifiers)));
            }

            $object = $this->om->getRepository($class)->findOneBy([$idProp => $id]);
        }

        if ($object && !in_array(static::NO_PERMISSIONS, $options)) {
            // creates the entity if allowed
            $this->checkPermission('OPEN', $object, [], true);
        }

        return $object;
    }

    public function exist(string $class, mixed $id, string $idProp = 'id'): bool
    {
        $object = null;
        if ('id' === $idProp) {
            $object = $this->om->getRepository($class)->count(['uuid' => $id]);
        } else {
            $identifiers = $this->schema->getIdentifiers($class);
            if (!in_array($idProp, $identifiers)) {
                throw new \LogicException(sprintf('You can only get entities with an identifier property (identifiers: %s).', implode(', ', $identifiers)));
            }

            $object = $this->om->getRepository($class)->findOneBy([$idProp => $id]);
        }

        return !empty($object);
    }

    public function find(string $class, $data): ?object
    {
        return $this->om->getObject($data, $class, $this->schema->getIdentifiers($class));
    }

    public function list(string $class, array $query = [], array $options = []): array
    {
        $results = $this->finder->searchEntities($class, $query);

        return array_merge($results, [
            'data' => array_map(function ($result) use ($options) {
                return $this->serializer->serialize($result, $options);
            }, $results['data']),
        ]);
    }

    /**
     * Creates a new entry for `class` and populates it with `data`.
     *
     * @param mixed $classOrObject - the class of the entity to create or an instance of the entity
     * @param array $data          - the serialized data of the object to create
     * @param array $options       - additional creation options
     *
     * @return object|array
     *
     * @throws InvalidDataException
     */
    public function create(mixed $classOrObject, array $data = [], array $options = []): mixed
    {
        if (is_string($classOrObject)) {
            // class name received
            $class = $classOrObject;
            $object = new $classOrObject();
        } else {
            // object instance received
            $class = $this->getRealClass($classOrObject);
            $object = $classOrObject;
        }

        // validates submitted data.
        if (!in_array(self::NO_VALIDATION, $options)) {
            $errors = $this->validate($class, $data, ValidatorProvider::CREATE, $options);
            if (count($errors) > 0) {
                // TODO : it should always throw exception
                if (in_array(self::THROW_EXCEPTION, $options)) {
                    throw new InvalidDataException(sprintf('%s is not valid', $class), $errors);
                } else {
                    return $errors;
                }
            }
        }

        // gets entity from raw data.
        $object = $this->serializer->deserialize($data, $object, $options);

        if (!in_array(static::NO_PERMISSIONS, $options)) {
            // creates the entity if allowed
            $this->checkPermission('CREATE', $object, [], true);
        }

        if ($this->dispatch('create', 'pre', [$object, $options, $data])) {
            $this->om->persist($object);
            if (!in_array(Options::FORCE_FLUSH, $options)) {
                $this->om->flush();
            } else {
                $this->om->forceFlush();
            }

            $this->dispatch('create', 'post', [$object, $options, $data]);
        }

        return $object;
    }

    /**
     * Updates an entry of `class` with `data`.
     *
     * @param mixed $classOrObject - the class of the entity to update or an instance of the entity
     * @param mixed $data          - the serialized data of the object to create
     * @param array $options       - additional update options
     *
     * @return array|object
     *
     * @throws InvalidDataException
     */
    public function update(mixed $classOrObject, array $data, array $options = []): mixed
    {
        if (is_string($classOrObject)) {
            // class name received
            $class = $classOrObject;
            // grab object from db
            $oldObject = $this->om->getObject($data, $class, $this->schema->getIdentifiers($class) ?? []) ?? new $class();
        } else {
            // object instance received
            $class = $this->getRealClass($classOrObject);
            $oldObject = $classOrObject;
        }

        // validates submitted data.
        if (!in_array(self::NO_VALIDATION, $options)) {
            $errors = $this->validate($class, $data, ValidatorProvider::UPDATE, $options);
            if (count($errors) > 0) {
                // TODO : it should always throw exception
                if (in_array(self::THROW_EXCEPTION, $options)) {
                    throw new InvalidDataException(sprintf('%s is not valid', $class), $errors);
                } else {
                    return $errors;
                }
            }
        }

        if (!in_array(static::NO_PERMISSIONS, $options)) {
            $this->checkPermission('EDIT', $oldObject, [], true);
        }

        $oldData = $this->serializer->serialize($oldObject) ?? [];

        $object = $this->serializer->deserialize($data, $oldObject, $options);
        if ($this->dispatch('update', 'pre', [$object, $options, $data, $oldData])) {
            $this->om->persist($object);

            if (!in_array(Options::FORCE_FLUSH, $options)) {
                $this->om->flush();
            } else {
                $this->om->forceFlush();
            }

            $this->dispatch('update', 'post', [$object, $options, $data, $oldData]);
        }

        return $object;
    }

    /**
     * Deletes an entry `object`.
     *
     * @param object $object  - the entity to delete
     * @param array  $options - additional delete options
     */
    public function delete(mixed $object, array $options = []): void
    {
        if (!in_array(static::NO_PERMISSIONS, $options)) {
            $this->checkPermission('DELETE', $object, [], true);
        }

        if ($this->dispatch('delete', 'pre', [$object, $options])) {
            if (!in_array(Options::SOFT_DELETE, $options)) {
                $this->om->remove($object);
            }

            if (!in_array(Options::FORCE_FLUSH, $options)) {
                $this->om->flush();
            } else {
                $this->om->forceFlush();
            }

            $this->dispatch('delete', 'post', [$object, $options]);
        }
    }

    /**
     * Deletes a list of entries of `class`.
     *
     * @param array $data    - the list of entries to delete
     * @param array $options - additional delete options
     */
    public function deleteBulk(array $data, array $options = []): void
    {
        $this->om->startFlushSuite();

        foreach ($data as $el) {
            // get the element
            $this->delete($el, $options);
        }

        $this->om->endFlushSuite();
    }

    /**
     * Copy an entry `object` of `class`.
     *
     * @param object $object  - the entity to copy
     * @param array  $options - additional copy options
     * @param array  $extra   - extra data used for copy
     *
     * @return object
     */
    public function copy(mixed $object, array $options = [], array $extra = []): mixed
    {
        if (!in_array(static::NO_PERMISSIONS, $options)) {
            $this->checkPermission('COPY', $object, [], true);
        }

        $class = $this->getRealClass($object);
        $new = new $class();

        $serializer = $this->serializer->get($new);

        if (method_exists($serializer, 'getCopyOptions')) {
            $options = array_merge($options, $serializer->getCopyOptions());
        }

        $this->serializer->deserialize(
            $this->serializer->serialize($object, $options),
            $new,
            array_merge([], $options, [Options::REFRESH_UUID])
        );

        $this->om->persist($new);

        // first event is the pre one
        if ($this->dispatch('copy', 'pre', [$object, $options, $new, $extra])) {
            if (!in_array(Options::FORCE_FLUSH, $options)) {
                $this->om->flush();
            } else {
                $this->om->forceFlush();
            }

            $this->dispatch('copy', 'post', [$object, $options, $new, $extra]);
        }

        return $new;
    }

    /**
     * Patches a collection in `object`. It will also work for collection with the add/delete method.
     *
     * @param object $object   - the entity to update
     * @param string $property - the name of the property which holds the collection
     * @param string $action   - the action to execute on the collection (aka. add/remove/set)
     * @param array  $elements - the collection to patch
     * @param array  $options  - additional patch options
     *
     * @todo only flush once (do not flush for each collection element)
     * @todo only dispatch lifecycle events once with the full collection in param
     */
    public function patch(mixed $object, string $property, string $action, array $elements, array $options = []): mixed
    {
        $methodName = $action.ucfirst(strtolower($property));

        if (!method_exists($object, $methodName)) {
            throw new \LogicException(sprintf('You have requested a non implemented action %s on %s', $methodName, $this->getRealClass($object)));
        }

        if (!in_array(static::NO_PERMISSIONS, $options)) {
            $this->checkPermission('PATCH', $object, ['collection' => new ObjectCollection($elements, ['action' => $action])], true);
        }
        foreach ($elements as $element) {
            // check if the element is in the collection if the object implement a has*() method
            $checkerName = 'has'.ucfirst(strtolower($property));
            if (method_exists($object, $checkerName)) {
                if ((self::COLLECTION_ADD === $action && $object->$checkerName($element))
                    || (self::COLLECTION_REMOVE === $action && !$object->$checkerName($element))) {
                    continue;
                }
            }

            if ($this->dispatch('patch', 'pre', [$object, $options, $property, $element, $action])) {
                $object->$methodName($element);

                $this->om->persist($object);
                if (!in_array(Options::FORCE_FLUSH, $options)) {
                    $this->om->flush();
                } else {
                    $this->om->forceFlush();
                }

                $this->dispatch('patch', 'post', [$object, $options, $property, $element, $action]);
            }
        }

        return $object;
    }

    /**
     * Patches a property in `object`.
     * This may use 'update' permission and events.
     *
     * @param object $object   - the entity to update
     * @param string $property - the property to update
     * @param mixed  $data     - the data that must be set
     * @param array  $options  - an array of options
     *
     * @return object
     *
     * @deprecated should use standard update instead
     */
    public function replace(mixed $object, string $property, mixed $data, array $options = []): mixed
    {
        $methodName = 'set'.ucfirst($property);

        if (!method_exists($object, $methodName)) {
            throw new \LogicException(sprintf('You have requested a non implemented action \'set\' on %s (looked for %s)', $this->getRealClass($object), $methodName));
        }

        if (!in_array(static::NO_PERMISSIONS, $options)) {
            // add the options to pass on here
            $this->checkPermission('PATCH', $object, [], true);
            // we'll need to pass the $action and $data here aswell later
        }

        if ($this->dispatch('patch', 'pre', [$object, $options, $property, $data, self::PROPERTY_SET])) {
            $object->$methodName($data);

            $this->om->persist($object);
            if (!in_array(Options::FORCE_FLUSH, $options)) {
                $this->om->flush();
            } else {
                $this->om->forceFlush();
            }

            $this->dispatch('patch', 'post', [$object, $options, $property, $data, self::PROPERTY_SET]);
        }

        return $object;
    }

    /**
     * Validates `data` with the available validator for `class`.
     *
     * @param string $class   - the class of the entity used for validation
     * @param array  $data    - the serialized data to validate
     * @param string $mode    - the validation mode
     * @param array  $options - the validation options
     */
    public function validate(mixed $class, array $data, string $mode, array $options = []): array
    {
        return $this->validator->validate($class, $data, $mode, true, $options);
    }

    /**
     * We dispatch 2 events: a generic one and another with a custom name.
     * Listen to what you want. Both have their uses.
     *
     * @param string $action (create, copy, delete, patch, update)
     * @param string $when   (post, pre)
     * @param array  $args   the event arguments
     */
    public function dispatch(string $action, string $when, array $args): bool
    {
        $className = $this->getRealClass($args[0]);

        $eventClass = ucfirst($action);
        /** @var CrudEvent $generic */
        $generic = $this->dispatcher->dispatch(static::getEventName($action, $when), 'Claroline\\AppBundle\\Event\\Crud\\'.$eventClass.'Event', $args);

        /** @var CrudEvent $specific */
        $specific = $this->dispatcher->dispatch(static::getEventName($action, $when, $className), 'Claroline\\AppBundle\\Event\\Crud\\'.$eventClass.'Event', $args);
        $isAllowed = $specific->isAllowed();

        if ($this->serializer->has($className)) {
            $serializer = $this->serializer->get($className);

            if (method_exists($serializer, 'getName')) {
                $shortName = 'crud.'.$when.'.'.$action.'.'.$serializer->getName();
                $specific = $this->dispatcher->dispatch($shortName, 'Claroline\\AppBundle\\Event\\Crud\\'.$eventClass.'Event', $args);
            }
        }

        // TODO : let the event explain why it has blocked the process
        // for now we will do nothing and the user will not know why.
        return $generic->isAllowed() && $specific->isAllowed() && $isAllowed;
    }

    public static function getEventName(string $action, string $when, string $className = null): string
    {
        // TODO : find a way to make shortcut work (will require to inject the service to make it work for now)
        $name = 'crud_'.$when.'_'.$action.'_object';
        if ($className) {
            $name = $name.'_'.strtolower(str_replace('\\', '_', $className));
        }

        return $name;
    }

    private function getRealClass($object): string
    {
        return $this->om->getMetadataFactory()->getMetadataFor(get_class($object))->getName();
    }
}
