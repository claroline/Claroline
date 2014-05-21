<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\LdapBundle\Controller;

use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\LdapBundle\Form\LdapType;
use Claroline\LdapBundle\Library\LdapManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Controller of the platform parameters section.
 */
class LdapController extends Controller
{
    private $ldap;
    private $request;
    private $formFactory;
    private $securityContext;

    /**
     * @InjectParams({
     *     "ldap"               = @Inject("claroline.ldap_bundle.library.ldap_manager"),
     *     "request"            = @Inject("request_stack"),
     *     "formFactory"        = @Inject("form.factory"),
     *     "toolManager"        = @Inject("claroline.manager.tool_manager"),
     *     "securityContext"    = @Inject("security.context")
     * })
     */
    public function __construct(
        LdapManager $ldap,
        FormFactory $formFactory,
        RequestStack $request,
        ToolManager $toolManager,
        SecurityContextInterface $securityContext
    )
    {
        $this->ldap = $ldap;
        $this->request = $request->getMasterRequest();
        $this->formFactory = $formFactory;
        $this->securityContext = $securityContext;
        $this->adminTool = $toolManager->getAdminToolByName('administration_tool_ldap');
    }

    /**
     * @Route("/", name="claro_admin_ldap")
     * @Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function menuAction()
    {
        $this->checkOpen();

        return array();
    }

    /**
     * @Route("/settings", name="claro_admin_ldap_form")
     * @Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function formAction()
    {
        $this->checkOpen();

        $form = $this->formFactory->create(new LdapType(), $this->ldap);

        if ($this->request->getMethod() === 'POST' and $form->handleRequest($this->request) and $form->isValid()) {
            $this->ldap->saveConfig();
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/import/users", name="claro_admin_ldap_import_users")
     * @Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function importUsersAction()
    {
        $this->checkOpen();

        $classes = array();

        if ($this->ldap->connect()) {

            if ($search = $this->ldap->search('(&(objectClass=*))', array('objectclass'))) {
                $entries = $this->ldap->getEntries($search);
                foreach ($entries as $objectClass) {
                    if (isset($objectClass['objectclass'])) {
                        unset($objectClass['objectclass']['count']);
                        $classes = array_merge($classes, $objectClass['objectclass']);
                    }
                }
            }

            $this->ldap->close();

            return array('classes' => array_unique($classes));
        }

        return array('error' => true);
    }

    /**
     * @Route("/get/users/{objectClass}", name="claro_admin_ldap_get_users", options = {"expose"=true})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getUsersAction($objectClass)
    {
        $this->checkOpen();

        $users = array();

        if ($this->ldap->connect()) {
            if ($search = $this->ldap->search('(&(objectClass=' . $objectClass . '))')) {
                $users = $this->ldap->getEntries($search);
            }

            $this->ldap->close();
        }

        return new Response(json_encode($users));
    }

    private function checkOpen()
    {
        if ($this->securityContext->isGranted('OPEN', $this->adminTool)) {
            return true;
        }

        throw new AccessDeniedException();
    }
}

