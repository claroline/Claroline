<?php

namespace Claroline\CoreBundle\API;

use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Provides common CRUD operations.
 *
 * @DI\Service("claroline.api.crud")
 */
class Crud
{
    use PermissionCheckerTrait;

    const COLLECTION_ADD = 'add';
    const COLLECTION_REMOVE = 'remove';

    /** @var ObjectManager */
    private $om;

    /** @var StrictDispatcher */
    private $dispatcher;

    /** @var SerializerProvider */
    private $serializer;

    /** @var ValidatorProvider */
    private $validator;

    /**
     * Crud constructor.
     *
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "dispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *     "serializer" = @DI\Inject("claroline.api.serializer"),
     *     "validator" = @DI\Inject("claroline.api.validator")
     * })
     *
     * @param ObjectManager      $om
     * @param StrictDispatcher   $dispatcher
     * @param SerializerProvider $serializer
     * @param ValidatorProvider  $validator
     */
    public function __construct(
      ObjectManager $om,
      StrictDispatcher $dispatcher,
      SerializerProvider $serializer,
      ValidatorProvider $validator
    ) {
        $this->om = $om;
        $this->dispatcher = $dispatcher;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * Creates a new entry for `class` and populates it with `data`.
     *
     * @param string $class   - the class of the entity to create
     * @param mixed  $data    - the serialized data of the object to create
     * @param array  $options - additional creation options
     *
     * @return object
     */
    public function create($class, $data, array $options = [])
    {
        // validates submitted data.
        $this->validate($class, $data);

        // gets entity from raw data.
        $object = $this->serializer->deserialize($class, $data);

        // creates the entity if allowed
        $this->checkPermission('CREATE', $object, [], true);

        $event = $this->dispatcher->dispatch('crud_pre_create_object', 'Crud', [$object]);

        if ($event->isAllowed()) {
            $this->om->save($object);
            $this->dispatcher->dispatch('crud_post_create_object', 'Crud', [$object]);
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
     * @return object
     */
    public function update($class, $data, array $options = [])
    {
        // validates submitted data.
        $this->validate($class, $data);

        // gets entity from raw data.
        $object = $this->serializer->deserialize($class, $data);

        // updates the entity if allowed
        $this->checkPermission('EDIT', $object, [], true);
        $event = $this->dispatcher->dispatch('crud_pre_update_object', 'Crud', [$object]);

        if ($event->isAllowed()) {
            $this->om->save($object);
            $this->dispatcher->dispatch('crud_post_update_object', 'Crud', [$object]);
        }

        return $object;
    }

    /**
     * Deletes an entry `object` of `class`.
     *
     * @param object $object  - the entity to delete
     * @param string $class   - the class of the entity to delete
     * @param array  $options - additional delete options
     */
    public function delete($object, $class, array $options = [])
    {
        $this->checkPermission('DELETE', $object, [], true);

        $event = $this->dispatcher->dispatch('crud_pre_delete_object', 'Crud', [$object]);

        if ($event->isAllowed()) {
            $this->om->remove($object);
            $this->om->flush();
            $this->dispatcher->dispatch('crud_post_delete_object', 'Crud', [$object]);
        }
    }

    /**
     * Deletes a list of entries of `class`.
     *
     * @param string $class   - the class of the entries to delete
     * @param array  $data    - the list of entries to delete
     * @param array  $options - additional delete options
     */
    public function deleteBulk($class, array $data, array $options = [])
    {
        $this->om->startFlushSuite();

        foreach ($data as $el) {
            //get the element
            $this->delete($el, $class, $options);
        }

        $this->om->endFlushSuite();
    }

    /**
     * Patches a collection in `object`. It will also work for collection with the add/delete method.
     *
     * @param object $object   - the entity to update
     * @param string $property - the name of the property which holds the collection
     * @param string $action   - the action to execute on the collection (aka. add/remove/set)
     * @param mixed  $elements - the collection to patch
     */
    public function patch($object, $property, $action, array $elements)
    {
        $methodName = $action.ucfirst(strtolower($property));

        if (!method_exists($object, $methodName)) {
            throw new \LogicException(
                sprintf('You have requested a non implemented action %s on %s', $action, get_class($object))
            );
        }

        //add the options to pass on here
        $this->checkPermission('PATCH', $object, [], true);
        //we'll need to pass the $action and $data here aswell later
        $this->dispatcher->dispatch('crud_pre_patch_object', 'Crud', [$object]);

        foreach ($elements as $element) {
            $object->$methodName($element);
        }

        $this->om->save($object);
        $this->dispatcher->dispatch('crud_post_patch_object', 'Crud', [$object]);
    }

    /**
     * Patches a property in `object`.
     *
     * @param object $object - the entity to update
     * @param string $action - the action to execute on the collection (aka. add/remove/set)
     * @param mixed  $data   - the datas that must be set
     */
    public function replace($object, $property, $data)
    {
        $methodName = 'set'.ucfirst(strtolower($property));

        if (!method_exists($object, $methodName)) {
            throw new \LogicException(
                sprintf('You have requested a non implemented action \'set\' on %s', get_class($object))
            );
        }

        //add the options to pass on here
        $this->checkPermission('PATCH', $object, [], true);
        //we'll need to pass the $action and $data here aswell later
        $this->dispatcher->dispatch('crud_pre_patch_object', 'Crud', [$object]);
        $object->$methodName($data);

        $this->om->save($object);
        $this->dispatcher->dispatch('crud_post_patch_object', 'Crud', [$object]);
    }

    /**
     * Validates `data` with the available validator for `class`.
     *
     * @param string $class - the class of the entity used for validation
     * @param mixed  $data  - the serialized data to validate
     */
    public function validate($class, $data)
    {
        if ($this->validator->has($class)) {
            // calls the validator for class. It will throw exception on error
            $this->validator->validate($class, $data, true);
        }
    }
}
