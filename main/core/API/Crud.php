<?php

namespace Claroline\CoreBundle\API;

use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Security\ObjectCollection;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @DI\Service("claroline.api.crud")
 */
class Crud
{
    const ADD_ARRAY_ELEMENT = 'add';
    const REMOVE_ARRAY_ELEMENT = 'remove';
    /**
     * Finder constructor.
     *
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer" = @DI\Inject("claroline.api.serializer"),
     *     "dispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *     "security"   = @DI\Inject("security.authorization_checker")
     * })
     *
     * @param ObjectManager      $om
     * @param SerializerProvider $serializer
     */
    public function __construct(
      ObjectManager $om,
      SerializerProvider $serializer,
      StrictDispatcher $dispatcher,
      AuthorizationCheckerInterface $security
    ) {
        $this->om = $om;
        $this->serializer = $serializer;
        $this->dispatcher = $dispatcher;
        $this->security = $security;
    }

    public function create($class, $data, array $options = [])
    {
        $this->validate($class, $data);
        $object = $this->serializer->deserialize($class, $data);
        $this->checkPermission('CREATE', $object);
        $this->dispatcher->dispatch('crud_pre_create_object', 'Crud', [$object]);
        $this->om->save($object);
        $this->dispatcher->dispatch('crud_post_create_object', 'Crud', [$object]);

        return $object;
    }

    public function update($class, $data, array $options = [])
    {
        $this->validate($class, $data);
        $object = $this->serializer->deserialize($class, $data);
        $this->checkPermission('EDIT', $object);
        $this->dispatcher->dispatch('crud_pre_update_object', 'Crud', [$object]);
        $this->om->save($object);
        $this->dispatcher->dispatch('crud_post_update_object', 'Crud', [$object]);

        return $object;
    }

    public function delete($object, $class, array $options = [])
    {
        $this->checkPermission('DELETE', $object);
        $this->dispatcher->dispatch('crud_pre_delete_object', 'Crud', [$object]);
        $this->om->remove($object);
        $this->om->flush();
        $this->dispatcher->dispatch('crud_post_delete_object', 'Crud', [$object]);
    }

    public function deleteBulk($class, array $data, array $options = [])
    {
        foreach ($data as $el) {
            //get the element
            $this->delete($el, $class);
        }
    }

    public function patch($object, $property, $action, $arrayElement)
    {
        //add the options to pass on here
        $this->checkPermission('PATCH', $object);
        $this->dispatcher->dispatch('crud_pre_patch_object', 'Crud', [$object]);
        $methodName = $action.substr(ucfirst(strtolower($property)), 0, -1);

        foreach ($arrayElement as $element) {
            $object->$methodName($element);
        }

        $this->om->save($object);
        $this->dispatcher->dispatch('crud_post_patch_object', 'Crud', [$object]);
    }

    public function validate($class, $data)
    {
    }

    private function checkPermission($permission, $object, $options = [])
    {
        $collection = new ObjectCollection([$object]);

        if (!$this->security->isGranted($permission, $collection)) {
            throw new AccessDeniedException(
              'operation '.$permission.' couldn\'t be done on object '.get_class($object)
            );
        }
    }
}
