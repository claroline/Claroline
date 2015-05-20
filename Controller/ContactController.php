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
use Claroline\CoreBundle\Form\ContactCategoryType;
use Claroline\CoreBundle\Manager\ContactManager;
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

    /**
     * @DI\InjectParams({
     *     "contactManager" = @DI\Inject("claroline.manager.contact_manager"),
     *     "formFactory"    = @DI\Inject("form.factory"),
     *     "requestStack"   = @DI\Inject("request_stack")
     * })
     */
    public function __construct(
        ContactManager $contactManager,
        FormFactory $formFactory,
        RequestStack $requestStack
    )
    {
        $this->contactManager = $contactManager;
        $this->formFactory = $formFactory;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @EXT\Route(
     *     "/my/contacts/tool/index",
     *     name="claro_my_contacts_tool_index",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function myContactsToolIndexAction(User $authenticatedUser)
    {
        $options = $this->contactManager->getUserOptionsValues($authenticatedUser);
        $contacts = $this->contactManager->getUserContacts($authenticatedUser);
        $categories = $this->contactManager->getCategoriesByUser($authenticatedUser);

        return array(
            'options' => $options,
            'contacts' => $contacts,
            'categories' => $categories
        );
    }

    /**
     * @EXT\Route(
     *     "/category/create/form",
     *     name="claro_contact_category_create_form",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Contact:categoryCreateModalForm.html.twig")
     *
     * Displays the homeTab form.
     *
     * @return Response
     */
    public function categoryCreateFormAction()
    {
        $form = $this->formFactory->create(new ContactCategoryType(), new Category());

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
     *
     * Create a new homeTab.
     *
     * @return Response
     */
    public function categoryCreateAction(User $authenticatedUser)
    {
        $category = new Category();
        $category->setUser($authenticatedUser);
        $form = $this->formFactory->create(new ContactCategoryType(), $category);
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
     *
     * Displays the homeTab form.
     *
     * @return Response
     */
    public function categoryEditFormAction(Category $category)
    {
        $form = $this->formFactory->create(new ContactCategoryType(), $category);

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
     *
     * Create a new homeTab.
     *
     * @return Response
     */
    public function categoryEditAction(Category $category)
    {
        $form = $this->formFactory->create(new ContactCategoryType(), $category);
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
     * @EXT\Template("ClarolineCoreBundle:Contact:categoryEditModalForm.html.twig")
     *
     * Create a new homeTab.
     *
     * @return Response
     */
    public function categoryDeleteAction(User $authenticatedUser, Category $category)
    {
        $this->checkUserAccessForCategory($category, $authenticatedUser);
        $this->contactManager->deleteCategory($category);

        return new JsonResponse('success', 200);
    }

    private function checkUserAccessForCategory(Category $category, User $user)
    {
        if ($category->getUser()->getId() !== $user->getId()) {

            throw new AccessDeniedException();
        }
    }
}
