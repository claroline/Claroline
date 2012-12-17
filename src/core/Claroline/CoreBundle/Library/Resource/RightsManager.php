<?php

namespace Claroline\CoreBundle\Library\Resource;

use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Role;

class RightsManager
{
    /** @var EntityManager */
    private $em;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Returns an array of effective ResourceRight for a resource in a workspace
     *
     * @param AbstractResource $resource
     *
     * @return array
     */
    public function getRights(AbstractResource $resource)
    {
        $repo = $this->em->getRepository('ClarolineCoreBundle:Workspace\ResourceRights');
        $configs = $repo->getAllForResource($resource);
        $baseConfigs = array();
        $customConfigs = array();
        $effectiveConfigs = array();

        foreach($configs as $config){
            ($config->getResource() != null) ? $customConfigs[] = $config: $baseConfigs[] = $config;
        }

        foreach($baseConfigs as $baseConfig){
            $found = false;
            $toAdd = null;

            foreach ($customConfigs as $key => $customConfig){
                if($customConfig->getRole() == $baseConfig->getRole()){
                    $found = true;
                    $toAdd = $key;
                }
            }

            $found ? $effectiveConfigs[] = $customConfigs[$toAdd] : $effectiveConfigs[] = $baseConfig;
        }

        return $effectiveConfigs;
    }

    /**
     * Returns the effective rights for a role.
     *      *
     * @param \Claroline\CoreBundle\Entity\Role $role
     *
     * @return Claroline\CoreBundle\Entity\Workspace\ResourceRights
     */
    public function getRoleRights(Role $role)
    {
        $repo = $this->em->getRepository('ClarolineCoreBundle:Workspace\ResourceRights');
        $rights = $repo->findBy(array('role' => $role));
        if(count($rights) > 1){
            foreach ($rights as $right){
                if ($right->getResource()!= null){
                    return $right;
                }
            }
        } else {
            return $rights[0];
        }

        throw new \Exception("function getRoleRight couldn't find an effective right");
    }

    /**
     * Takes the array of checked ids from the rights form (ie rights_form.html.twig) and
     * transforms them into a easy to use permission array.

     * @param array $checks
     *
     * @Return array
     */
    public function setRightsRequest($checks)
    {
        $configs = array();
        foreach(array_keys($checks) as $key){
            $arr = explode('-', $key);
            $configs[$arr[1]][$arr[0]] = true;
        }

        foreach($configs as $key => $config){
            $configs[$key] = $this->addMissingRights($config);
        }

        return $configs;
    }

    private function addMissingRights($rights)
    {
        $expectedKeys = array('canSee', 'canOpen', 'canDelete', 'canEdit', 'canCopy', 'canCreate');
        foreach($expectedKeys as $expected){
            if(!isset($rights[$expected])){
                $rights[$expected] = false;
            }
        }

        return $rights;
    }
}

