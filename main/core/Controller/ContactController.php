<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\Contact\Category;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\Contact\CategoriesTransferType;
use Claroline\CoreBundle\Form\Contact\CategoryType;
use Claroline\CoreBundle\Form\Contact\OptionsType;
use Claroline\CoreBundle\Manager\ContactManager;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ContactController extends Controller
{
    private $contactManager;
    private $formFactory;
    private $request;
    private $userManager;

    /**
     * @DI\InjectParams({
     *     "contactManager" = @DI\Inject("claroline.manager.contact_manager"),
     *     "formFactory"    = @DI\Inject("form.factory"),
     *     "requestStack"   = @DI\Inject("request_stack"),
     *     "userManager"    = @DI\Inject("claroline.manager.user_manager")
     * })
     */
    public function __construct(
        ContactManager $contactManager,
        FormFactory $formFactory,
        RequestStack $requestStack,
        UserManager $userManager
    ) {
        $this->contactManager = $contactManager;
        $this->formFactory = $formFactory;
        $this->request = $requestStack->getCurrentRequest();
        $this->userManager = $userManager;
    }

    /**
     * @EXT\Route(
     *     "/my/contacts/tool/index/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="claro_my_contacts_tool_index",
     *     defaults={"page"=1, "max"=50, "orderedBy"="lastName","order"="ASC","search"=""},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function myContactsToolIndexAction(
        User $authenticatedUser,
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'lastName',
        $order = 'ASC'
    ) {
        $options = $this->contactManager->getUserOptionsValues($authenticatedUser);
        $allContacts = $this->contactManager->getUserContacts(
            $authenticatedUser,
            '',
            $orderedBy,
            $order
        );

        if (empty($search)) {
            $categories = $this->contactManager->getCategoriesByUser(
                $authenticatedUser,
                'name',
                'ASC'
            );
            $contacts = $this->contactManager->sortContactsByCategories(
                $authenticatedUser,
                $categories,
                $orderedBy,
                $order,
                $page,
                $max
            );
            $params = array(
                'options' => $options,
                'categories' => $categories,
                'allContacts' => $allContacts,
                'contacts' => $contacts,
                'search' => $search,
                'max' => $max,
                'orderedBy' => $orderedBy,
                'order' => $order,
            );
        } else {
            $contacts = $this->contactManager->getUserContactsWithPager(
                $authenticatedUser,
                $search,
                $page,
                $max,
                $orderedBy,
                $order
            );
            $params = array(
                'options' => $options,
                'allContacts' => $allContacts,
                'contacts' => $contacts,
                'search' => $search,
                'max' => $max,
                'orderedBy' => $orderedBy,
                'order' => $order,
            );
        }
        $users = $this->userManager->getUsersForUserPicker($authenticatedUser);
        $params['users'] = $users;

        return $params;
    }

    /**
     * @EXT\Route(
     *     "/show/all/my/contacts/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}",
     *     name="claro_contact_show_all_my_contacts",
     *     defaults={"page"=1, "max"=50, "orderedBy"="lastName","order"="ASC"},
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function showAllMyContactsAction(
        User $authenticatedUser,
        $page = 1,
        $max = 50,
        $orderedBy = 'lastName',
        $order = 'ASC'
    ) {
        $options = $this->contactManager->getUserOptionsValues($authenticatedUser);
        $contacts = $this->contactManager->getUserContactsWithPager(
            $authenticatedUser,
            '',
            $page,
            $max,
            $orderedBy,
            $order
        );

        return array(
            'options' => $options,
            'contacts' => $contacts,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order,
        );
    }

    /**
     * @EXT\Route(
     *     "/show/category/{category}/contacts/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}",
     *     name="claro_contact_show_contacts_by_category",
     *     defaults={"page"=1, "max"=50, "orderedBy"="lastName","order"="ASC"},
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function showCategoryContactsAction(
        User $authenticatedUser,
        Category $category,
        $page = 1,
        $max = 50,
        $orderedBy = 'lastName',
        $order = 'ASC'
    ) {
        $options = $this->contactManager->getUserOptionsValues($authenticatedUser);
        $contacts = $this->contactManager->getUserContactsByCategoryWithPager(
            $authenticatedUser,
            $category,
            $page,
            $max,
            $orderedBy,
            $order
        );

        return array(
            'category' => $category,
            'options' => $options,
            'contacts' => $contacts,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order,
        );
    }

    /**
     * @EXT\Route(
     *     "/show/all/visible/users/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}",
     *     name="claro_contact_show_all_visible_users",
     *     defaults={"page"=1, "max"=50, "orderedBy"="lastName","order"="ASC"},
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function showAllVisibleUsersAction(
        User $authenticatedUser,
        $page = 1,
        $max = 50,
        $orderedBy = 'lastName',
        $order = 'ASC'
    ) {
        $options = $this->contactManager->getUserOptionsValues($authenticatedUser);
        $users = $this->userManager->getUsersForUserPicker(
            $authenticatedUser,
            '',
            false,
            true,
            false,
            false,
            $page,
            $max,
            $orderedBy,
            $order
        );

        return array(
            'options' => $options,
            'users' => $users,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order,
        );
    }

    /**
     * @EXT\Route(
     *     "/contacts/add",
     *     name="claro_contacts_add",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "userIds"}
     * )
     */
    public function contactsAddAction(User $authenticatedUser, array $users)
    {
        $this->contactManager->addContactsToUser($authenticatedUser, $users);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/contact/{contact}/delete",
     *     name="claro_contact_delete",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function contactDeleteAction(User $authenticatedUser, User $contact)
    {
        $userContact = $this->contactManager->getContactByUserAndContact(
            $authenticatedUser,
            $contact
        );

        if (!is_null($userContact)) {
            $this->contactManager->deleteContact($userContact);
        }

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/contacts/add/to/category/{category}",
     *     name="claro_contacts_add_to_category",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "userIds"}
     * )
     */
    public function contactsAddToCategoryAction(
        User $authenticatedUser,
        Category $category,
        array $users
    ) {
        $this->contactManager->addContactsToUserAndCategory(
            $authenticatedUser,
            $category,
            $users
        );

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/options/configure/form",
     *     name="claro_contact_options_configure_form",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Contact:optionsConfigureModalForm.html.twig")
     */
    public function optionsConfigureFormAction(User $authenticatedUser)
    {
        $options = $this->contactManager->getUserOptions($authenticatedUser);
        $form = $this->formFactory->create(new OptionsType($options));

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/options/configure",
     *     name="claro_contact_options_configure",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Contact:optionsConfigureModalForm.html.twig")
     */
    public function optionsConfigureAction(User $authenticatedUser)
    {
        $options = $this->contactManager->getUserOptions($authenticatedUser);
        $form = $this->formFactory->create(new OptionsType($options));
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $values = array(
                'show_all_my_contacts' => $form['showAllMyContacts']->getData(),
                'show_all_visible_users' => $form['showAllVisibleUsers']->getData(),
                'show_username' => $form['showUsername']->getData(),
                'show_mail' => $form['showMail']->getData(),
                'show_phone' => $form['showPhone']->getData(),
                'show_picture' => $form['showPicture']->getData(),
            );
            $options->setOptions($values);
            $this->contactManager->persistOptions($options);

            return new JsonResponse('success', 200);
        } else {
            return array('form' => $form->createView());
        }
    }

    /**
     * @EXT\Route(
     *     "/category/create/form",
     *     name="claro_contact_category_create_form",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Contact:categoryCreateModalForm.html.twig")
     */
    public function categoryCreateFormAction()
    {
        $form = $this->formFactory->create(new CategoryType(), new Category());

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/category/create",
     *     name="claro_contact_category_create",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Contact:categoryCreateModalForm.html.twig")
     */
    public function categoryCreateAction(User $authenticatedUser)
    {
        $category = new Category();
        $category->setUser($authenticatedUser);
        $form = $this->formFactory->create(new CategoryType(), $category);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $lastOrder = $this->contactManager
                ->getOrderOfLastCategoryByUser($authenticatedUser);

            if (is_null($lastOrder['order_max'])) {
                $category->setOrder(1);
            } else {
                $category->setOrder($lastOrder['order_max'] + 1);
            }
            $this->contactManager->persistCategory($category);

            return new JsonResponse(
                array('id' => $category->getId(), 'name' => $category->getName()),
                200
            );
        } else {
            return array('form' => $form->createView());
        }
    }

    /**
     * @EXT\Route(
     *     "/category/{category}/edit/form",
     *     name="claro_contact_category_edit_form",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Contact:categoryEditModalForm.html.twig")
     */
    public function categoryEditFormAction(User $authenticatedUser, Category $category)
    {
        $this->checkUserAccessForCategory($category, $authenticatedUser);
        $form = $this->formFactory->create(new CategoryType(), $category);

        return array('form' => $form->createView(), 'category' => $category);
    }

    /**
     * @EXT\Route(
     *     "/category/{category}/edit",
     *     name="claro_contact_category_edit",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Contact:categoryEditModalForm.html.twig")
     */
    public function categoryEditAction(User $authenticatedUser, Category $category)
    {
        $this->checkUserAccessForCategory($category, $authenticatedUser);
        $form = $this->formFactory->create(new CategoryType(), $category);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->contactManager->persistCategory($category);

            return new JsonResponse(
                array('id' => $category->getId(), 'name' => $category->getName()),
                200
            );
        } else {
            return array('form' => $form->createView(), 'category' => $category);
        }
    }

    /**
     * @EXT\Route(
     *     "/category/{category}/delete",
     *     name="claro_contact_category_delete",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function categoryDeleteAction(User $authenticatedUser, Category $category)
    {
        $this->checkUserAccessForCategory($category, $authenticatedUser);
        $this->contactManager->deleteCategory($category);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/contact/{user}/categories/transfer/form",
     *     name="claro_contact_categories_transfer_form",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Contact:categoriesTransferModalForm.html.twig")
     */
    public function categoriesTransferFormAction(User $authenticatedUser, User $user)
    {
        $contact = $this->contactManager->getContactByUserAndContact($authenticatedUser, $user);
        $form = $this->formFactory->create(new CategoriesTransferType($authenticatedUser), $contact);

        return array('form' => $form->createView(), 'contact' => $user);
    }

    /**
     * @EXT\Route(
     *     "/contact/{user}/categories/transfer",
     *     name="claro_contact_categories_transfer",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Contact:categoriesTransferModalForm.html.twig")
     */
    public function categoriesTransferAction(User $authenticatedUser, User $user)
    {
        $contact = $this->contactManager->getContactByUserAndContact($authenticatedUser, $user);
        $form = $this->formFactory->create(new CategoriesTransferType($authenticatedUser), $contact);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            if (!is_null($contact)) {
                $this->contactManager->persistContact($contact);
            }

            return new JsonResponse('success', 200);
        } else {
            return array('form' => $form->createView(), 'contact' => $user);
        }
    }

    /**
     * @EXT\Route(
     *     "/contact/{user}/category/{category}/remove",
     *     name="claro_contact_category_remove",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function contactCategoryRemoveAction(
        User $authenticatedUser,
        User $user,
        Category $category
    ) {
        $contact = $this->contactManager->getContactByUserAndContact(
            $authenticatedUser,
            $user
        );

        if (!is_null($contact)) {
            $this->contactManager->removeContactFromCategory($contact, $category);
        }

        return new JsonResponse('success', 200);
    }

    private function checkUserAccessForCategory(Category $category, User $user)
    {
        if ($category->getUser()->getId() !== $user->getId()) {
            throw new AccessDeniedException();
        }
    }
}
