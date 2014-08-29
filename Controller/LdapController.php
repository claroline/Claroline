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
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

/**
 * Controller of the platform parameters section.
 */
class LdapController extends Controller
{
    private $ldap;
    private $request;
    private $formFactory;
    private $securityContext;
    private $translator;

    /**
     * @InjectParams({
     *     "ldap"               = @Inject("claroline.ldap_bundle.library.ldap_manager"),
     *     "request"            = @Inject("request_stack"),
     *     "formFactory"        = @Inject("form.factory"),
     *     "toolManager"        = @Inject("claroline.manager.tool_manager"),
     *     "securityContext"    = @Inject("security.context"),
     *     "translator"         = @Inject("translator")
     * })
     */
    public function __construct(
        LdapManager $ldap,
        FormFactory $formFactory,
        RequestStack $request,
        ToolManager $toolManager,
        SecurityContextInterface $securityContext,
        Translator $translator
    )
    {
        $this->ldap = $ldap;
        $this->request = $request->getMasterRequest();
        $this->formFactory = $formFactory;
        $this->securityContext = $securityContext;
        $this->adminTool = $toolManager->getAdminToolByName('administration_tool_ldap');
        $this->translator = $translator;
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
     * @Route("/settings", name="claro_admin_ldap_settings")
     * @Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function settingsAction()
    {
        $this->checkOpen();

        $config = $this->ldap->getConfig();
        $servers = isset($config['servers']) ? $config['servers'] : null;
        $userCreation = isset($config['userCreation']) ? $config['userCreation'] : null;

        return array('servers' => $servers, 'userCreation' => $userCreation);
    }

    /**
     * @Route("/form/{host}", name="claro_admin_ldap_form", options = {"expose"=true}, requirements = {"host"=".+"})
     * @Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function formAction($host = null)
    {
        $this->checkOpen();

        $form = $this->formFactory->create(new LdapType(), $this->ldap->get($host));

        if ($this->request->getMethod() === 'POST' and $form->handleRequest($this->request) and $form->isValid()) {
            $data = $form->getData();

            if ($this->ldap->exists($host, $data)) {
                $form->addError(new FormError($this->translator->trans('ldap_already_exists', array(), 'ldap')));
            } else {
                if (!$this->ldap->connect($data)) {
                    $form->addError(new FormError($this->translator->trans('ldap_cant_connect', array(), 'ldap')));
                } else {
                    $this->ldap->saveConfig($data);
                    $this->ldap->deleteIfReplace($host, $data);

                    return new Response('true');
                }
            }
        }

        return array('form' => $form->createView(), 'host' => $host);
    }

    /**
     * @Route("/usercreation/{state}", name="claro_admin_ldap_check_user_creation", options = {"expose"=true})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkUserCreationAction($state)
    {
        $this->checkOpen();

        $state = $state === 'true' ? true : false;

        if ($this->ldap->checkUserCreation($state)) {
            return new Response('true');
        }

        return new Response('false');
    }

    /**
     * @Route("/delte/{host}", name="claro_admin_ldap_delete", options = {"expose"=true}, requirements = {"host"=".+"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($host)
    {
        $this->checkOpen();

        if ($host and $this->ldap->deleteServer($host)) {
            return new Response('true');
        }

        return new Response('false');
    }

    /**
     * @Route("/config/menu", name="claro_admin_ldap_servers", options = {"expose"=true})
     * @Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function serversAction()
    {
        $this->checkOpen();

        return array('servers' => $this->ldap->getConfig()['servers']);
    }

    /**
     * @Route("/config/users/{host}", name="claro_admin_ldap_users", requirements = {"host"=".+"})
     * @Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function usersAction($host)
    {
        $this->checkOpen();

        $classes = array();
        $users = array();
        $server = $this->ldap->get($host);

        if ($this->ldap->connect($server)) {

            if ($search = $this->ldap->search($server, '(&(objectClass=*))', array('objectclass'))) {
                $entries = $this->ldap->getEntries($search);
                foreach ($entries as $objectClass) {
                    if (isset($objectClass['objectclass'])) {
                        unset($objectClass['objectclass']['count']);
                        $classes = array_merge($classes, $objectClass['objectclass']);
                    }
                }
            }

            if (isset($server['objectClass']) and
                $search = $this->ldap->search($server, '(&(objectClass=' . $server['objectClass'] . '))')
            ) {
                $users = $this->ldap->getEntries($search);
            }

            $this->ldap->close();

            return array(
                'server' => $server,
                'users' => $users,
                'usersJSON' => json_encode($users),
                'classes' => array_unique($classes)
            );
        }

        return array('error' => true);
    }

    /**
     * @Route("/config/groups/{host}", name="claro_admin_ldap_groups", requirements = {"host"=".+"})
     * @Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function groupsAction($host)
    {
        $this->checkOpen();

        //$classes = array();
        $groups = array();
        $server = $this->ldap->get($host);

        if ($this->ldap->connect($server)) {

            if ($search = $this->ldap->search(
                $server, '(&(objectClass=person))'
            )) {
                $groups = $this->ldap->getEntries($search);
            }

            throw new \Exception(var_dump($groups));

            $this->ldap->close();

            return array(
                'server' => $server,
                'groups' => $groups,
                'groupJSON' => json_encode($groups),
            );
        }

        return array('error' => true);
    }

     /**
     * @Route("/config/save/settings", name="claro_admin_ldap_save_settings", options = {"expose"=true})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function saveSettingsAction()
    {
        if ($this->ldap->saveSettings($this->request->request->all())) {
            return new Response('true');
        }

        return new Response('false');
    }

    /**
     * @Route(
     *     "/get/users/{objectClass}/{host}",
     *     name="claro_admin_ldap_get_users",
     *     options = {"expose"=true},
     *     requirements = {"host"=".+"}
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getUsersAction($objectClass, $host)
    {
        $this->checkOpen();

        $users = array();

        if ($this->ldap->connect($this->ldap->get($host))) {
            if ($search = $this->ldap->search($this->ldap->get($host), '(&(objectClass=' . $objectClass . '))')) {
                $users = $this->ldap->getEntries($search);
            }

            $this->ldap->close();
        }

        return new Response(json_encode($users));
    }

    /**
     * Check if the user can open or change this admin tool.
     *
     */
    private function checkOpen()
    {
        if ($this->securityContext->isGranted('OPEN', $this->adminTool)) {
            return true;
        }

        throw new AccessDeniedException();
    }
}

