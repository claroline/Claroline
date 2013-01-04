<?php

namespace Claroline\CoreBundle\Library\Resource;

use Doctrine\ORM\EntityManager;

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
        $expectedKeys = array('canView', 'canOpen', 'canDelete', 'canEdit', 'canCopy', 'canCreate', 'canExport');

        foreach($expectedKeys as $expected){
            if(!isset($rights[$expected])){
                $rights[$expected] = false;
            }
        }

        return $rights;
    }
}

