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

use Claroline\ClacoFormBundle\Entity\Entry;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;

class LogEntryEditEvent extends LogGenericEvent
{
    const ACTION = 'clacoformbundle-entry-edit';

    public function __construct(Entry $entry)
    {
        $details = [];
        $details['id'] = $entry->getId();
        $details['title'] = $entry->getTitle();
        $details['status'] = $entry->getStatus();
        $details['locked'] = $entry->isLocked();
        $details['creationDate'] = $entry->getCreationDate();
        $details['editionDate'] = $entry->getEditionDate();
        $details['publicationDate'] = $entry->getPublicationDate();
        $user = $entry->getUser();

        if (!is_null($user)) {
            $details['userId'] = $user->getId();
            $details['username'] = $user->getUsername();
            $details['firstName'] = $user->getFirstName();
            $details['lastName'] = $user->getLastName();
        }
        $fieldValues = $entry->getFieldValues();
        $details['values'] = [];

        foreach ($fieldValues as $fieldValue) {
            $fieldFacetValue = $fieldValue->getFieldFacetValue();
            $fieldFacet = $fieldFacetValue->getFieldFacet();
            $details['values'][] = [
                'id' => $fieldFacetValue->getId(),
                'value' => $fieldFacetValue->getValue(),
                'name' => $fieldFacet->getName(),
                'type' => $fieldFacet->getType(),
                'typeName' => $fieldFacet->getFieldType(),
            ];
        }
        $categories = $entry->getCategories();
        $details['categories'] = [];

        foreach ($categories as $category) {
            $details['categories'][] = ['id' => $category->getId(), 'name' => $category->getName()];
        }
        $keywords = $entry->getKeywords();
        $details['keywords'] = [];

        foreach ($keywords as $keyword) {
            $details['keywords'][] = ['id' => $keyword->getId(), 'name' => $keyword->getName()];
        }
        $clacoForm = $entry->getClacoForm();
        $resourceNode = $clacoForm->getResourceNode();
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
