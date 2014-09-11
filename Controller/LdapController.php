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
use Claroline\LdapBundle\Manager\LdapManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Templating\EngineInterface;

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
     *     "ldap"               = @Inject("claroline.ldap_bundle.manager.ldap_manager"),
     *     "request"            = @Inject("request_stack"),
     *     "formFactory"        = @Inject("form.factory"),
     *     "toolManager"        = @Inject("claroline.manager.tool_manager"),
     *     "securityContext"    = @Inject("security.context"),
     *     "translator"         = @Inject("translator"),
     *     "templating"         = @Inject("templating")
     * })
     */
    public function __construct(
        LdapManager $ldap,
        FormFactory $formFactory,
        RequestStack $request,
        ToolManager $toolManager,
        SecurityContextInterface $securityContext,
        Translator $translator,
        EngineInterface $templating
    )
    {
        $this->ldap = $ldap;
        $this->request = $request->getMasterRequest();
        $this->formFactory = $formFactory;
        $this->securityContext = $securityContext;
        $this->adminTool = $toolManager->getAdminToolByName('administration_tool_ldap');
        $this->translator = $translator;
        $this->templating = $templating;
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
     * @Route("/form/{name}", name="claro_admin_ldap_form", options = {"expose"=true})
     * @Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function formAction($name = null)
    {
        $this->checkOpen();

        $form = $this->formFactory->create(new LdapType(), $this->ldap->get($name));

        if ($this->request->getMethod() === 'POST' and $form->handleRequest($this->request) and $form->isValid()) {
            $data = $form->getData();

            if ($this->ldap->exists($name, $data)) {
                $form->addError(new FormError($this->translator->trans('ldap_already_exists', array(), 'ldap')));
            } else {
                $user = isset($data['user']) ? $data['user'] : null;
                $password = isset($data['password']) ? $data['user'] : null;

                if (!$this->ldap->connect($data, $user, $password)) {
                    $form->addError(new FormError($this->translator->trans('ldap_cant_connect', array(), 'ldap')));
                } else {
                    $this->ldap->saveConfig($data);
                    $this->ldap->deleteIfReplace($name, $data);

                    return new Response('true');
                }
            }
        }

        return array('form' => $form->createView(), 'name' => $name);
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
     * @Route("/delte/{name}", name="claro_admin_ldap_delete", options = {"expose"=true})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($name)
    {
        $this->checkOpen();

        if ($name and $this->ldap->deleteServer($name)) {
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
     * @Route("/config/users/{name}", name="claro_admin_ldap_users")
     * @Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function usersAction($name)
    {
        $this->checkOpen();

        $users = array();
        $server = $this->ldap->get($name);
        $user = isset($server['user']) ? $server['user'] : null;
        $password = isset($server['password']) ? $server['user'] : null;

        if ($this->ldap->connect($server, $user, $password)) {

            $classes = $this->ldap->getClasses($server);
            $users = $this->ldap->getUsers($server);
            $this->ldap->close();

            return array(
                'server' => $server,
                'users' => $users,
                'usersJSON' => json_encode($users),
                'classes' => $classes
            );
        }

        return array('error' => true);
    }

    /**
     * @Route("/config/groups/{name}", name="claro_admin_ldap_groups")
     * @Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function groupsAction($name)
    {
        $this->checkOpen();

        $server = $this->ldap->get($name);
        $user = isset($server['user']) ? $server['user'] : null;
        $password = isset($server['password']) ? $server['user'] : null;

        if ($this->ldap->connect($server, $user, $password)) {

            $classes = $this->ldap->getClasses($server);
            $groups = $this->ldap->getGroups($server);
            $this->ldap->close();

            return array(
                'server' => $server,
                'groups' => $groups,
                'groupsJSON' => json_encode($groups),
                'classes' => $classes
            );
        }

        return array('error' => true);
    }

    /**
     * @Route("/export", name="claro_admin_ldap_export")
     * @Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportAction()
    {
        $this->checkOpen();

        return array('servers' => $this->ldap->getConfig()['servers']);
    }

    /**
     * @Route("/preview/{type}/{name}", name="claro_admin_ldap_export_preview", options = {"expose"=true})
     * @Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function previewAction($type, $name)
    {
        $this->checkOpen();

        $users = array();
        $server = $this->ldap->get($name);
        $user = isset($server['user']) ? $server['user'] : null;
        $password = isset($server['password']) ? $server['user'] : null;

        if ($this->ldap->connect($server, $user, $password)) {

            $users = $this->ldap->getUsers($server);
            $this->ldap->close();

            return array(
                'mapping' => $this->ldap->userMapping($server),
                'type' => $type,
                'users' => $users,
                'server' => $server
            );
        }

        return array('error' => true);
    }

    /**
     * @Route("
     *    /export/{name}.{type}", name="claro_admin_ldap_export_export_file", options = {"expose"=true})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportFileAction($type, $name)
    {
        $this->checkOpen();

        $users = array();
        $server = $this->ldap->get($name);
        $user = isset($server['user']) ? $server['user'] : null;
        $password = isset($server['password']) ? $server['user'] : null;

        if ($this->ldap->userMapping($server) and $this->ldap->connect($server, $user, $password)) {

            $users = $this->ldap->getUsers($server);
            $this->ldap->close();

            $response = new response(
                $this->templating->render(
                    'ClarolineLdapBundle:export:' . $type . '.html.twig',
                    array('users' => $users, 'server' => $server)
                )
            );
        } else {
            $response = new Response('Error');
        }

        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $name . '.' . $type);
        //$response->headers->set('Content-Type', $mimeType);
        $response->headers->set('Connection', 'close');

        return $response;
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
     * @Route("/get/users/{objectClass}/{name}", name="claro_admin_ldap_get_entries", options = {"expose"=true})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getEntriesAction($objectClass, $name)
    {
        $this->checkOpen();

        $entries = array();
        $server = $this->ldap->get($name);
        $user = isset($server['user']) ? $server['user'] : null;
        $password = isset($server['password']) ? $server['user'] : null;

        if ($this->ldap->connect($server, $user, $password)) {
            if ($search = $this->ldap->search($this->ldap->get($name), '(&(objectClass=' . $objectClass . '))')) {
                $entries = $this->ldap->getEntries($search);
            }

            $this->ldap->close();
        }

        return new Response(json_encode($entries));
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

