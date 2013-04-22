<?php

namespace Claroline\CoreBundle\Library\Security\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class ViewAsToken extends AbstractToken
{
    public function __construct(array $roles = array())
    {
        parent::__construct($roles);

        // If the user has roles, consider it authenticated
        $this->setAuthenticated(count($roles) > 0);
        $this->workspaceName = '';
    }

    public function getCredentials()
    {
        return '';
    }
}
