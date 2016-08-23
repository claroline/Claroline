<?php

namespace Claroline\ChatBundle\Library\Xmpp\Protocol;

use Claroline\CoreBundle\Entity\User;
use Fabiang\Xmpp\Protocol\ProtocolImplementationInterface;
use Fabiang\Xmpp\Util\XML;

/**
 * Register new user.
 */
class Register implements ProtocolImplementationInterface
{
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build XML message.
     *
     * @return type
     */
    public function toString()
    {
        $query = "<iq type='set' id='%s'><query xmlns='jabber:iq:register'><username>%s</username><password>%s</password><name>%s</name></query></iq>";

        return XML::quoteMessage(
            $query, XML::generateId(),
            $this->user->getUsername(),
            $this->user->getGuid(),
            $this->user->getFirstName().' '.$this->user->getLastName()
        );
    }
}
