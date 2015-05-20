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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\ContactManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ContactController extends Controller
{
    private $contactManager;

    /**
     * @DI\InjectParams({
     *     "contactManager" = @DI\Inject("claroline.manager.contact_manager")
     * })
     */
    public function __construct(ContactManager $contactManager)
    {
        $this->contactManager = $contactManager;
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
}
