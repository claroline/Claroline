<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Contact\Category;
use Claroline\CoreBundle\Entity\Contact\Contact;
use Claroline\CoreBundle\Entity\Contact\Options;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.contact_manager")
 */
class ContactManager
{
    private $om;
    private $categoryRepo;
    private $contactRepo;
    private $optionsRepo;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->categoryRepo = $om->getRepository('ClarolineCoreBundle:Contact\Category');
        $this->contactRepo = $om->getRepository('ClarolineCoreBundle:Contact\Contact');
        $this->optionsRepo = $om->getRepository('ClarolineCoreBundle:Contact\Options');
    }

    public function getUserContacts(
        User $user,
        $search = '',
        $orderedBy = 'lastName',
        $order = 'ASC'
    )
    {
        $users = array();

        if (empty($search)) {
            $contacts = $this->getContactsByUser($user, $orderedBy, $order);
        } else {
            $options = $this->getUserOptionsValues($user);
            $withUsername = isset($options['username']) && ($options['username'] === 1);
            $withMail = isset($options['mail']) && ($options['mail'] === 1);

            $contacts = $this->getContactsByUserAndSearch(
                $user,
                $search,
                $withUsername,
                $withMail,
                $orderedBy,
                $order
            );
        }

        foreach ($contacts as $contact) {
            $users[] = $contact->getContact();
        }

        return $users;
    }

    public function getUserOptionsValues(User $user)
    {
        $options = $this->getUserOptions($user);

        return $options->getOptions();
    }

    public function getUserOptions(User $user)
    {
        $options = $this->getOptionsByUser($user);

        if (is_null($options)) {
            $options = new Options();
            $options->setUser($user);
            $defaultValues = array(
                'show_all_my_contacts' => true,
                'show_all_visible_users' => true,
                'show_username' => true,
                'show_mail' => false,
                'show_phone' => false,
                'show_picture' => true
            );
            $options->setOptions($defaultValues);
            $this->om->persist($options);
            $this->om->flush();
        }

        return $options;
    }

    public function persistContact(Contact $contact)
    {
        $this->om->persist($contact);
        $this->om->flush();
    }

    public function deleteContact(Contact $contact)
    {
        $this->om->remove($contact);
        $this->om->flush();
    }

    public function persistCategory(Category $category)
    {
        $this->om->persist($category);
        $this->om->flush();
    }

    public function deleteCategory(Category $category)
    {
        $this->om->remove($category);
        $this->om->flush();
    }

    public function persistOptions(Options $options)
    {
        $this->om->persist($options);
        $this->om->flush();
    }

    /***************************************
     * Access to ContactRepository methods *
     ***************************************/

    public function getContactsByUser(
        User $user,
        $orderedBy = 'lastName',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        return $this->contactRepo->findContactsByUser(
            $user,
            $orderedBy,
            $order, 
            $executeQuery
        );
    }

    public function getContactsByUserAndSearch(
        User $user,
        $search,
        $withUsername = false,
        $withMail = false,
        $orderedBy = 'lastName',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        return $this->contactRepo->findContactsByUserAndSearch(
            $user,
            $search,
            $withUsername,
            $withMail,
            $orderedBy,
            $order,
            $executeQuery
        );
    }

    /****************************************
     * Access to CategoryRepository methods *
     ****************************************/

    public function getCategoriesByUser(
        User $user,
        $orderedBy = 'order',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        return $this->categoryRepo->findCategoriesByUser(
            $user,
            $orderedBy,
            $order,
            $executeQuery
        );
    }

    public function getCategoryByUserAndName(User $user, $name, $executeQuery = true)
    {
        return $this->categoryRepo->findCategoryByUserAndName($user, $name, $executeQuery);
    }

    public function getOrderOfLastCategoryByUser(User $user)
    {
        return $this->categoryRepo->findOrderOfLastCategoryByUser($user);
    }


    /***************************************
     * Access to OptionsRepository methods *
     ***************************************/

    public function getOptionsByUser(User $user, $executeQuery = true)
    {
        return $this->optionsRepo->findOptionsByUser($user, $executeQuery);
    }
}
