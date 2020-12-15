<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MessageBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\MessageBundle\Entity\Contact\Contact;

class ContactManager
{
    private $om;

    private $contactRepo;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;

        $this->contactRepo = $om->getRepository(Contact::class);
    }

    /**
     * Creates contacts from a list of user.
     *
     * @param User[] $users
     *
     * @return Contact[]
     */
    public function createContacts(User $currentUser, array $users)
    {
        $this->om->startFlushSuite();
        $createdContacts = [];

        foreach ($users as $user) {
            $contact = $this->contactRepo->findOneBy(['user' => $currentUser, 'contact' => $user]);

            if (is_null($contact)) {
                $contact = new Contact();
                $contact->setUser($currentUser);
                $contact->setContact($user);
                $this->om->persist($contact);
                $createdContacts[] = $contact;
            }
        }
        $this->om->endFlushSuite();

        return $createdContacts;
    }
}
