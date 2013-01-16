<?php

namespace Claroline\CoreBundle\Library\Security;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class RightsManager
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
        $this->expectedKeysForResource = array('canView', 'canOpen', 'canDelete', 'canEdit', 'canCopy', 'canCreate', 'canExport');
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
    public function setRightsRequest($checks, $typeOfRight)
    {
        if(!in_array($typeOfRight, $this->expectedTypeOfRight)){
            throw new \Exception('Wrong type of right specified on setRightRequest for the RightsManager.
                Expected values are "workspace", "resource" ("'.$typeOfRight.'" is inccorect).');
        }

        $configs = array();
        foreach(array_keys($checks) as $key){
            $arr = explode('-', $key);
            $configs[$arr[1]][$arr[0]] = true;
        }

        foreach($configs as $key => $config){
            $configs[$key] = $this->addMissingRights($config, $typeOfRight);
        }

        return $configs;
    }


    public function getRoles(TokenInterface $token)
    {
        if ($token->getUser() === 'anon.'){
            foreach($token->getRoles() as $role){
                $roles[] = $role->getRole();
            }
        } else {
            foreach($token->getUser()->getRoles() as $role){
                $roles[] = $role;
            }
        }

        return $roles;
    }

    private function addMissingRights($rights, $typeOfRight)
    {
        switch($typeOfRight){
            case "resource": $expectedKeys = $this->expectedKeysForResource;
            case "workspace": $expectedKeys = $this->expectedKeysForWorkspace;
        }

        foreach($expectedKeys as $expected){
            if(!isset($rights[$expected])){
                $rights[$expected] = false;
            }
        }

        return $rights;
    }
}

