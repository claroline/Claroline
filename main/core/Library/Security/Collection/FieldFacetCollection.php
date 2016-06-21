<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Security\Collection;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;

/**
 * This is the class used by the UserVoter to take access decisions.
 */
class FieldFacetCollection
{
    private $groups;
    private $errors;

    public function __construct(array $fields, User $user)
    {
        $this->fields = $fields;
        $this->user = $user;
    }

    public function addField(FieldFacet $field)
    {
        $this->fields[] = $field;
    }

    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    public function getErrorsForDisplay()
    {
        $content = '';

        foreach ($this->errors as $error) {
            $content .= "<p>{$error}</p>";
        }

        return $content;
    }
}
