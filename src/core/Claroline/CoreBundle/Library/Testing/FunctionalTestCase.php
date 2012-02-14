<?php

namespace Claroline\CoreBundle\Library\Testing;

use Claroline\CoreBundle\Entity\User;

abstract class FunctionalTestCase extends FixtureTestCase
{
    protected function logUser(User $user)
    {
        $this->client->request('GET', '/logout');
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->filter('#login_form input[type=submit]')->form();
        $form['_username'] = $user->getUsername();
        $form['_password'] = $user->getPlainPassword();
        
        return $this->client->submit($form);
    }
    
    /** @return Symfony\Component\Security\Core\SecurityContextInterface */
    protected function getSecurityContext()
    {
        return $this->client->getContainer()->get('security.context');
    }
}