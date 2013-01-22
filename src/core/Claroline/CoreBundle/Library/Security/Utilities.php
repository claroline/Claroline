<?php

namespace Claroline\CoreBundle\Library\Security;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class Utilities
{
    /** @var EntityManager */
    private $em;
    /** @var array */
    private $expectedKeysForResource;
    /** @var array */
    private $expectedKeysForWorkspace;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->expectedTypeOfRight = array('workspace', 'resource');
        $this->expectedKeysForResource = array(
            'canView',
            'canOpen',
            'canDelete',
            'canEdit',
            'canCopy',
            'canCreate',
            'canExport'
        );
        $this->expectedKeysForWorkspace = array('canView', 'canDelete', 'canEdit');
    }

    /**
     * Takes the array of checked ids from the rights form (ie rights_form.html.twig) and
     * transforms them into a easy to use permission array.

     * @param array $checks
     * @param string $typeOfRight
     *
     * @Return array
     */
    public function setRightsRequest(array $checks, $typeOfRight)
    {
        if (!in_array($typeOfRight, $this->expectedTypeOfRight)) {
            throw new \Exception(
                "Unknown right type '{$typeOfRight}' (possible values are 'workspace' and 'resource')"
            );
        }

        $configs = array();
        foreach (array_keys($checks) as $key) {
            $arr = explode('-', $key);
            $configs[$arr[1]][$arr[0]] = true;
        }

        foreach ($configs as $key => $config) {
            $configs[$key] = $this->addMissingRights($config, $typeOfRight);
        }

        return $configs;
    }

    /**
     * Returns the roles (an array of string) of the $token.
     *
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     *
     * @return array
     */
    public function getRoles(TokenInterface $token)
    {
        if ($token->getUser() === 'anon.') {
            foreach ($token->getRoles() as $role) {
                $roles[] = $role->getRole();
            }
        } else {
            foreach ($token->getUser()->getRoles() as $role) {
                $roles[] = $role;
            }
        }

        return $roles;
    }

    /**
     * Given an array of rights permission, the method will add the missing rights
     * and set them to false.
     *
     * @param array $rights the right array ie(array('canDelete' => true))
     * @param type $typeOfRight the type of right: currently 'resource' or 'workspace'
     *
     * @return array
     */
    private function addMissingRights(array $rights, $typeOfRight)
    {
        switch ($typeOfRight) {
            case "resource": $expectedKeys = $this->expectedKeysForResource;
            case "workspace": $expectedKeys = $this->expectedKeysForWorkspace;
        }

        foreach ($expectedKeys as $expected) {
            if (!isset($rights[$expected])) {
                $rights[$expected] = false;
            }
        }

        return $rights;
    }
}