<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 5/5/15
 */

namespace Claroline\CoreBundle\Event\Profile;

use Claroline\CoreBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class ProfileLinksEvent extends Event
{
    protected $links;
    protected $user;
    protected $locale;

    public function __construct(User $user, $locale = null)
    {
        $this->user = $user;
        $this->locale = $locale;
        $this->links = array();
    }

    public function addTab(ProfileLink $tab)
    {
        $this->links[] = $tab;
    }

    public function getLinks()
    {
        return $this->links;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getLocale()
    {
        return $this->locale;
    }
}
