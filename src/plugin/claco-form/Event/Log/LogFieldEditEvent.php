<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Event\Log;

use Claroline\ClacoFormBundle\Entity\Field;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;

class LogFieldEditEvent extends LogGenericEvent
{
    const ACTION = 'clacoformbundle-field-edit';

    public function __construct(Field $field)
    {
        $clacoForm = $field->getClacoForm();
        $resourceNode = $clacoForm->getResourceNode();
        $fieldFacet = $field->getFieldFacet();
        $fieldFacetChoices = !is_null($fieldFacet) ? $fieldFacet->getFieldFacetChoices() : [];
        $choices = [];

        foreach ($fieldFacetChoices as $fieldFacetChoice) {
            $choices[] = $fieldFacetChoice->getLabel();
        }
        $details = [];
        $details['id'] = $field->getId();
        $details['type'] = $field->getType();
        $details['name'] = $field->getName();
        $details['required'] = $field->isRequired();
        $details['isMetadata'] = $field->isMetadata();
        $details['locked'] = $field->isLocked();
        $details['lockedEditionOnly'] = $field->getLockedEditionOnly();
        $details['hidden'] = $field->isHidden();

        if (count($choices) > 0) {
            $details['choices'] = $choices;
        }
        $details['resourceId'] = $clacoForm->getId();
        $details['resourceNodeId'] = $resourceNode->getId();
        $details['resourceName'] = $resourceNode->getName();
        parent::__construct(self::ACTION, $details, null, null, $resourceNode);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return [self::DISPLAYED_WORKSPACE];
    }
}
