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
use Claroline\CoreBundle\Pager\PagerFactory;
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
    private $pagerFactory;

    /**
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory" = @DI\Inject("claroline.pager.pager_factory")
     * })
     */
    public function __construct(ObjectManager $om, PagerFactory $pagerFactory)
    {
        $this->om = $om;
        $this->pagerFactory = $pagerFactory;
        $this->categoryRepo = $om->getRepository('ClarolineCoreBundle:Contact\Category');
        $this->contactRepo = $om->getRepository('ClarolineCoreBundle:Contact\Contact');
        $this->optionsRepo = $om->getRepository('ClarolineCoreBundle:Contact\Options');
    }

    public function getUserContacts(
        User $user,
        $search = '',
        $orderedBy = 'lastName',
        $order = 'ASC'
    ) {
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

    public function getUserContactsWithPager(
        User $user,
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'lastName',
        $order = 'ASC'
    ) {
        $contacts = $this->getUserContacts($user, $search, $orderedBy, $order);

        return $this->pagerFactory->createPagerFromArray($contacts, $page, $max);
    }

    public function getUserContactsByCategory(
        User $user,
        Category $category,
        $orderedBy = 'lastName',
        $order = 'ASC'
    ) {
        $users = array();
        $contacts = $this->getContactsByUserAndCategory(
            $user,
            $category,
            $orderedBy,
            $order
        );

        foreach ($contacts as $contact) {
            $users[] = $contact->getContact();
        }

        return $users;
    }

    public function getUserContactsByCategoryWithPager(
        User $user,
        Category $category,
        $page = 1,
        $max = 50,
        $orderedBy = 'lastName',
        $order = 'ASC'
    ) {
        $contacts = $this->getUserContactsByCategory($user, $category, $orderedBy, $order);

        return $this->pagerFactory->createPagerFromArray($contacts, $page, $max);
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
                'show_picture' => true,
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

    public function addContactsToUser(User $user, array $contacts)
    {
        $this->om->startFlushSuite();

        foreach ($contacts as $contact) {
            $existingContact = $this->getContactByUserAndContact($user, $contact);

            if (is_null($existingContact)) {
                $existingContact = new Contact();
                $existingContact->setUser($user);
                $existingContact->setContact($contact);
                $this->om->persist($existingContact);
            }
        }
        $this->om->endFlushSuite();
    }

    public function addContactsToUserAndCategory(
        User $user,
        Category $category,
        array $contacts
    ) {
        $this->om->startFlushSuite();

        foreach ($contacts as $contact) {
            $existingContact = $this->getContactByUserAndContact($user, $contact);

            if (is_null($existingContact)) {
                $existingContact = new Contact();
                $existingContact->setUser($user);
                $existingContact->setContact($contact);
                $existingContact->addCategory($category);
                $this->om->persist($existingContact);
            }
        }
        $this->om->endFlushSuite();
    }

    public function sortContactsByCategories(
        User $user,
        array $categories,
        $orderedBy = 'firstName',
        $order = 'ASC',
        $page = 1,
        $max = 50
    ) {
        $contacts = array();
        $contacts['all_my_contacts'] = array();

        foreach ($categories as $category) {
            $contacts[$category->getId()] = array();
        }

        $contacts['all_my_contacts'] = $this->getUserContactsWithPager(
            $user,
            '',
            $page,
            $max,
            $orderedBy,
            $order
        );

        foreach ($categories as $category) {
            $contacts[$category->getId()] = $this->getUserContactsByCategoryWithPager(
                $user,
                $category,
                $page,
                $max,
                $orderedBy,
                $order
            );
        }

        return $contacts;
    }

    public function removeContactFromCategory(Contact $contact, Category $category)
    {
        $contact->removeCategory($category);
        $this->persistContact($contact);
    }

    /***************************************
     * Access to ContactRepository methods *
     ***************************************/

    public function getContactsByUser(
        User $user,
        $orderedBy = 'lastName',
        $order = 'ASC',
        $executeQuery = true
    ) {
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
    ) {
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

    public function getContactByUserAndContact(
        User $user,
        User $contact,
        $executeQuery = true
    ) {
        return $this->contactRepo->findContactByUserAndContact(
            $user,
            $contact,
            $executeQuery
        );
    }

    public function getContactsByUserAndCategory(
        User $user,
        Category $category,
        $orderedBy = 'lastName',
        $order = 'ASC',
        $executeQuery = true
    ) {
        return $this->contactRepo->findContactsByUserAndCategory(
            $user,
            $category,
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
    ) {
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
