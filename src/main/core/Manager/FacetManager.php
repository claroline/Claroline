<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\AppBundle\API\Utils\ArrayUtils;
use Claroline\CoreBundle\Event\CatalogEvents\FacetEvents;
use Claroline\CoreBundle\Event\Facet\GetFacetValueEvent;
use Claroline\CoreBundle\Event\Facet\SetFacetValueEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FacetManager
{
    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function serializeFieldValue($object, $type, $value = null)
    {
        $event = new GetFacetValueEvent(
            $object,
            $type,
            $value
        );

        $this->dispatcher->dispatch($event, FacetEvents::getEventName(FacetEvents::GET_VALUE, $type));

        return $event->getFormattedValue();
    }

    public function deserializeFieldValue($object, $type, $value = null)
    {
        $event = new SetFacetValueEvent(
            $object,
            $type,
            $value
        );

        $this->dispatcher->dispatch($event, FacetEvents::getEventName(FacetEvents::SET_VALUE, $type));

        return $event->getFormattedValue();
    }

    public function isFieldDisplayed($fieldDef, $allFields, $data)
    {
        $condition = $fieldDef['display']['condition'];

        if (!empty($condition)) {
            $parentField = null;

            foreach ($allFields as $searchedField) {
                if ($searchedField['id'] === $condition['field']) {
                    $parentField = $searchedField;
                }
            }

            if ($parentField) {
                $parentValue = ArrayUtils::get($data, 'profile.'.$parentField['id']);

                $displayed = false;

                switch ($condition['comparator']) {
                    case 'equal':
                        $displayed = $parentValue === $condition['value'];
                        break;
                    case 'different':
                        $displayed = $parentValue !== $condition['value'];
                        break;
                    case 'empty':
                        $displayed = empty($parentValue);
                        break;
                    case 'not_empty':
                        $displayed = !empty($parentValue);
                        break;
                }

                return $displayed;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }
}
