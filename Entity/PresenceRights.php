<?php

namespace FormaLibre\PresenceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\Role;

/**
 * @ORM\Entity()
 * @ORM\Table(name="formalibre_presencebundle_rights")
 */
 class PresenceRights
{
     const PERSONAL_ARCHIVES = 1;
     const CHECK_PRESENCES = 2;
     const READING_ARCHIVES = 4;
     const EDIT_ARCHIVES = 8;

     /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Role"
     * )
     * @ORM\JoinColumn(name="role_id", onDelete="CASCADE")
     */
    protected  $role;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $mask;
    
    function getId() {
        return $this->id;
    }

    function getRole() {
        return $this->role;
    }

    function getMask() {
        return $this->mask;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setRole(Role $role) {
        $this->role = $role;
    }

    function setMask($mask) {
        $this->mask = $mask;
    }


}
