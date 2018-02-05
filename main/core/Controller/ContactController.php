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
     * ContactController constructor.
     *
     * @DI\InjectParams({
     *     "contactManager" = @DI\Inject("claroline.manager.contact_manager")
     * })
     *
     * @param ContactManager $contactManager
     */
    public function __construct(ContactManager $contactManager)
    {
        $this->contactManager = $contactManager;
    }

    /**
     * @EXT\Route(
     *     "/my/contacts",
     *     name="claro_my_contacts",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     * @EXT\Template()
     *
     * @param User $user
     *
     * @return array
     */
    public function myContactsAction(User $user)
    {
        $options = $this->contactManager->getUserOptions($user);

        return [
            'options' => $options,
        ];
    }
}
