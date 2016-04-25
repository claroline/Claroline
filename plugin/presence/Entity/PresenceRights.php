<?php

namespace FormaLibre\PresenceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\Role;

/**
  * @ORM\Entity(repositoryClass="FormaLibre\PresenceBundle\Repository\PresenceRightsRepository")    
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
    protected $role;

    /**
     * @ORM\Column(type="integer")
     */
    protected $mask;

     public function getId()
     {
         return $this->id;
     }

     public function getRole()
     {
         return $this->role;
     }

     public function getMask()
     {
         return $this->mask;
     }

     public function setId($id)
     {
         $this->id = $id;
     }

     public function setRole(Role $role)
     {
         $this->role = $role;
     }

     public function setMask($mask)
     {
         $this->mask = $mask;
     }
 }
