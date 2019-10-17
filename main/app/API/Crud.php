<?php

namespace Claroline\AppBundle\API;

use Claroline\AppBundle\Event\Crud\CrudEvent;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AppBundle\Security\ObjectCollection;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
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
    /** @var string */
    const NO_VALIDATE = 'no_validate';

    /** @var ObjectManager */
    private $om;

    /** @var StrictDispatcher */
    private $dispatcher;

    /** @var SerializerProvider */
    private $serializer;

    /** @var ValidatorProvider */
    private $validator;

    /** @var SchemaProvider */
    private $schema;

    /**
     * Crud constructor.
     *
     * @param ObjectManager      $om
     * @param StrictDispatcher   $dispatcher
     * @param SerializerProvider $serializer
     * @param ValidatorProvider  $validator
     * @param SchemaProvider     $schema
     */
    public function __construct(
      ObjectManager $om,
      StrictDispatcher $dispatcher,
      SerializerProvider $serializer,
      ValidatorProvider $validator,
      SchemaProvider $schema,
      AuthorizationCheckerInterface $authorization
    ) {
        $this->om = $om;
        $this->dispatcher = $dispatcher;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->schema = $schema;
        $this->authorization = $authorization;
    }

    /**
     * Creates a new entry for `class` and populates it with `data`.
     *
     * @param string $class   - the class of the entity to create
     * @param mixed  $data    - the serialized data of the object to create
     * @param array  $options - additional creation options
     *
     * @return object|array
     */
    public function create($class, $data, array $options = [])
    {
        // validates submitted data.
        if (!in_array(self::NO_VALIDATE, $options)) {
            $errors = $this->validate($class, $data, ValidatorProvider::CREATE, $options);

            if (count($errors) > 0) {
                return $errors; // todo : it should throw an Exception otherwise it makes return inconsistent
            }
        }

        // gets entity from raw data.
        $object = new $class();
        $object = $this->serializer->deserialize($data, $object, $options);

        // creates the entity if allowed
        $this->checkPermission('CREATE', $object, [], true);

        if ($this->dispatch('create', 'pre', [$object, $options, $data])) {
            $this->om->save($object);
            if (!in_array(Options::IGNORE_CRUD_POST_EVENT, $options)) {
                $this->dispatch('create', 'post', [$object, $options, $data]);
            }
        }

        return $object;
    }

    /**
     * Updates an entry of `class` with `data`.
     *
     * @param string $class   - the class of the entity to updates
     * @param mixed  $data    - the serialized data of the object to create
     * @param array  $options - additional update options
     *
     * @return object|array
     */
    public function update($class, $data, array $options = [])
    {
        // validates submitted data.
        if (!in_array(self::NO_VALIDATE, $options)) {
            $errors = $this->validate($class, $data, ValidatorProvider::UPDATE);

            if (count($errors) > 0) {
                return $errors; // todo : it should throw an Exception otherwise it makes return inconsistent
            }
        }

        $oldObject = $this->om->getObject($data, $class, $this->schema->getIdentifiers($class) ?? []) ?? new $class();
        $this->checkPermission('EDIT', $oldObject, [], true);
        $oldData = $this->serializer->serialize($oldObject);

        if (!$oldData) {
            $oldData = [];
        }

        $object = $this->serializer->deserialize($data, $oldObject, $options);

        if ($this->dispatch('update', 'pre', [$object, $options, $oldData])) {
            $this->om->save($object);
            $this->dispatch('update', 'post', [$object, $options, $oldData]);
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
        $this->checkPermission('DELETE', $object, [], true);

        if ($this->dispatch('delete', 'pre', [$object, $options])) {
            if (!in_array(Options::SOFT_DELETE, $options)) {
                $this->om->remove($object);
                $this->om->flush();
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
     *
     * @return object
     */
    public function copy($object, array $options = [])
    {
        $this->checkPermission('COPY', $object, [], true);
        $class = get_class($object);
        $new = new $class();

        $this->serializer->deserialize(
          $this->serializer->serialize($object),
          $new
        );

        $this->om->persist($new);

        //first event is the pre one
        if ($this->dispatch('copy', 'pre', [$object, $options, $new])) {
            //second event is the post one
            //we could use only one event afaik
            $this->dispatch('copy', 'post', [$object, $options, $new]);
        }

        $this->om->flush();

        return $new;
    }

    /**
     * Copy a list of entries of `class`.
     *
     * @param string $class   - the class of the entries to copy
     * @param array  $data    - the list of entries to copy
     * @param array  $options - additional copy options
     *
     * @return array
     */
    public function copyBulk($class, array $data, array $options = [])
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
     * @param array  $options
     */
    public function patch($object, $property, $action, array $elements, array $options = [])
    {
        $methodName = $action.ucfirst(strtolower($property));

        if (!method_exists($object, $methodName)) {
            throw new \LogicException(
                sprintf('You have requested a non implemented action %s on %s', $methodName, get_class($object))
            );
        }

        //add the options to pass on here
        $this->checkPermission('PATCH', $object, ['collection' => new ObjectCollection($elements)], true);
        //we'll need to pass the $action and $data here aswell later

        foreach ($elements as $element) {
            if ($this->dispatch('patch', 'pre', [$object, $options, $property, $element, $action])) {
                $object->$methodName($element);

                $this->om->save($object);
                $this->dispatch('patch', 'post', [$object, $options, $property, $element, $action]);
            }
        }
        $this->dispatch('patch', 'post_collection', [$object, $options, $property, $elements, $action]);
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
            throw new \LogicException(
                sprintf('You have requested a non implemented action \'set\' on %s (looked for %s)', get_class($object), $methodName)
            );
        }

        //add the options to pass on here
        $this->checkPermission('PATCH', $object, [], true);
        //we'll need to pass the $action and $data here aswell later
        if ($this->dispatch('patch', 'pre', [$object, $options, $property, $data, self::PROPERTY_SET])) {
            $object->$methodName($data);

            $this->om->save($object);
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
     * @param array  $options
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
     * @param array  $args
     *
     * @return bool
     */
    public function dispatch($action, $when, array $args)
    {
        $name = 'crud_'.$when.'_'.$action.'_object';
        $eventClass = ucfirst($action);
        /** @var CrudEvent $generic */
        $generic = $this->dispatcher->dispatch($name, 'Claroline\\AppBundle\\Event\\Crud\\'.$eventClass.'Event', $args);

        $className = $this->om->getMetadataFactory()->getMetadataFor(get_class($args[0]))->getName();
        $serializedName = $name.'_'.strtolower(str_replace('\\', '_', $className));
        /** @var CrudEvent $specific */
        $specific = $this->dispatcher->dispatch($serializedName, 'Claroline\\AppBundle\\Event\\Crud\\'.$eventClass.'Event', $args);

        return $generic->isAllowed() && $specific->isAllowed();
    }
}
