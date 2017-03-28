<?php

namespace FormaLibre\SupportBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="formalibre_support_configuration")
 * @ORM\Entity
 */
class Configuration
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $details;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function setDetails($details)
    {
        $this->details = $details;
    }

    public function getContacts()
    {
        return !is_null($this->details) && isset($this->details['contacts']) ? $this->details['contacts'] : [];
    }

    public function setContacts(array $contacts)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['contacts'] = $contacts;
    }

    public function getNotify($type)
    {
        return !is_null($this->details) && isset($this->details['notify']) && isset($this->details['notify'][$type]) ?
            $this->details['notify'][$type] :
            true;
    }

    public function setNotify($type, $value)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        if (!isset($this->details['notify'])) {
            $this->details['notify'] = [];
        }
        $this->details['notify'][$type] = $value;
    }
}
