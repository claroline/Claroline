<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\ProfileProperty;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Security\Utilities;

/**
 * @DI\Service("claroline.manager.profile_property_manager")
 */
class ProfilePropertyManager
{
    private $om;
    private $roleManager;
    private $profileTypeRepo;
    private $secUtils;
    private $container;

    /**
     * @DI\InjectParams({
     *     "om"          = @DI\Inject("claroline.persistence.object_manager"),
     *     "roleManager" = @DI\Inject("claroline.manager.role_manager"),
     *     "secUtils"    = @DI\Inject("claroline.security.utilities"),
     *     "container"   = @DI\Inject("service_container")
     * })
     */
    public function __construct(
        ObjectManager $om,
        RoleManager $roleManager,
        Utilities $secUtils,
        $container
    ) {
        $this->om = $om;
        $this->roleManager = $roleManager;
        $this->secUtils = $secUtils;
        $this->container = $container;
        $this->profilePropertyRepo = $om->getRepository('ClarolineCoreBundle:ProfileProperty');
    }

    /**
     * Add the default properties accesses for each roles.
     */
    public function addDefaultProperties()
    {
        $properties = User::getEditableProperties();
        $this->om->startFlushSuite();

        foreach ($properties as $property => $editable) {
            $this->addProperties($property, $editable);
        }

        $this->om->endFlushSuite();
    }

    /**
     * Public function add a property for each roles.
     *
     * @param string $property
     * @param bool   $editable
     */
    public function addProperties($property, $editable)
    {
        $platformRoles = $this->roleManager->getAllPlatformRoles();

        foreach ($platformRoles as $role) {
            $prop = $this->profilePropertyRepo
                ->findBy(array('property' => $property, 'role' => $role));

            if (count($prop) === 0) {
                $this->addProperty($property, $editable, $role);
            }
        }
    }

    /**
     * Create a property entity.
     *
     * @param string $property
     * @param bool   $editable
     * @param Role   $role
     */
    public function addProperty($property, $editable, Role $role)
    {
        $propertyEntity = new ProfileProperty();
        $propertyEntity->setProperty($property);
        $propertyEntity->setIsEditable($editable);
        $propertyEntity->setRole($role);
        $this->om->persist($propertyEntity);
        $this->om->flush();
    }

    public function getAccessesForCurrentUser()
    {
        $roles = $this->container->get('security.token_storage')->getToken();
        $rolenames = $this->secUtils->getRoles($roles);

        return $this->getAccessessByRoles($rolenames);
    }

    /**
     * Get the property access for an list of roles.
     *
     * @param array $rolenames
     */
    public function getAccessessByRoles(array $rolenames)
    {
        $properties = $this->profilePropertyRepo->findAccessesByRoles($rolenames);

        return $properties;
    }

    public function getAllProperties()
    {
        return $this->profilePropertyRepo->findAll();
    }

    public function invertProperty(ProfileProperty $property)
    {
        $property->setIsEditable(!$property->isEditable());
        $this->om->persist($property);
        $this->om->flush();
    }
}
