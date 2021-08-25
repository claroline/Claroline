<?php

namespace Claroline\AppBundle\API;

use Claroline\AppBundle\API\Utils\ArrayUtils;
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
    const COLLECTION_ADD = 'add';
    /** @var string */
    const COLLECTION_REMOVE = 'remove';
    /** @var string */
    const PROPERTY_SET = 'set';
    // TODO : remove me. only for retro compatibility it should be always the case
    // but I don't know if it will break things if I do it now
    const THROW_EXCEPTION = 'throw_exception';

    const NO_PERMISSIONS = 'NO_PERMISSIONS';

    /** @var ObjectManager */
    private $om;

    /** @var StrictDispatcher */
    private $dispatcher;

    /** @var FinderProvider */
    private $finder;

    /** @var SerializerProvider */
    private $serializer;

    /** @var ValidatorProvider */
    private $validator;

    /** @var SchemaProvider */
    private $schema;

    public function __construct(
      ObjectManager $om,
      StrictDispatcher $dispatcher,
      FinderProvider $finder,
      SerializerProvider $serializer,
      ValidatorProvider $validator,
      SchemaProvider $schema,
      AuthorizationCheckerInterface $authorization
    ) {
        $this->om = $om;
        $this->dispatcher = $dispatcher;
        $this->finder = $finder;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->schema = $schema;
        $this->authorization = $authorization;
    }

    /**
     * @param string|int $id
     *
     * @return object|null
     */
    public function get(string $class, $id)
    {
        if (!is_numeric($id) && property_exists($class, 'uuid')) {
            return $this->om->getRepository($class)->findOneBy(['uuid' => $id]);
        }

        return $this->om->getRepository($class)->findOneBy(['id' => $id]);
    }

    public function list(string $class, array $query = [], array $options = [])
    {
        $results = $this->finder->searchEntities($class, $query);

        return array_merge($results, [
            'data' => array_map(function ($result) use ($options) {
                return $this->serializer->serialize($result, $options);
            }, $results['data']),
        ]);
    }

    public function csv(string $class, array $columns = [], array $query = [], array $options = [])
    {
        $data = $this->list($class, $query, $options)['data'];

        $titles = [];
        $formatted = [];
        if (!empty($data[0])) {
            $firstRow = $data[0];
            //get the title list
            $titles = !empty($columns) ? $columns : ArrayUtils::getPropertiesName($firstRow);

            foreach ($data as $el) {
                $formattedData = [];
                foreach ($titles as $title) {
                    $formattedData[$title] = ArrayUtils::has($el, $title) ?
                        ArrayUtils::get($el, $title) : null;
                    $formattedData[$title] = !is_array($formattedData[$title]) ? $formattedData[$title] : json_encode($formattedData[$title]);
                }
                $formatted[] = $formattedData;
            }
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'CSVCLARO').'.csv';
        $fp = fopen($tmpFile, 'w');

        fputcsv($fp, $titles, ';');

        foreach ($formatted as $item) {
            fputcsv($fp, $item, ';');
        }

        fclose($fp);

        return $tmpFile;
    }

    /**
     * Creates a new entry for `class` and populates it with `data`.
     *
     * @param mixed $classOrObject - the class of the entity to create or an instance of the entity
     * @param mixed $data          - the serialized data of the object to create
     * @param array $options       - additional creation options
     *
     * @return object|array
     *
     * @throws InvalidDataException
     */
    public function create($classOrObject, $data, array $options = [])
    {
        if (is_string($classOrObject)) {
            // class name received
            $class = $classOrObject;
            $object = new $classOrObject();
        } else {
            // object instance received
            $class = get_class($classOrObject);
            $object = $classOrObject;
        }

        // validates submitted data.
        $errors = $this->validate($class, $data, ValidatorProvider::CREATE, $options);
        if (count($errors) > 0) {
            // TODO : it should always throw exception
            if (in_array(self::THROW_EXCEPTION, $options)) {
                throw new InvalidDataException(sprintf('%s is not valid', $class), $errors);
            } else {
                return $errors;
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
    public function update($classOrObject, $data, array $options = [])
    {
        if (is_string($classOrObject)) {
            // class name received
            $class = $classOrObject;
            // grab object from db
            $oldObject = $this->om->getObject($data, $class, $this->schema->getIdentifiers($class) ?? []) ?? new $class();
        } else {
            // object instance received
            $class = get_class($classOrObject);
            $oldObject = $classOrObject;
        }

        // validates submitted data.
        $errors = $this->validate($class, $data, ValidatorProvider::UPDATE);
        if (count($errors) > 0) {
            // TODO : it should always throw exception
            if (in_array(self::THROW_EXCEPTION, $options)) {
                throw new InvalidDataException(sprintf('%s is not valid', $class), $errors);
            } else {
                return $errors;
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
    public function delete($object, array $options = [])
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
    public function deleteBulk(array $data, array $options = [])
    {
        $this->om->startFlushSuite();

        foreach ($data as $el) {
            //get the element
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
    public function copy($object, array $options = [], array $extra = [])
    {
        if (!in_array(static::NO_PERMISSIONS, $options)) {
            $this->checkPermission('COPY', $object, [], true);
        }

        $class = $this->getRealClass($object);
        $new = new $class();

        //default option for copy
        $options[] = Options::REFRESH_UUID;
        $serializer = $this->serializer->get($new);

        if (method_exists($serializer, 'getCopyOptions')) {
            $options = array_merge($options, $serializer->getCopyOptions());
        }

        $this->serializer->deserialize(
          $this->serializer->serialize($object, $options),
          $new,
          $options
        );

        $this->om->persist($new);

        //first event is the pre one
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
     * Copy a list of entries of `class`.
     *
     * @param array $data    - the list of entries to copy
     * @param array $options - additional copy options
     *
     * @return array
     */
    public function copyBulk(array $data, array $options = [])
    {
        $this->om->startFlushSuite();
        $copies = [];

        foreach ($data as $el) {
            //get the element
            $copies[] = $this->copy($el, $options);
        }

        $this->om->endFlushSuite();

        return $copies;
    }

    /**
     * Patches a collection in `object`. It will also work for collection with the add/delete method.
     *
     * @param object $object   - the entity to update
     * @param string $property - the name of the property which holds the collection
     * @param string $action   - the action to execute on the collection (aka. add/remove/set)
     * @param array  $elements - the collection to patch
     * @param array  $options  - additional patch options
     */
    public function patch($object, $property, $action, array $elements, array $options = [])
    {
        $methodName = $action.ucfirst(strtolower($property));

        if (!method_exists($object, $methodName)) {
            throw new \LogicException(sprintf('You have requested a non implemented action %s on %s', $methodName, get_class($object)));
        }

        if (!in_array(static::NO_PERMISSIONS, $options)) {
            //add the options to pass on here
            $this->checkPermission('PATCH', $object, ['collection' => new ObjectCollection($elements)], true);
            //we'll need to pass the $action and $data here aswell later
        }

        foreach ($elements as $element) {
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

        $this->dispatch('patch', 'post_collection', [$object, $options, $property, $elements, $action]);

        return $object;
    }

    /**
     * Patches a property in `object`.
     *
     * @param object $object   - the entity to update
     * @param string $property - the property to update
     * @param mixed  $data     - the data that must be set
     * @param array  $options  - an array of options
     *
     * @return object
     */
    public function replace($object, $property, $data, array $options = [])
    {
        $methodName = 'set'.ucfirst($property);

        if (!method_exists($object, $methodName)) {
            throw new \LogicException(sprintf('You have requested a non implemented action \'set\' on %s (looked for %s)', get_class($object), $methodName));
        }

        if (!in_array(static::NO_PERMISSIONS, $options)) {
            //add the options to pass on here
            $this->checkPermission('PATCH', $object, [], true);
            //we'll need to pass the $action and $data here aswell later
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
     * @param mixed  $data    - the serialized data to validate
     * @param string $mode    - the validation mode
     * @param array  $options - the validation options
     *
     * @return array
     */
    public function validate($class, $data, $mode, array $options = [])
    {
        return $this->validator->validate($class, $data, $mode, true, $options);
    }

    /**
     * We dispatch 2 events: a generic one and an other with a custom name.
     * Listen to what you want. Both have their uses.
     *
     * @param string $action (create, copy, delete, patch, update)
     * @param string $when   (post, pre)
     * @param array  $args   the event arguments
     *
     * @return bool
     */
    public function dispatch($action, $when, array $args)
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

    private function getRealClass($object)
    {
        return $this->om->getMetadataFactory()->getMetadataFor(get_class($object))->getName();
    }
}
