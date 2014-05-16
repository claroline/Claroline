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

use Claroline\LdapBundle\Library\Ldap;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Inject;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller of the platform parameters section.
 */
class LdapController extends Controller
{
    private $ldap;

    /**
     * @InjectParams({
     *     "ldap" = @Inject("claroline.library.ldap")
     * })
     */
    public function __construct(Ldap $ldap)
    {
        $this->ldap = $ldap;
    }

    /**
     * @Route("/ldap", name="claro_admin_ldap")
     * @Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function menuAction()
    {
        return array();
    }

    /**
     * @Route("/ldap/settings", name="claro_admin_ldap_form")
     * @Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function formAction()
    {
        $platformConfig = $this->configHandler->getPlatformConfig();
        $form = $this->formFactory->create(new AdminForm\LdapType(), $platformConfig);

        if ($this->request->getMethod() === 'POST' and $form->handleRequest($this->request) and $form->isValid()) {
            $data = array(
                'ldap_host' => $form['ldap_host']->getData(),
                'ldap_port' => $form['ldap_port']->getData(),
                'ldap_root_dn' => $form['ldap_root_dn']->getData()
            );

            $this->configHandler->setParameters($data);
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/ldap/import/users", name="claro_admin_ldap_import_users")
     * @Template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function importUsersAction()
    {
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
     * @Route("/ldap/get/users/{objectClass}", name="claro_admin_ldap_get_users", options = {"expose"=true})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getUsersAction($objectClass)
    {
        $users = array();

        if ($this->ldap->connect()) {
            if ($search = $this->ldap->search('(&(objectClass=' . $objectClass . '))')) {
                $users = $this->ldap->getEntries($search);
            }

            $this->ldap->close();
        }

        return new Response(json_encode($users));
    }
}

