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

/**
 * This is the class used by the UserVoter to take access decisions.
 */
class GroupCollection
{
    private $groups;
    private $errors;

    public function __construct(array $groups)
    {
        $this->groups = $groups;
    }

    public function addGroup(Group $group)
    {
        $this->groups[] = $group;
    }

    public function setGroups($groups)
    {
        $this->groups = $groups;
    }

    public function getGroups()
    {
        return $this->groups;
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
